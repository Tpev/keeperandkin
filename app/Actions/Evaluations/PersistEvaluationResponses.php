<?php

namespace App\Actions\Evaluations;

use App\Events\EvaluationSubmitted;
use App\Models\AnswerOption;
use App\Models\Evaluation;
use App\Models\EvaluationForm;
use App\Models\EvaluationFormQuestion;
use App\Models\EvaluationResponse;
use App\Models\EvaluationResponseOption;
use App\Services\Evaluation\ScoringService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Persist responses for a given Evaluation + Form, then compute scores/flags and update the evaluation row.
 *
 * Usage:
 *   $action->handle(
 *       evaluation: $evaluation,
 *       form: $form,
 *       payload: [
 *         // per question_id:
 *         101 => ['answer_option_id' => 2001],                // single_choice / boolean
 *         102 => ['answer_option_ids' => [2002,2003]],        // multi_choice
 *         103 => ['answer_value' => 4],                       // scale (numeric)
 *         104 => ['answer_text' => 'free text'],              // text
 *       ]
 *   );
 */
class PersistEvaluationResponses
{
    public function __construct(
        protected ScoringService $scoring
    ) {}

    /**
     * @param  Evaluation      $evaluation  Existing evaluation model (must be saved)
     * @param  EvaluationForm  $form        The form used for this evaluation
     * @param  array           $payload     Answers keyed by question_id (see class doc)
     * @return void
     *
     * @throws \Throwable
     */
    public function handle(Evaluation $evaluation, EvaluationForm $form, array $payload): void
    {
        // Validate that the payload only includes questions present in this form
        $formQuestions = EvaluationFormQuestion::query()
            ->where('form_id', $form->id)
            ->with(['question'])
            ->get()
            ->keyBy('question_id');

        // If you want to enforce "required" here, you can do it now:
        $missingRequired = [];
        foreach ($formQuestions as $qId => $fq) {
            if ($fq->required && !array_key_exists((string)$qId, $this->stringifyKeys($payload)) && !array_key_exists($qId, $payload)) {
                $missingRequired[] = $qId;
            }
        }
        if (!empty($missingRequired)) {
            throw ValidationException::withMessages([
                'responses' => ['Missing required answers for question IDs: '.implode(',', $missingRequired)]
            ]);
        }

        DB::transaction(function () use ($evaluation, $form, $payload, $formQuestions) {
            // Clean previous responses for this evaluation (idempotent resubmit)
            $evaluation->responses()->delete();

            // Link the evaluation to the form version used for this submission
            if ($evaluation->evaluation_form_id !== $form->id) {
                $evaluation->evaluation_form_id = $form->id;
                $evaluation->save();
            }

            // Insert responses
            foreach ($payload as $questionId => $answer) {
                $questionId = (int) $questionId;

                $fq = $formQuestions->get($questionId);
                if (!$fq) {
                    // ignore answers that are not part of this form
                    continue;
                }
                $q = $fq->question;

                $resp = new EvaluationResponse();
                $resp->evaluation_id   = $evaluation->id;
                $resp->form_id         = $form->id;
                $resp->question_id     = $questionId;

                switch ($q->type) {
                    case 'single_choice':
                    case 'boolean':
                        $optId = Arr::get($answer, 'answer_option_id');
                        if ($optId) {
                            // Ensure the option belongs to this question
                            $valid = AnswerOption::query()
                                ->where('id', $optId)
                                ->where('question_id', $questionId)
                                ->exists();
                            if ($valid) {
                                $resp->answer_option_id = $optId;
                            }
                        }
                        break;

                    case 'multi_choice':
                        // We'll save the parent response first, then child rows
                        break;

                    case 'scale':
                        $val = Arr::get($answer, 'answer_value');
                        if (is_numeric($val)) {
                            $resp->answer_value = (float) $val;
                        }
                        break;

                    case 'text':
                        $txt = Arr::get($answer, 'answer_text');
                        if (is_string($txt) && $txt !== '') {
                            $resp->answer_text = $txt;
                        }
                        break;

                    default:
                        // allow custom types to use answer_json
                        $json = Arr::get($answer, 'answer_json');
                        if ($json !== null) {
                            $resp->answer_json = $json;
                        }
                        break;
                }

                $resp->save();

                // Save multi-choice options if needed
                if ($q->type === 'multi_choice') {
                    $ids = Arr::get($answer, 'answer_option_ids', []);
                    $ids = array_values(array_unique(array_filter(array_map('intval', (array) $ids))));
                    if (!empty($ids)) {
                        // Keep only options that belong to this question
                        $validIds = AnswerOption::query()
                            ->where('question_id', $questionId)
                            ->whereIn('id', $ids)
                            ->pluck('id')
                            ->all();

                        foreach ($validIds as $oid) {
                            EvaluationResponseOption::create([
                                'response_id'      => $resp->id,
                                'answer_option_id' => $oid,
                            ]);
                        }
                    }
                }
            }

            // Compute scores + flags and update evaluation
            $calc = $this->scoring->score($evaluation);

            // Keep your existing columns as-is
            // category_scores stored with your public labels; we’ll map keys:
            $categoryScores = $calc['category_scores'];

            // Map to your display keys exactly as your UI expects:
            // Comfort & Confidence, Sociability, Trainability
            $displayScores = [
                'Comfort & Confidence' => $categoryScores[ScoringService::CAT_CC] ?? null,
                'Sociability'          => $categoryScores[ScoringService::CAT_SO] ?? null,
                'Trainability'         => $categoryScores[ScoringService::CAT_TR] ?? null,
            ];

            $evaluation->category_scores = $displayScores;
            $evaluation->red_flags = array_values(array_unique($calc['red_flags'] ?? []));
            // Do NOT touch $evaluation->score since you removed overall score from UI
            $evaluation->save();

            // Fire event (optional)
            event(new EvaluationSubmitted($evaluation->id));
        });
    }

    private function stringifyKeys(array $arr): array
    {
        $out = [];
        foreach ($arr as $k => $v) {
            $out[(string)$k] = $v;
        }
        return $out;
    }
}
