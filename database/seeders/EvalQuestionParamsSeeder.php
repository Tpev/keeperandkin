<?php

namespace Database\Seeders;

use App\Models\EvaluationQuestionParam;
use Illuminate\Database\Seeder;

class EvalQuestionParamsSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = config('dog_eval', []);

        foreach ($catalog as $categoryKey => $questions) {
            foreach ($questions as $qKey => $q) {
                EvaluationQuestionParam::updateOrCreate(
                    ['question_key' => $qKey],
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
