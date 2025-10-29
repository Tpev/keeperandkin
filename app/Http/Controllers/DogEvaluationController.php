<?php

namespace App\Http\Controllers;

use App\Models\Dog;
use App\Models\Evaluation;
use App\Support\DogEvalCatalog;
use App\Services\Evaluation\FormProvider;
use Illuminate\Support\Facades\Auth;

class DogEvaluationController extends Controller
{
    public function show(Dog $dog, Evaluation $evaluation, FormProvider $forms)
    {
        // Must belong to this dog
        abort_if($evaluation->dog_id !== $dog->id, 404);

        // -------- Authorization by team_id or is_admin --------
        $user    = Auth::user();
        $teamId  = (int) ($dog->team_id ?? 0);
        $isAdmin = (bool) ($user->is_admin ?? false);

        // User must be admin OR a member of the dog's team
        $onTeam = false;
        if ($user && $teamId) {
            $onTeam = (optional($user->currentTeam)->id === $teamId)
                || $user->teams()->whereKey($teamId)->exists();
        }

        abort_unless($isAdmin || $onTeam, 403, 'You are not authorized to view this evaluation.');

        // ----- Scores (unchanged) -----
        $scores = (array) ($evaluation->category_scores ?? []);
        $pick = function(array $a, array $keys){
            foreach ($keys as $k) {
                if (array_key_exists($k, $a) && $a[$k] !== null && $a[$k] !== '') {
                    return (int) $a[$k];
                }
            }
            return null;
        };
        $displayScores = [
            'Comfort & Confidence' => $pick($scores, ['Comfort & Confidence','Confidence','comfort_confidence']),
            'Sociability'          => $pick($scores, ['Sociability','Social','sociability']),
            'Trainability'         => $pick($scores, ['Trainability','trainability']),
        ];

        // ----- Answers normalization (DB-form path or Legacy) -----
        $rawAnswers = (array) ($evaluation->answers ?? []);
        $answers = [];

        if ($this->looksLikeDbFormAnswers($rawAnswers)) {
            $dto = $forms->activeFormForTeam(null) ?: ['form'=>null,'sections'=>[]];

            $qById = [];
            $optByQ = [];
            foreach (($dto['sections'] ?? []) as $sec) {
                foreach (($sec['questions'] ?? []) as $q) {
                    $qid = (int) ($q['id'] ?? 0);
                    if (!$qid) continue;
                    $qById[$qid] = [
                        'prompt'  => $q['prompt'] ?? 'Question',
                        'type'    => $q['type'] ?? null,
                    ];
                    $optByQ[$qid] = [];
                    foreach (($q['options'] ?? []) as $o) {
                        $optByQ[$qid][(int)($o['id'] ?? 0)] = $o['label'] ?? ('Option #'.($o['id'] ?? '?'));
                    }
                }
            }

            foreach ($rawAnswers as $qid => $ans) {
                $qid = (int) $qid;
                $meta   = $qById[$qid] ?? ['prompt'=>'Question','type'=>null];
                $prompt = $meta['prompt'];

                $answerText = '';
                $notes      = '';

                if (is_array($ans)) {
                    if (array_key_exists('answer_option_id', $ans)) {
                        $oid = (int) ($ans['answer_option_id'] ?? 0);
                        $answerText = $optByQ[$qid][$oid] ?? (string) $oid;
                    } elseif (array_key_exists('answer_option_ids', $ans)) {
                        $ids = array_map('intval', (array) ($ans['answer_option_ids'] ?? []));
                        $labels = [];
                        foreach ($ids as $oid) {
                            $labels[] = $optByQ[$qid][$oid] ?? (string) $oid;
                        }
                        $answerText = implode(', ', array_filter($labels));
                    } elseif (array_key_exists('answer_value', $ans)) {
                        $answerText = (string) $ans['answer_value'];
                    } elseif (array_key_exists('answer_text', $ans)) {
                        $answerText = (string) $ans['answer_text'];
                    } elseif (array_key_exists('answer_json', $ans)) {
                        $answerText = is_scalar($ans['answer_json'])
                            ? (string) $ans['answer_json']
                            : json_encode($ans['answer_json']);
                    }

                    if (array_key_exists('notes', $ans)) {
                        $notes = is_scalar($ans['notes']) ? (string) $ans['notes'] : json_encode($ans['notes']);
                    }
                } else {
                    $answerText = (string) $ans;
                }

                $answers[] = [
                    'question' => $prompt,
                    'answer'   => $answerText,
                    'notes'    => $notes,
                ];
            }
        } else {
            $catalog = DogEvalCatalog::catalog();
            $flat = [];
            foreach ($catalog as $cat) {
                foreach ($cat as $qKey => $q) {
                    $flat[$qKey] = $q;
                }
            }

            foreach ($rawAnswers as $qKey => $val) {
                $qMeta = $flat[$qKey] ?? null;
                if (!$qMeta) {
                    $answers[] = ['question' => $qKey, 'answer' => is_array($val) ? implode(', ', $val) : (string) $val, 'notes' => ''];
                    continue;
                }

                $prompt = $qMeta['text'] ?? $qKey;
                $type   = $qMeta['type'] ?? null;
                $opts   = $qMeta['options'] ?? [];

                if ($type === 'radio') {
                    $label = $opts[$val]['label'] ?? (string) $val;
                    $answers[] = ['question' => $prompt, 'answer' => $label, 'notes' => ''];
                } elseif ($type === 'checkbox') {
                    $labels = [];
                    foreach ((array) $val as $k) {
                        $labels[] = $opts[$k]['label'] ?? (string) $k;
                    }
                    $answers[] = ['question' => $prompt, 'answer' => implode(', ', array_filter($labels)), 'notes' => ''];
                } else {
                    $answers[] = ['question' => $prompt, 'answer' => is_array($val) ? json_encode($val) : (string) $val, 'notes' => ''];
                }
            }
        }

        return view('dogs.evaluations.show', [
            'dog'           => $dog,
            'evaluation'    => $evaluation,
            'displayScores' => $displayScores,
            'answers'       => $answers,
        ]);
    }

    /** Heuristic: DB-form answers have numeric keys and sub-arrays with known fields */
    protected function looksLikeDbFormAnswers(array $answers): bool
    {
        if ($answers === []) return false;

        foreach (array_keys($answers) as $k) {
            if (!is_numeric($k)) return false;
        }

        $first = reset($answers);
        if (!is_array($first)) return false;

        $known = ['answer_option_id','answer_option_ids','answer_value','answer_text','answer_json'];
        foreach ($known as $key) {
            if (array_key_exists($key, $first)) return true;
        }
        return false;
    }
}
