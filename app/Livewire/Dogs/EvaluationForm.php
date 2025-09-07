<?php

namespace App\Livewire\Dogs;

use Livewire\Component;
use App\Models\Dog;
use App\Models\Evaluation;
use App\Models\EvaluationOptionParam;
use Illuminate\Support\Facades\Auth;
use App\Support\DogEvalCatalog;

class EvaluationForm extends Component
{
    public Dog $dog;

    /** Wizard state (1..N) */
    public int $step = 1;

    /** User selections: radio => string, checkbox => array */
    public array $answers = [];

    /* ---------- lifecycle ---------- */

    public function mount(Dog $dog): void
    {
        $this->dog = $dog;

        // Initialize all checkbox questions as empty arrays
        foreach (config('dog_eval', []) as $category) {
            foreach ($category as $key => $q) {
                if (($q['type'] ?? null) === 'checkbox') {
                    if (!isset($this->answers[$key]) || !is_array($this->answers[$key])) {
                        $this->answers[$key] = [];
                    }
                }
            }
        }
    }

    /* ---------- derived props (catalog & wizard) ---------- */

    /** Entire catalog from config (grouped by category) */
    public function getCatalogProperty(): array
    {
        return DogEvalCatalog::catalog();
    }

    /** Flat list of question keys (for param queries) */
    public function getQuestionKeysProperty(): array
    {
        return collect($this->catalog)->flatMap(fn ($group) => array_keys($group))->values()->all();
    }

    /**
     * Map of per-option admin params keyed by "question|option":
     *  [ 'q_key|opt_key' => ['weight'=>int,'flags'=>array] ]
     */
public function getParamMapProperty(): array
{
    $rows = EvaluationOptionParam::whereIn('question_key', $this->questionKeys)->get();

    $map = [];
    foreach ($rows as $p) {
        // thanks to model casts this is already an array, but be defensive
        $red = is_array($p->red_flags) ? $p->red_flags : (json_decode($p->red_flags ?? '[]', true) ?: []);

        $map[$p->question_key.'|'.$p->option_key] = [
            'weight'     => (int) ($p->weight ?? 1),
            'red_flags'  => array_values(array_filter($red)),
        ];
    }
    return $map;
}


    /** Step count = number of top-level categories */
    public function getStepsCountProperty(): int
    {
        return count($this->catalog);
    }

    /** Ordered category keys: e.g. ["Confidence","Sociability","Trainability"] */
    public function getCategoryKeysProperty(): array
    {
        return array_keys($this->catalog);
    }

    /** Current category key based on $step (1-indexed) */
    public function getCurrentCategoryKeyProperty(): ?string
    {
        return $this->categoryKeys[$this->step - 1] ?? null;
    }

    /** Questions for current category */
    public function getCurrentQuestionsProperty(): array
    {
        return $this->catalog[$this->currentCategoryKey] ?? [];
    }

    /* ---------- scoring ---------- */
/** Per-category live (raw) score based on current answers */
public function getCategoryRawScoresProperty(): array
{
    $out = [];

    foreach ($this->catalog as $catName => $group) {
        $raw = 0;

        foreach ($group as $qKey => $q) {
            if (($q['type'] ?? null) !== 'radio') continue;

            $selected = $this->answers[$qKey] ?? null;
            if ($selected === null || $selected === '') continue;

            $optMeta = $q['options'][$selected] ?? null;
            $base = (is_array($optMeta) && array_key_exists('score', $optMeta) && is_int($optMeta['score']))
                ? $optMeta['score']
                : 0;

            if ($base <= 0) continue;

            $w = (int) ($this->paramMap[$qKey.'|'.$selected]['weight'] ?? 1);
            $w = max(1, $w);

            $raw += $base * $w;
        }

        $out[$catName] = $raw;
    }

    return $out;
}

/** Per-category maximum possible score (radio questions only) */
public function getCategoryMaxScoresProperty(): array
{
    $out = [];

    foreach ($this->catalog as $catName => $group) {
        $maxCat = 0;

        foreach ($group as $qKey => $q) {
            if (($q['type'] ?? null) !== 'radio') continue;

            $maxForQuestion = 0;
            foreach (($q['options'] ?? []) as $optKey => $optMeta) {
                $base = (is_array($optMeta) && array_key_exists('score', $optMeta) && is_int($optMeta['score']))
                    ? $optMeta['score']
                    : 0;

                if ($base <= 0) continue;

                $w = (int) ($this->paramMap[$qKey.'|'.$optKey]['weight'] ?? 1);
                $w = max(1, $w);

                $maxForQuestion = max($maxForQuestion, $base * $w);
            }

            $maxCat += $maxForQuestion;
        }

        $out[$catName] = $maxCat;
    }

    return $out;
}

/** Per-category normalized % (0..100) for the three big categories */
public function getCategoryPercentsProperty(): array
{
    $raw  = $this->categoryRawScores;
    $max  = $this->categoryMaxScores;

    $toPercent = function (int $r, int $m): int {
        return $m > 0 ? (int) round(($r / $m) * 100) : 0;
    };

    $out = [];
    foreach ($this->catalog as $catName => $group) {
        $out[$catName] = $toPercent((int) ($raw[$catName] ?? 0), (int) ($max[$catName] ?? 0));
    }

    return $out;
}

/** Global normalized % (0..100) */
public function getGlobalPercentProperty(): int
{
    return $this->maxScore > 0
        ? (int) round(($this->liveScore / $this->maxScore) * 100)
        : 0;
}

