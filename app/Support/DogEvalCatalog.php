<?php

namespace App\Support;

use App\Models\EvaluationOptionParam;

class DogEvalCatalog
{
    /** Return catalog with per-option admin params merged. */
    public static function catalog(): array
    {
        $base = config('dog_eval', []);

        // Load per-option params keyed by "question_key|option_key"
        $params = EvaluationOptionParam::query()
            ->get()
            ->keyBy(fn ($p) => $p->question_key.'|'.$p->option_key);

        foreach ($base as $categoryKey => &$questions) {
            foreach ($questions as $qKey => &$q) {
                // ensure options exists
                $q['options'] = $q['options'] ?? [];

                foreach ($q['options'] as $optKey => &$opt) {
                    $pk = $qKey.'|'.$optKey;
                    $p  = $params->get($pk);

                    $opt['admin'] = [
                        'weight'            => $p->weight ?? 1,
                        'training_category' => $p->training_category ?? null,
                        'flags'             => $p->flags ?? [],
                    ];

                    // keep meta (useful in UIs)
                    $opt['_meta'] = [
                        'category_key' => $categoryKey,
                        'question_key' => $qKey,
                        'option_key'   => (string)$optKey,
                    ];
                }

                // keep meta at question level too (useful elsewhere)
                $q['_meta'] = [
                    'category_key' => $categoryKey,
                    'question_key' => $qKey,
                ];
            }
        }

        return $base;
    }

    /** Flat list of options with merged admin data. */
    public static function flatOptions(): array
    {
        $out = [];
        foreach (self::catalog() as $categoryKey => $questions) {
            foreach ($questions as $qKey => $q) {
                foreach (($q['options'] ?? []) as $optKey => $opt) {
                    $out[] = array_merge($opt, [
                        '_meta' => [
                            'category_key' => $categoryKey,
                            'question_key' => $qKey,
                            'option_key'   => (string)$optKey,
                        ],
                    ]);
                }
            }
        }
        return $out;
    }
}
