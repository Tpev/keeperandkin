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
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PersistEvaluationResponses
{
    public function __construct(
        protected ScoringService $scoring
    ) {}

    /**
     * Persist a submission, then compute & store scores/flags.
     *
     * @param  Evaluation     $evaluation  (already created)
     * @param  EvaluationForm $form        (the form version used)
     * @param  array          $payload     answers keyed by question_id
     * @return void
     * @throws \Throwable
     */
    public function handle(Evaluation $evaluation, EvaluationForm $form, array $payload): void
    {
        // Load the questions attached to this form, keyed by question_id
        $formQuestions = EvaluationFormQuestion::query()
            ->where('form_id', $form->id)
            ->with('question') // we’ll need type/category for validation & saving branches
            ->get()
            ->keyBy('question_id');

        // Required check (visibility=always only, to match the Livewire rules)
        $missingRequired = [];
        $byStringKey = $this->stringifyKeys($payload);

        foreach ($formQuestions as $qId => $fq) {
            if (!($fq->required ?? false) || ($fq->visibility ?? 'always') !== 'always') {
                continue;
            }

            // Consider it missing if no entry exists at all
            if (!array_key_exists($qId, $payload) && !array_key_exists((string) $qId, $byStringKey)) {
                $missingRequired[] = $qId;
            }
        }

        if ($missingRequired) {
            throw ValidationException::withMessages([
                'responses' => ['Missing required answers for question IDs: ' . implode(',', $missingRequired)]
            ]);
        }

        DB::transaction(function () use ($evaluation, $form, $payload, $formQuestions) {
            // Start clean for idempotent resubmits
            $evaluation->responses()->delete();

            // Link evaluation to the form version (if your schema supports it)
            if (Schema::hasColumn('evaluations', 'evaluation_form_id')) {
                if ($evaluation->evaluation_form_id !== $form->id) {
                    $evaluation->evaluation_form_id = $form->id;
                    $evaluation->save();
                }
            }

            // Persist each answer
            foreach ($payload as $questionId => $answer) {
                $questionId = (int) $questionId;

                /** @var EvaluationFormQuestion|null $fq */
                $fq = $formQuestions->get($questionId);
                if (!$fq || !$fq->question) {
                    // Ignore answers for questions not in this form
                    continue;
                }

                $q = $fq->question;

                // MULTI-CHOICE: only save a response if there are valid option ids
                if ($q->type === 'multi_choice') {
                    $ids = array_values(array_unique(array_filter(array_map('intval', (array) Arr::get($answer, 'answer_option_ids', [])))));
                    if (empty($ids)) {
                        continue; // nothing selected -> no row at all
                    }

                    // Keep only options belonging to this question
                    $validIds = AnswerOption::query()
                        ->where('question_id', $questionId)
                        ->whereIn('id', $ids)
                        ->pluck('id')
                        ->all();

                    if (empty($validIds)) {
                        continue;
                    }

                    $resp = new EvaluationResponse();
                    $resp->evaluation_id = $evaluation->id;
                    $resp->form_id       = $form->id;
                    $resp->question_id   = $questionId;
                    $resp->save();

                    foreach ($validIds as $oid) {
                        EvaluationResponseOption::create([
                            'response_id'      => $resp->id,
                            'answer_option_id' => $oid,
                        ]);
                    }

                    continue; // done with multi-choice
                }

                // Other types: prepare a row only if there is an actual value
                $resp = new EvaluationResponse();
                $resp->evaluation_id = $evaluation->id;
                $resp->form_id       = $form->id;
                $resp->question_id   = $questionId;

                $touched = false;

                switch ($q->type) {
                    case 'single_choice':
                    case 'boolean':
                        $optId = Arr::get($answer, 'answer_option_id');
                        if ($optId) {
                            $valid = AnswerOption::query()
                                ->where('id', $optId)
                                ->where('question_id', $questionId)
                                ->exists();
                            if ($valid) {
                                $resp->answer_option_id = (int) $optId;
                                $touched = true;
                            }
                        }
                        break;

                    case 'scale':
                        $val = Arr::get($answer, 'answer_value');
                        if (is_numeric($val)) {
                            $resp->answer_value = (float) $val;
                            $touched = true;
                        }
                        break;

                    case 'text':
                        $txt = Arr::get($answer, 'answer_text');
                        if (is_string($txt) && $txt !== '') {
                            $resp->answer_text = $txt;
                            $touched = true;
                        }
                        break;

                    default:
                        // Custom types: store any structured value
                        $json = Arr::get($answer, 'answer_json');
                        if (!is_null($json)) {
                            $resp->answer_json = $json;
                            $touched = true;
                        }
                        break;
                }

                if ($touched) {
                    $resp->save();
                }
                // else: skip saving an empty/invalid response
            }

            // Compute scores & flags
            $calc = $this->scoring->score($evaluation);

            // Map internal keys → public labels expected by your UI
            $displayScores = [
                'Comfort & Confidence' => $calc['category_scores'][ScoringService::CAT_CC] ?? null,
                'Sociability'          => $calc['category_scores'][ScoringService::CAT_SO] ?? null,
                'Trainability'         => $calc['category_scores'][ScoringService::CAT_TR] ?? null,
            ];

            // Store snapshot + metadata
            $evaluation->category_scores = $displayScores;
            $evaluation->red_flags       = array_values(array_unique($calc['red_flags'] ?? []));
            $evaluation->answers         = $payload;             // snapshot for audits/tinker

            $evaluation->save();

            event(new EvaluationSubmitted($evaluation->id));
        });
    }

    private function stringifyKeys(array $arr): array
    {
        $out = [];
        foreach ($arr as $k => $v) {
            $out[(string) $k] = $v;
        }
        return $out;
    }
}