    /** Max possible score across all radio questions (uses DB weights; ignores null scores like N/A) */
    public function getMaxScoreProperty(): int
    {
        $total = 0;

        foreach ($this->catalog as $group) {
            foreach ($group as $qKey => $q) {
                if (($q['type'] ?? null) !== 'radio') continue;

                $maxForQuestion = 0;

                foreach (($q['options'] ?? []) as $optKey => $optMeta) {
                    $base = (is_array($optMeta) && array_key_exists('score', $optMeta) && is_int($optMeta['score']))
                        ? $optMeta['score']
                        : 0;

                    if ($base <= 0) continue;

                    $w = (int) ($this->paramMap[$qKey.'|'.$optKey]['weight'] ?? 1);
                    $w = max(1, $w);

                    $maxForQuestion = max($maxForQuestion, $base * $w);
                }

                $total += $maxForQuestion;
            }
        }

        return $total;
    }

    /** Live score based on current answers (radio only; checkbox not scored here) */
    public function getLiveScoreProperty(): int
    {
        $score = 0;
        $flat  = collect($this->catalog)->flatMap(fn ($group) => $group);

        foreach ($this->answers as $qKey => $optValue) {
            $question = $flat[$qKey] ?? null;
            if (!$question || ($question['type'] ?? null) !== 'radio') continue;

            $optMeta = $question['options'][$optValue] ?? null;

            $base = (is_array($optMeta) && array_key_exists('score', $optMeta) && is_int($optMeta['score']))
                ? $optMeta['score']
                : 0;

            if ($base <= 0) continue;

            $w = (int) ($this->paramMap[$qKey.'|'.$optValue]['weight'] ?? 1);
            $w = max(1, $w);

            $score += $base * $w;
        }

        return $score;
    }

    /* ---------- completion progress ---------- */

    public function getTotalQuestionsProperty(): int
    {
        return collect($this->catalog)->sum(fn ($group) => count($group));
    }

    public function getAnsweredQuestionsProperty(): int
    {
        $flat = collect($this->catalog)->flatMap(fn ($group) => $group);
        $answered = 0;

        foreach ($flat as $key => $q) {
            $type = $q['type'] ?? null;
            if ($type === 'radio') {
                if (array_key_exists($key, $this->answers) && $this->answers[$key] !== null && $this->answers[$key] !== '') {
                    $answered++;
                }
            } elseif ($type === 'checkbox') {
                $val = $this->answers[$key] ?? null;
                if (is_array($val) && count($val) > 0) {
                    $answered++;
                }
            }
        }
        return $answered;
    }

    public function getProgressPercentProperty(): int
    {
        return $this->totalQuestions > 0
            ? (int) floor(($this->answeredQuestions / $this->totalQuestions) * 100)
            : 0;
    }

    /* ---------- validation helpers ---------- */

    public function rules(): array
    {
        $rules = [];
        foreach ($this->catalog as $category) {
            foreach ($category as $key => $q) {
                if (($q['type'] ?? null) === 'radio') {
                    $rules["answers.$key"] = ['required', 'in:' . implode(',', array_keys($q['options'] ?? []))];
                } elseif (($q['type'] ?? null) === 'checkbox') {
                    $rules["answers.$key"] = ['array'];
                }
            }
        }
        return $rules;
    }

