<?php

namespace App\Livewire\Dogs;

use Livewire\Component;
use App\Models\Dog;
use App\Models\Evaluation;
use Illuminate\Support\Facades\Auth;

class EvaluationForm extends Component
{
    public Dog $dog;

    /* ---------- static QCM catalog ---------- */
    public static array $catalog = [
        'Temperament' => [
            'calm_indoors' => [
                'text'    => 'Is the dog calm indoors?',
                'options' => ['always' => 5, 'sometimes' => 3, 'never' => 0],
            ],
            // … add more questions
        ],
        'Sociability' => [
            'gets_along_dogs' => [
                'text'    => 'Gets along with other dogs?',
                'options' => ['always' => 5, 'sometimes' => 3, 'never' => 0],
            ],
        ],
        'Health & Fitness' => [
            'healthy_weight' => [
                'text'    => 'Maintains healthy weight?',
                'options' => ['always' => 5, 'sometimes' => 3, 'never' => 0],
            ],
        ],
    ];

    /* ---------- form state ---------- */
    public array $answers = [];   // ['calm_indoors' => 'always', …]

    /* ---------- derived props ---------- */
    public function getCatalogProperty(): array
    {
        return static::$catalog;
    }

    public function getMaxScoreProperty(): int
    {
        return collect(static::$catalog)
            ->flatMap(fn ($q) => $q)
            ->sum(fn ($q) => max($q['options']));
    }

    public function getLiveScoreProperty(): int
    {
        $score = 0;

        foreach ($this->answers as $qKey => $optKey) {
            foreach (static::$catalog as $category) {
                if (isset($category[$qKey]['options'][$optKey])) {
                    $score += $category[$qKey]['options'][$optKey];
                    break;
                }
            }
        }

        return $score;
    }

    public function getScorePercentProperty(): int
    {
        return $this->maxScore
            ? intval(($this->liveScore / $this->maxScore) * 100)
            : 0;
    }

    /* ---------- validation ---------- */
    public function rules(): array
    {
        $rules = [];
        foreach (static::$catalog as $category) {
            foreach ($category as $key => $q) {
                $rules["answers.$key"] = [
                    'required',
                    'in:' . implode(',', array_keys($q['options'])),
                ];
            }
        }
        return $rules;
    }

    /* ---------- save ---------- */
    public function submit(): void
    {
        $this->validate();

        Evaluation::create([
            'dog_id'  => $this->dog->id,
            'user_id' => Auth::id(),
            'score'   => $this->liveScore,
            'answers' => $this->answers,
        ]);

        session()->flash(
            'success',
            "Evaluation saved (score {$this->liveScore} / {$this->maxScore})"
        );

        $this->redirectRoute('dogs.show', $this->dog);
    }

    /* ---------- render ---------- */
    public function render()
    {
        return view('livewire.dogs.evaluation-form');
    }
}
