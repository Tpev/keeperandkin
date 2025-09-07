<?php

namespace Database\Seeders;

use App\Models\EvaluationOptionParam;
use Illuminate\Database\Seeder;

class EvalOptionParamsSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = config('dog_eval', []);

        foreach ($catalog as $categoryKey => $questions) {
            foreach ($questions as $qKey => $q) {
                $options = $q['options'] ?? [];
                foreach ($options as $optKey => $opt) {
                    EvaluationOptionParam::updateOrCreate(
                        ['question_key' => $qKey, 'option_key' => (string)$optKey],
                        [
                            'category_key'      => $categoryKey,
                            'weight'            => 1,
                            'training_category' => null,
                            'flags'             => [],
                        ]
                    );
                }
            }
        }
    }
}
