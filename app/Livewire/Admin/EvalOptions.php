<?php

namespace App\Livewire\Admin;

use App\Models\EvaluationOptionParam;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class EvalOptions extends Component
{
    use WithPagination;

    #[Url] public string $search = '';
    public int $perPage = 25;

    /** Inline form buffer keyed by "question_key|option_key" */
    public array $form = [];

    public function mount(): void
    {
        $this->primeFromConfigIfMissing();
    }

    /** Ensure table has one row per config option */
    protected function primeFromConfigIfMissing(): void
    {
        if (EvaluationOptionParam::query()->exists()) return;

        $catalog = config('dog_eval', []);
        foreach ($catalog as $categoryKey => $questions) {
            foreach ($questions as $questionKey => $q) {
                foreach (($q['options'] ?? []) as $optionKey => $opt) {
                    $defaultWeight = (isset($opt['score']) && is_int($opt['score'])) ? (int) $opt['score'] : 0;
                    EvaluationOptionParam::firstOrCreate(
                        [
                            'category_key' => (string) $categoryKey,
                            'question_key' => (string) $questionKey,
                            'option_key'   => (string) $optionKey,
                        ],
                        [
                            'weight'         => $defaultWeight,
                            'training_tags'  => [],
                            'red_flags'      => [],
                            'flags'          => [],        // legacy, keep empty
                            'training_category' => null,   // legacy
                        ]
                    );
                }
            }
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function saveRow(string $questionKey, string $optionKey): void
    {
        $compound = $questionKey.'|'.$optionKey;
        $buf = $this->form[$compound] ?? [];

        $param = EvaluationOptionParam::where('question_key', $questionKey)
            ->where('option_key', $optionKey)
            ->firstOrFail();

        // Weight
        $param->weight = max(0, (int)($buf['weight'] ?? $param->weight));

        // NEW arrays
        $param->training_tags = array_values(array_filter((array)($buf['training_tags'] ?? $param->training_tags ?? [])));
        $param->red_flags     = array_values(array_filter((array)($buf['red_flags']     ?? $param->red_flags     ?? [])));

        // Legacy text fields (optional to keep in sync or drop)
        if (array_key_exists('training_category', $buf)) {
            $tc = trim((string)$buf['training_category']);
            $param->training_category = $tc !== '' ? $tc : null;
        }

        // If you still want to keep generic flags_str input in UI, map it here
        if (array_key_exists('flags_str', $buf)) {
            $flagsStr = (string) $buf['flags_str'];
            $param->flags = array_values(array_filter(array_map('trim', explode(',', $flagsStr))));
        }

        $param->save();

        session()->flash('success', "Saved: {$questionKey} / {$optionKey}");
    }

    public function render()
    {
        // Build options for TallStackUI selects from config
        $trainingOptions = collect(config('eval_taxonomy.training', []))
            ->map(fn ($k) => ['label' => ucwords(str_replace('_',' ', $k)), 'value' => $k])
            ->values()
            ->all();

        $redFlagOptions = collect(config('eval_taxonomy.red_flags', []))
            ->map(fn ($k) => ['label' => ucwords(str_replace('_',' ', $k)), 'value' => $k])
            ->values()
            ->all();

        $term = trim($this->search);

        $params = EvaluationOptionParam::query()
            ->when($term !== '', function ($q) use ($term) {
                $s = '%'.$term.'%';
                $q->where(function ($qq) use ($s) {
                    $qq->where('question_key', 'like', $s)
                       ->orWhere('option_key', 'like', $s)
                       ->orWhere('category_key', 'like', $s);
                });
            })
            ->orderBy('category_key')
            ->orderBy('question_key')
            ->orderBy('option_key')
            ->paginate($this->perPage);

        // Decorate rows with config text/labels + prime form buffer
        $cfg = config('dog_eval', []);

        $items = $params->getCollection()->map(function ($p) use ($cfg) {
            $text = $type = $label = '';

            if (isset($cfg[$p->category_key][$p->question_key])) {
                $qcfg  = $cfg[$p->category_key][$p->question_key];
                $text  = (string)($qcfg['text'] ?? '');
                $type  = (string)($qcfg['type'] ?? '');
                $label = (string)($qcfg['options'][$p->option_key]['label'] ?? '');
            }

            $compound = $p->question_key.'|'.$p->option_key;

            if (!isset($this->form[$compound])) {
                $this->form[$compound] = [
                    'weight'            => $p->weight,
                    'training_tags'     => $p->training_tags ?? [],
                    'red_flags'         => $p->red_flags ?? [],
                    // legacy helpers if you still show them
                    'training_category' => $p->training_category ?? '',
                    'flags_str'         => implode(',', $p->flags ?? []),
                ];
            }

            return [
                'category_key'      => $p->category_key,
                'question_key'      => $p->question_key,
                'option_key'        => $p->option_key,
                'text'              => $text,
                'type'              => $type,
                'label'             => $label,
                'weight'            => $p->weight,
                'training_tags'     => $p->training_tags ?? [],
                'red_flags'         => $p->red_flags ?? [],
                'compound'          => $compound,
            ];
        });

        return view('livewire.admin.eval-options', [
            'items'           => $items,
            'params'          => $params,
            'trainingOptions' => $trainingOptions,
            'redFlagOptions'  => $redFlagOptions,
        ])->layout('layouts.app');
    }
}
