<?php

namespace App\Livewire\Dogs;

use App\Models\Evaluation;
use Illuminate\Support\Arr;
use Livewire\Attributes\On;
use Livewire\Component;

class EvaluationViewer extends Component
{
    public bool $open = false;

    public ?int $evaluationId = null;

    /** @var array<string, mixed> */
    public array $header = [
        'date' => null,
        'scores' => [
            'Comfort & Confidence' => null,
            'Sociability'          => null,
            'Trainability'         => null,
        ],
    ];

    /** @var array<int, array{question:string, answer:string|null, notes:string|null}> */
    public array $qa = [];

    /**
     * Accept either:
     * - open(123)
     * - open(['id' => 123])
     * - open((object)['id' => 123])
     */
    #[On('open-eval')]
    public function open($payload = null): void
    {
        $this->resetToDefaults();

        // Normalize to an ID
        $id = null;

        if (is_scalar($payload)) {
            $id = (int) $payload;
        } elseif (is_array($payload)) {
            $id = isset($payload['id']) ? (int) $payload['id'] : null;
        } elseif (is_object($payload)) {
            $id = isset($payload->id) ? (int) $payload->id : null;
        }

        $this->evaluationId = $id;

        /** @var \App\Models\Evaluation|null $eval */
        $eval = $id ? Evaluation::query()->find($id) : null;

        if (!$eval) {
            $this->open = true; // open with empty state
            return;
        }

        // Header
        $this->header['date'] = optional($eval->created_at)->format('M d, Y');

        $cats = (array) ($eval->category_scores ?? []);

        $this->header['scores'] = [
            'Comfort & Confidence' => $this->pickInt($cats, ['Comfort & Confidence', 'Confidence', 'comfort_confidence']),
            'Sociability'          => $this->pickInt($cats, ['Sociability', 'Social', 'sociability']),
            'Trainability'         => $this->pickInt($cats, ['Trainability', 'trainability']),
        ];

        // Answers – support a few common shapes
        $rawAnswers = [];

        if (is_array($eval->answers ?? null)) {
            $rawAnswers = $eval->answers;
        } elseif (is_array($eval->data ?? null) && is_array(($eval->data)['answers'] ?? null)) {
            $rawAnswers = $eval->data['answers'];
        } elseif (method_exists($eval, 'answers')) {
            try {
                $rel = $eval->answers; // relation or attribute
                if ($rel) {
                    $rawAnswers = $rel instanceof \Illuminate\Support\Collection ? $rel->toArray() : (array) $rel;
                }
            } catch (\Throwable $e) {
                // ignore if relation missing
            }
        }

        $this->qa = collect($rawAnswers)
            ->map(function ($row) {
                $q = Arr::get($row, 'question') ?? Arr::get($row, 'label') ?? Arr::get($row, 'q') ?? '';
                $a = Arr::get($row, 'answer')   ?? Arr::get($row, 'value') ?? Arr::get($row, 'a') ?? null;

                if (is_array($a)) {
                    $a = implode(', ', array_filter($a, fn($v) => $v !== null && $v !== ''));
                }
                if (is_bool($a)) {
                    $a = $a ? 'Yes' : 'No';
                }

                $notes = Arr::get($row, 'notes') ?? Arr::get($row, 'comment') ?? null;

                return [
                    'question' => (string) $q,
                    'answer'   => $a !== null && $a !== '' ? (string) $a : null,
                    'notes'    => $notes !== null && $notes !== '' ? (string) $notes : null,
                ];
            })
            ->filter(fn ($r) => trim($r['question']) !== '')
            ->values()
            ->all();

        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function render()
    {
        return view('livewire.dogs.evaluation-viewer');
    }

    // --- helpers

    private function resetToDefaults(): void
    {
        $this->open = false;
        $this->header = [
            'date' => null,
            'scores' => [
                'Comfort & Confidence' => null,
                'Sociability'          => null,
                'Trainability'         => null,
            ],
        ];
        $this->qa = [];
        $this->evaluationId = null;
    }

    private function pickInt(array $a, array $keys): ?int
    {
        foreach ($keys as $k) {
            if (array_key_exists($k, $a) && $a[$k] !== null && $a[$k] !== '') {
                return (int) $a[$k];
            }
        }
        return null;
    }
}