    public function rulesStep(int $step = null): array
    {
        $step = $step ?: $this->step;
        $rules = [];
        $catKey = $this->categoryKeys[$step - 1] ?? null;
        if (!$catKey) return $rules;

        foreach ($this->catalog[$catKey] as $key => $q) {
            if (($q['type'] ?? null) === 'radio') {
                $rules["answers.$key"] = ['required', 'in:' . implode(',', array_keys($q['options'] ?? []))];
            } elseif (($q['type'] ?? null) === 'checkbox') {
                $rules["answers.$key"] = ['array'];
            }
        }
        return $rules;
    }

    protected function rulesUpTo(int $upto): array
    {
        $rules = [];
        $upto = max(1, min($upto, $this->stepsCount));

        for ($i = 1; $i <= $upto; $i++) {
            $catKey = $this->categoryKeys[$i - 1] ?? null;
            if (!$catKey) continue;

            foreach ($this->catalog[$catKey] as $key => $q) {
                if (($q['type'] ?? null) === 'radio') {
                    $rules["answers.$key"] = ['required', 'in:' . implode(',', array_keys($q['options'] ?? []))];
                } elseif (($q['type'] ?? null) === 'checkbox') {
                    $rules["answers.$key"] = ['array'];
                }
            }
        }

        return $rules;
    }

    /* ---------- wizard actions ---------- */

    public function nextStep(): void
    {
        $this->validate($this->rulesStep($this->step));
        if ($this->step < $this->stepsCount) {
            $this->resetErrorBag();
            $this->resetValidation();
            $this->step++;
        }
    }

    public function prevStep(): void
    {
        if ($this->step > 1) {
            $this->resetErrorBag();
            $this->resetValidation();
            $this->step--;
        }
    }

    public function goToStep(int $n): void
    {
        if ($n > $this->step) {
            $this->validate($this->rulesUpTo($n - 1));
        }

        if ($n >= 1 && $n <= $this->stepsCount) {
            $this->resetErrorBag();
            $this->resetValidation();
            $this->step = $n;
        }
    }

    /* ---------- save ---------- */

    public function submit(): void
    {
        // Ensure everything is valid before saving
        $this->validate($this->rules());

        // Build selected options per question
        $selectedByQ = [];
        $flat = collect($this->catalog)->flatMap(fn ($g) => $g);

        foreach ($flat as $qKey => $q) {
            $type = $q['type'] ?? null;

            if ($type === 'radio') {
                $opt = $this->answers[$qKey] ?? null;
                if ($opt !== null && $opt !== '') $selectedByQ[$qKey] = [$opt];
            } elseif ($type === 'checkbox') {
                $opts = $this->answers[$qKey] ?? [];
                if (is_array($opts) && $opts) $selectedByQ[$qKey] = array_values($opts);
            }
        }
$globalPercent     = $this->globalPercent;        
$categoryPercents  = $this->categoryPercents; 
// 🔴 Aggregate red_flags (no allow-list filtering)
$redFlags = [];
foreach ($selectedByQ as $qKey => $optKeys) {
    foreach ($optKeys as $optKey) {
        $p = $this->paramMap[$qKey.'|'.$optKey] ?? null;
        if (!$p) continue;

        foreach ((array) ($p['red_flags'] ?? []) as $flag) {
            if ($flag !== null && $flag !== '') {
                $redFlags[] = $flag;
            }
        }
    }
}
$redFlags = array_values(array_unique($redFlags));


        Evaluation::create([
            'dog_id'    => $this->dog->id,
            'user_id'   => Auth::id(),
                'score'           => $globalPercent,       // ✅ 0..100
    'category_scores' => $categoryPercents,    // ✅ per big category
            'answers'   => $this->answers,
            'red_flags' => $redFlags, // ✅ snapshot from taxonomy-backed admin flags
        ]);

        session()->flash('success', "Evaluation saved (score {$this->liveScore} / {$this->maxScore})");

        $this->redirectRoute('dogs.show', $this->dog);
    }

    public function render()
    {
        return view('livewire.dogs.evaluation-form');
    }
}
