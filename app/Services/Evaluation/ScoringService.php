<?php

namespace App\Services\Evaluation;

use App\Models\Evaluation;
use App\Models\EvaluationResponse;
use Illuminate\Support\Arr;

class ScoringService
{
    public const CAT_CC = 'comfort_confidence';
    public const CAT_SO = 'sociability';
    public const CAT_TR = 'trainability';

    /** @return array{category_scores: array<string,int|null>, red_flags: string[]} */
    public function score(Evaluation $evaluation): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, EvaluationResponse> $responses */
        $responses = $evaluation->responses()
            ->with(['question', 'answerOption', 'responseOptions.answerOption'])
            ->get();

        $perQuestionScore = [
            self::CAT_CC => [],
            self::CAT_SO => [],
            self::CAT_TR => [],
        ];

        $flags = [];

        foreach ($responses as $resp) {
            $q = $resp->question;
            if (!$q) continue;

            $cat = strtolower((string) $q->category);
            if (!in_array($cat, [self::CAT_CC, self::CAT_SO, self::CAT_TR], true)) {
                // Questions in 'general' (or unknown) do not contribute to category scores
                continue;
            }

            $questionScore = null;

            switch ($q->type) {
                case 'single_choice':
                case 'boolean':
                    if ($resp->answerOption) {
                        $questionScore = $this->scoreFromOption($resp->answerOption->score_map ?? [], $cat);
                        $flags = array_values(array_unique(array_merge(
                            $flags,
                            (array) ($resp->answerOption->flags ?? [])
                        )));
                    }
                    break;

                case 'multi_choice':
                    $sum = 0;
                    $count = 0;
                    foreach ($resp->responseOptions as $ro) {
                        $opt = $ro->answerOption;
                        if (!$opt) continue;
                        $sum += $this->scoreFromOption($opt->score_map ?? [], $cat);
                        $flags = array_values(array_unique(array_merge(
                            $flags,
                            (array) ($opt->flags ?? [])
                        )));
                        $count++;
                    }
                    if ($count > 0) {
                        // Average across selected options for this *question*
                        $questionScore = (int) round($sum / $count);
                    }
                    break;

                case 'scale':
                    $questionScore = $this->mapScaleToHundred($resp->answer_value, $q->meta ?? []);
                    break;

                // text / custom: no score
            }

            if (is_numeric($questionScore)) {
                $perQuestionScore[$cat][] = $this->clamp((int) round($questionScore), 0, 100);
            }
        }

        // Average across questions per category (not sum)
        $categoryScores = [];
        foreach ([self::CAT_CC, self::CAT_SO, self::CAT_TR] as $cat) {
            $arr = $perQuestionScore[$cat] ?? [];
            $categoryScores[$cat] = count($arr) === 0
                ? null
                : $this->clamp((int) round(array_sum($arr) / count($arr)), 0, 100);
        }

        return [
            'category_scores' => $categoryScores,
            'red_flags'       => $flags,
        ];
    }

    private function scoreFromOption(array $scoreMap, string $cat): int
    {
        $val = (int) ($scoreMap[$cat] ?? 0);
        return $this->clamp($val, 0, 100);
    }

    private function mapScaleToHundred($value, array $meta): ?int
    {
        if (!is_numeric($value)) return null;
        $min    = is_numeric(Arr::get($meta, 'min')) ? (float) $meta['min'] : 0.0;
        $max    = is_numeric(Arr::get($meta, 'max')) ? (float) $meta['max'] : 100.0;
        $invert = (bool) Arr::get($meta, 'invert', false);
        if ($max == $min) return null;

        $v = (float) $value;
        $norm = ($v - $min) / ($max - $min);
        $norm = max(0.0, min(1.0, $norm));
        if ($invert) $norm = 1.0 - $norm;

        return $this->clamp((int) round($norm * 100), 0, 100);
    }

    private function clamp(int $v, int $lo, int $hi): int
    {
        return max($lo, min($hi, $v));
    }
}
