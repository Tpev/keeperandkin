<?php

namespace App\Livewire\Admin;

use App\Models\EvaluationQuestionParam;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class EvalQuestions extends Component
{
    use WithPagination;

    #[Url] public string $search = '';
    public int $perPage = 25;

    /** Inline form buffer keyed by question_key */
    public array $form = [];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function saveRow(string $questionKey): void
    {
        $param = EvaluationQuestionParam::where('question_key', $questionKey)->firstOrFail();

        $buf = $this->form[$questionKey] ?? [];

        // weight
        $param->weight = max(0, (int)($buf['weight'] ?? $param->weight));

        // training_category
        $tc = trim((string)($buf['training_category'] ?? $param->training_category));
        $param->training_category = $tc !== '' ? $tc : null;

        // flags string -> array
        $flagsStr = (string)($buf['flags_str'] ?? '');
        $flags = array_values(array_filter(array_map('trim', explode(',', $flagsStr))));
        $param->flags = $flags;

        $param->save();

        session()->flash('success', "Saved: {$questionKey}");
    }

    public function render()
    {
        // Native Eloquent pagination (so Livewire manages ?page)
        $query = EvaluationQuestionParam::query()
            ->when($this->search, function ($q) {
                $s = '%' . $this->search . '%';
                $q->where(function ($qq) use ($s) {
                    $qq->where('question_key', 'like', $s)
                       ->orWhere('category_key', 'like', $s);
                });
            })
            ->orderBy('category_key')
            ->orderBy('question_key');

        $params = $query->paginate($this->perPage);

        // Decorate each row with the config text/type; preload config once
        $cfg = config('dog_eval', []);

        $items = $params->getCollection()->map(function ($p) use ($cfg) {
            $text = '';
            $type = '';
            if (isset($cfg[$p->category_key][$p->question_key])) {
                $qcfg = $cfg[$p->category_key][$p->question_key];
                $text = (string)($qcfg['text'] ?? '');
                $type = (string)($qcfg['type'] ?? '');
            }

            return [
                'category_key'      => $p->category_key,
                'question_key'      => $p->question_key,
                'text'              => $text,
                'type'              => $type,
                'weight'            => $p->weight,
                'training_category' => $p->training_category ?? '',
                'flags'             => $p->flags ?? [],
                'flags_str'         => implode(',', $p->flags ?? []),
            ];
        });

        return view('livewire.admin.eval-questions', [
            'items'  => $items,
            'params' => $params, // for {{ $params->links() }}
        ])->layout('layouts.app');
    }
}
