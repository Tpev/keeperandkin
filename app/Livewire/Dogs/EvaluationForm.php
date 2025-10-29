<?php

namespace App\Livewire\Dogs;

use Livewire\Component;
use App\Models\Dog;
use App\Models\Evaluation;
use Illuminate\Support\Facades\Auth;
use App\Support\DogEvalCatalog;

// Phase 4 additions
use App\Services\Evaluation\FormProvider;
use App\Actions\Evaluations\PersistEvaluationResponses;
use App\Models\EvaluationForm as DbEvaluationForm;

class EvaluationForm extends Component
{
    public Dog $dog;

    /** Wizard state (1..N) */
    public int $step = 1;

    /**
     * Unified answers structure (DB path keyed by question_id)
     *
     *   single_choice / boolean:  [qid => ['answer_option_id' => int|null]]
     *   multi_choice:             [qid => ['answer_option_ids' => int[]]]
     *   scale:                    [qid => ['answer_value' => number|null]]
     *   text:                     [qid => ['answer_text' => string]]
     */
    public array $answers = [];

    /** ------- Phase 4 state ------- */
    public bool $useDbQuestions = false;

    // DB form dto: ['form'=>['id','name','version'], 'sections'=>[ ['id','title','position','questions'=>[...]] ]]
    public array $dbFormDto = [];

    // Flattened helper maps for DB path
    protected array $dbQuestionsById = [];       // question_id => question array (from DTO)
    protected array $dbOptionsByQid  = [];       // question_id => [options...]
    protected array $dbRequiredQids  = [];       // list of required qids (visibility=always only)
    protected array $typeByQid       = [];       // question_id => type

    /** ---- Follow-up graph (DB path) ---- */
    protected array $fqIdByQid          = [];    // question_id => form_question_id
    protected array $qidByFqId          = [];    // form_question_id => question_id
    protected array $fuRuleByChildFq    = [];    // child_fq_id => ['parent_fq_id','trigger_option_ids','required_mode','display_mode']
    protected array $childrenByParentFq = [];    // parent_fq_id => [child_fq_id, ...]

    /* ---------- lifecycle ---------- */

    public function mount(Dog $dog, FormProvider $forms): void
    {
        $this->dog = $dog;

        $this->useDbQuestions = (bool) config('kk.features.db_questions', false);

        if ($this->useDbQuestions) {
            $dto = $forms->activeFormForTeam(null);
            $this->dbFormDto = $dto ?? ['form'=>null,'sections'=>[]];

            $this->hydrateDbLookupsAndDefaults();
            $this->buildFollowUpGraphFromDto();
            // Ensure initial visibility cascades (clears hidden answers if any preload)
            $this->normalizeVisibilityForAll();
        } else {
            // LEGACY: init checkbox arrays
            foreach (config('dog_eval', []) as $category) {
                foreach ($category as $key => $q) {
                    if (($q['type'] ?? null) === 'checkbox') {
                        $this->answers[$key] = $this->answers[$key] ?? [];
                    }
                }
            }
        }
    }

    /** Build DB lookups, wizard steps, and default answer structures for DB path */
    protected function hydrateDbLookupsAndDefaults(): void
    {
        $this->dbQuestionsById = [];
        $this->dbOptionsByQid  = [];
        $this->dbRequiredQids  = [];
        $this->fqIdByQid       = [];
        $this->qidByFqId       = [];
        $this->typeByQid       = [];

        foreach (($this->dbFormDto['sections'] ?? []) as $section) {
            foreach (($section['questions'] ?? []) as $q) {
                // DTO fields: 'id' (question_id), 'form_question_id' (FQ id)
                $qid = (int) ($q['id'] ?? 0);
                $fq  = (int) ($q['form_question_id'] ?? 0);
                if ($qid <= 0) {
                    continue;
                }

                $this->dbQuestionsById[$qid] = $q;
                $this->dbOptionsByQid[$qid]  = $q['options'] ?? [];
                $this->typeByQid[$qid]       = $q['type'] ?? null;

                if ($fq > 0) {
                    $this->fqIdByQid[$qid] = $fq;
                    $this->qidByFqId[$fq]  = $qid;
                }

                if (!empty($q['required']) && ($q['visibility'] ?? 'always') === 'always') {
                    $this->dbRequiredQids[] = $qid;
                }

                // Initialize empty structures for known types (only if not set yet)
                if (!array_key_exists($qid, $this->answers)) {
                    switch ($q['type']) {
                        case 'multi_choice':
                            $this->answers[$qid] = ['answer_option_ids' => []];
                            break;
                        case 'scale':
                            $this->answers[$qid] = ['answer_value' => null];
                            break;
                        case 'text':
                            $this->answers[$qid] = ['answer_text' => ''];
                            break;
                        case 'single_choice':
                        case 'boolean':
                            $this->answers[$qid] = ['answer_option_id' => null];
                            break;
                        default:
                            $this->answers[$qid] = ['answer_json' => null];
                            break;
                    }
                }
            }
        }
    }

    /** Build follow-up maps from DTO if present */
    protected function buildFollowUpGraphFromDto(): void
    {
        $this->fuRuleByChildFq = [];
        $this->childrenByParentFq = [];

        foreach (($this->dbFormDto['sections'] ?? []) as $section) {
            foreach (($section['questions'] ?? []) as $q) {
                $fqId = (int) ($q['form_question_id'] ?? 0);
                $fu   = $q['follow_up'] ?? $q['follow_up_rule'] ?? null;
                if ($fqId <= 0 || !$fu || !is_array($fu)) {
                    continue;
                }

                // DTO uses parent_form_question_id (not parent_fq_id)
                $parentFq = (int) ($fu['parent_form_question_id'] ?? 0);
                $triggers = array_values(array_map('intval', (array)($fu['trigger_option_ids'] ?? [])));
                $reqMode  = in_array(($fu['required_mode'] ?? 'visible_only'), ['visible_only','always'], true)
                    ? $fu['required_mode'] : 'visible_only';
                $dispMode = in_array(($fu['display_mode'] ?? 'inline_after_parent'), ['inline_after_parent'], true)
                    ? $fu['display_mode'] : 'inline_after_parent';

                $this->fuRuleByChildFq[$fqId] = [
                    'parent_fq_id'       => $parentFq,
                    'trigger_option_ids' => $triggers,
                    'required_mode'      => $reqMode,
                    'display_mode'       => $dispMode,
                ];

                if ($parentFq > 0) {
                    $this->childrenByParentFq[$parentFq] = $this->childrenByParentFq[$parentFq] ?? [];
                    $this->childrenByParentFq[$parentFq][] = $fqId;
                }
            }
        }
    }

    /* ---------- visibility helpers (DB path) ---------- */

    /** Return true if question is visible considering follow-up rules */
    protected function isQuestionVisibleByQ(array $q): bool
    {
        // If no form_question_id or no follow-up for this child -> visible
        $childFq = (int) ($q['form_question_id'] ?? 0);
        if ($childFq <= 0 || !isset($this->fuRuleByChildFq[$childFq])) {
            return true;
        }

        $rule = $this->fuRuleByChildFq[$childFq];
        $parentFq = (int) ($rule['parent_fq_id'] ?? 0);
        $triggers = (array) ($rule['trigger_option_ids'] ?? []);

        $parentQid = $this->qidByFqId[$parentFq] ?? null;
        if (!$parentQid) {
            // If mapping is missing, fail-open (visible) to avoid blocking
            return true;
        }

        $selected = $this->selectedOptionIdsForQid($parentQid);
        if (empty($selected)) return false;

        // Intersection with trigger ids
        foreach ($selected as $sid) {
            if (in_array((int)$sid, $triggers, true)) {
                return true;
            }
        }
        return false;
    }

    /** Selected option ids (array) for a question_id, for single/multi/boolean */
    protected function selectedOptionIdsForQid(int $qid): array
    {
        $type = $this->typeByQid[$qid] ?? null;
        $ans  = $this->answers[$qid] ?? null;
        if (!is_array($ans)) return [];

        if ($type === 'single_choice' || $type === 'boolean') {
            $v = $ans['answer_option_id'] ?? null;
            return $v === null || $v === '' ? [] : [(int)$v];
        }
        if ($type === 'multi_choice') {
            $arr = $ans['answer_option_ids'] ?? [];
            return array_values(array_map('intval', (array)$arr));
        }

        // scale/text/custom are not used as parent in our rules
        return [];
    }

    /** Clear the stored answer for a question_id */
    protected function clearAnswerForQid(int $qid): void
    {
        if (!array_key_exists($qid, $this->answers)) return;
        $type = $this->typeByQid[$qid] ?? null;
        switch ($type) {
            case 'single_choice':
            case 'boolean':
                $this->answers[$qid]['answer_option_id'] = null;
                break;
            case 'multi_choice':
                $this->answers[$qid]['answer_option_ids'] = [];
                break;
            case 'scale':
                $this->answers[$qid]['answer_value'] = null;
                break;
            case 'text':
                $this->answers[$qid]['answer_text'] = '';
                break;
            default:
                if (array_key_exists('answer_json', $this->answers[$qid])) {
                    $this->answers[$qid]['answer_json'] = null;
                }
                break;
        }
    }

    /** After a parent changes, hide any non-matching descendants and clear their answers */
    protected function normalizeVisibilityFromParentQid(int $parentQid): void
    {
        $parentFq = $this->fqIdByQid[$parentQid] ?? null;
        if (!$parentFq) return;

        $queue = (array) ($this->childrenByParentFq[$parentFq] ?? []);
        while (!empty($queue)) {
            $childFq = array_shift($queue);
            $childQid = $this->qidByFqId[$childFq] ?? null;
            if (!$childQid) continue;

            $q = $this->dbQuestionsById[$childQid] ?? null;
            if (!$q) continue;

            $visible = $this->isQuestionVisibleByQ($q);
            if (!$visible) {
                $this->clearAnswerForQid($childQid);
            }

            // Recurse: a child can be a parent of further descendants
            if (!empty($this->childrenByParentFq[$childFq])) {
                foreach ($this->childrenByParentFq[$childFq] as $gchildFq) {
                    $gqid = $this->qidByFqId[$gchildFq] ?? null;
                    if ($gqid && !$visible) {
                        $this->clearAnswerForQid($gqid);
                    }
                    $queue[] = $gchildFq;
                }
            }
        }
    }

    /** Normalize visibility across all questions (used at mount and occasional safety) */
    protected function normalizeVisibilityForAll(): void
    {
        foreach (($this->dbFormDto['sections'] ?? []) as $section) {
            foreach (($section['questions'] ?? []) as $q) {
                $qid = (int) ($q['id'] ?? 0);
                if ($qid <= 0) continue;
                if (!$this->isQuestionVisibleByQ($q)) {
                    $this->clearAnswerForQid($qid);
                }
            }
        }
    }

    /** Livewire hook: any change under answers[...] */
    public function updatedAnswers($value, $key): void
    {
        if (!$this->useDbQuestions) return;

        // Keys look like: "123.answer_option_id" or "123.answer_option_ids.0"
        $parts = explode('.', (string)$key);
        if (empty($parts)) return;

        $qid = (int) ($parts[0] ?? 0);
        if ($qid > 0) {
            $this->normalizeVisibilityFromParentQid($qid);
        }
    }

    /* ---------- derived props (catalog & wizard) ---------- */

    public function getCatalogProperty(): array
    {
        return DogEvalCatalog::catalog();
    }

    public function getQuestionKeysProperty(): array
    {
        return collect($this->catalog)->flatMap(fn ($group) => array_keys($group))->values()->all();
    }

    public function getParamMapProperty(): array
    {
        if ($this->useDbQuestions) return [];
        $rows = \App\Models\EvaluationOptionParam::whereIn('question_key', $this->questionKeys)->get();

        $map = [];
        foreach ($rows as $p) {
            $red = is_array($p->red_flags) ? $p->red_flags : (json_decode($p->red_flags ?? '[]', true) ?: []);
            $map[$p->question_key.'|'.$p->option_key] = [
                'weight'     => (int) ($p->weight ?? 1),
                'red_flags'  => array_values(array_filter($red)),
            ];
        }
        return $map;
    }

    /** Step count */
    public function getStepsCountProperty(): int
    {
        if ($this->useDbQuestions) {
            return count($this->dbFormDto['sections'] ?? []);
        }
        return count($this->catalog);
    }

    /** Ordered section titles */
    public function getCategoryKeysProperty(): array
    {
        if ($this->useDbQuestions) {
            return collect($this->dbFormDto['sections'] ?? [])->pluck('title')->all();
        }
        return array_keys($this->catalog);
    }

    public function getCurrentCategoryKeyProperty(): ?string
    {
        return $this->categoryKeys[$this->step - 1] ?? null;
    }

    /** IMPORTANT: in DB mode, filter out hidden follow-up questions here */
    public function getCurrentQuestionsProperty(): array
    {
        if ($this->useDbQuestions) {
            $sec = ($this->dbFormDto['sections'][$this->step - 1] ?? null);
            $qs  = $sec['questions'] ?? [];
            $out = [];
            foreach ($qs as $q) {
                if ($this->isQuestionVisibleByQ($q)) {
                    $out[] = $q;
                }
            }
            return $out;
        }
        return $this->catalog[$this->currentCategoryKey] ?? [];
    }

    /* ---------- progress (shared UI) ---------- */

    public function getTotalQuestionsProperty(): int
    {
        if ($this->useDbQuestions) {
            // Count only currently visible questions
            $count = 0;
            foreach (($this->dbFormDto['sections'] ?? []) as $s) {
                foreach (($s['questions'] ?? []) as $q) {
                    if ($this->isQuestionVisibleByQ($q)) $count++;
                }
            }
            return $count;
        }
        return collect($this->catalog)->sum(fn ($group) => count($group));
    }

    public function getAnsweredQuestionsProperty(): int
    {
        if ($this->useDbQuestions) {
            $answered = 0;
            foreach (($this->dbFormDto['sections'] ?? []) as $s) {
                foreach (($s['questions'] ?? []) as $q) {
                    if (!$this->isQuestionVisibleByQ($q)) continue;

                    $qid = (int) $q['id'];
                    $t   = $q['type'] ?? null;
                    $ans = $this->answers[$qid] ?? null;

                    if ($t === 'single_choice' || $t === 'boolean') {
                        if (is_array($ans) && ($ans['answer_option_id'] ?? null)) $answered++;
                    } elseif ($t === 'multi_choice') {
                        if (is_array($ans) && !empty($ans['answer_option_ids'])) $answered++;
                    } elseif ($t === 'scale') {
                        if (is_array($ans) && is_numeric($ans['answer_value'])) $answered++;
                    } elseif ($t === 'text') {
                        if (is_array($ans) && is_string($ans['answer_text']) && $ans['answer_text'] !== '') $answered++;
                    } else {
                        if (is_array($ans) && array_key_exists('answer_json', $ans) && $ans['answer_json'] !== null) $answered++;
                    }
                }
            }
            return $answered;
        }

        // Legacy
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
        if ($this->useDbQuestions) {
            $rules = [];
            foreach (($this->dbFormDto['sections'] ?? []) as $s) {
                foreach (($s['questions'] ?? []) as $q) {
                    if (!$this->isQuestionVisibleByQ($q)) continue;
                    if (!($q['required'] ?? false) || ($q['visibility'] ?? 'always') !== 'always') continue;

                    $qid = (int) $q['id'];
                    switch ($q['type']) {
                        case 'single_choice':
                        case 'boolean':
                            $validIds = collect($q['options'] ?? [])->pluck('id')->implode(',');
                            $rules["answers.$qid.answer_option_id"] = ['required', $validIds !== '' ? "in:$validIds" : 'integer'];
                            break;
                        case 'multi_choice':
                            $validIds = collect($q['options'] ?? [])->pluck('id')->implode(',');
                            $rules["answers.$qid.answer_option_ids"] = ['required','array'];
                            if ($validIds !== '') {
                                $rules["answers.$qid.answer_option_ids.*"] = ["in:$validIds"];
                            }
                            break;
                        case 'scale':
                            $rules["answers.$qid.answer_value"] = ['required','numeric'];
                            break;
                        case 'text':
                            $rules["answers.$qid.answer_text"] = ['required','string'];
                            break;
                        default:
                            $rules["answers.$qid.answer_json"] = ['nullable'];
                            break;
                    }
                }
            }
            return $rules;
        }

        // Legacy rules unchanged
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

        if ($this->useDbQuestions) {
            $sec = $this->dbFormDto['sections'][$step - 1] ?? null;
            if (!$sec) return [];

            $rules = [];
            foreach (($sec['questions'] ?? []) as $q) {
                if (!$this->isQuestionVisibleByQ($q)) continue;
                if (!($q['required'] ?? false) || ($q['visibility'] ?? 'always') !== 'always') continue;

                $qid = (int) $q['id'];
                switch ($q['type']) {
                    case 'single_choice':
                    case 'boolean':
                        $validIds = collect($q['options'] ?? [])->pluck('id')->implode(',');
                        $rules["answers.$qid.answer_option_id"] = ['required', $validIds !== '' ? "in:$validIds" : 'integer'];
                        break;
                    case 'multi_choice':
                        $validIds = collect($q['options'] ?? [])->pluck('id')->implode(',');
                        $rules["answers.$qid.answer_option_ids"] = ['required','array'];
                        if ($validIds !== '') {
                            $rules["answers.$qid.answer_option_ids.*"] = ["in:$validIds"];
                        }
                        break;
                    case 'scale':
                        $rules["answers.$qid.answer_value"] = ['required','numeric'];
                        break;
                    case 'text':
                        $rules["answers.$qid.answer_text"] = ['required','string'];
                        break;
                }
            }
            return $rules;
        }

        // Legacy step rules unchanged
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
        $upto = max(1, min($upto, $this->stepsCount));

        if ($this->useDbQuestions) {
            $rules = [];
            for ($i = 1; $i <= $upto; $i++) {
                $sec = $this->dbFormDto['sections'][$i - 1] ?? null;
                if (!$sec) continue;

                foreach (($sec['questions'] ?? []) as $q) {
                    if (!$this->isQuestionVisibleByQ($q)) continue;
                    if (!($q['required'] ?? false) || ($q['visibility'] ?? 'always') !== 'always') continue;

                    $qid = (int) $q['id'];
                    switch ($q['type']) {
                        case 'single_choice':
                        case 'boolean':
                            $validIds = collect($q['options'] ?? [])->pluck('id')->implode(',');
                            $rules["answers.$qid.answer_option_id"] = ['required', $validIds !== '' ? "in:$validIds" : 'integer'];
                            break;
                        case 'multi_choice':
                            $validIds = collect($q['options'] ?? [])->pluck('id')->implode(',');
                            $rules["answers.$qid.answer_option_ids"] = ['required','array'];
                            if ($validIds !== '') {
                                $rules["answers.$qid.answer_option_ids.*"] = ["in:$validIds"];
                            }
                            break;
                        case 'scale':
                            $rules["answers.$qid.answer_value"] = ['required','numeric'];
                            break;
                        case 'text':
                            $rules["answers.$qid.answer_text"] = ['required','string'];
                            break;
                    }
                }
            }
            return $rules;
        }

        // Legacy up-to rules unchanged
        $rules = [];
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

    public function submit(PersistEvaluationResponses $persist): void
    {
        $this->validate($this->rules());

        if ($this->useDbQuestions) {
            /** @var DbEvaluationForm $form */
            $formId = (int) ($this->dbFormDto['form']['id'] ?? 0);
            $form   = DbEvaluationForm::findOrFail($formId);

            $initialAnswers = $this->answers ?? [];

            $evaluation = Evaluation::create([
                'dog_id'  => $this->dog->id,
                'user_id' => Auth::id(),
                'answers' => $initialAnswers,
            ]);

            $payload = $this->answers;

            $persist->handle($evaluation, $form, $payload);

            session()->flash('success', "Evaluation saved.");
            $this->redirectRoute('dogs.show', $this->dog);
            return;
        }

        // LEGACY PATH unchanged...
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

        $redFlags = [];
        foreach ($selectedByQ as $qKey => $optKeys) {
            foreach ($optKeys as $optKey) {
                $p = $this->paramMap[$qKey.'|'.$optKey] ?? null;
                if (!$p) continue;
                foreach ((array) ($p['red_flags'] ?? []) as $flag) {
                    if ($flag !== null && $flag !== '') $redFlags[] = $flag;
                }
            }
        }
        $redFlags = array_values(array_unique($redFlags));

        Evaluation::create([
            'dog_id'          => $this->dog->id,
            'user_id'         => Auth::id(),
            'score'           => $globalPercent,
            'category_scores' => $categoryPercents,
            'answers'         => $this->answers,
            'red_flags'       => $redFlags,
        ]);

        session()->flash('success', "Evaluation saved (score {$this->liveScore} / {$this->maxScore})");
        $this->redirectRoute('dogs.show', $this->dog);
    }

    /* ---------- LEGACY scoring helpers (kept intact) ---------- */

    public function getCategoryRawScoresProperty(): array
    {
        if ($this->useDbQuestions) return [];
        $out = [];
        foreach ($this->catalog as $catName => $group) {
            $raw = 0;
            foreach ($group as $qKey => $q) {
                if (($q['type'] ?? null) !== 'radio') continue;
                $selected = $this->answers[$qKey] ?? null;
                if ($selected === null || $selected === '') continue;
                $optMeta = $q['options'][$selected] ?? null;
                $base = (is_array($optMeta) && array_key_exists('score', $optMeta) && is_int($optMeta['score'])) ? $optMeta['score'] : 0;
                if ($base <= 0) continue;
                $w = (int) ($this->paramMap[$qKey.'|'.$selected]['weight'] ?? 1);
                $w = max(1, $w);
                $raw += $base * $w;
            }
            $out[$catName] = $raw;
        }
        return $out;
    }

    public function getCategoryMaxScoresProperty(): array
    {
        if ($this->useDbQuestions) return [];
        $out = [];
        foreach ($this->catalog as $catName => $group) {
            $maxCat = 0;
            foreach ($group as $qKey => $q) {
                if (($q['type'] ?? null) !== 'radio') continue;
                $maxForQuestion = 0;
                foreach (($q['options'] ?? []) as $optKey => $optMeta) {
                    $base = (is_array($optMeta) && array_key_exists('score', $optMeta) && is_int($optMeta['score'])) ? $optMeta['score'] : 0;
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

    public function getCategoryPercentsProperty(): array
    {
        if ($this->useDbQuestions) return [];
        $raw = $this->categoryRawScores;
        $max = $this->categoryMaxScores;
        $toPercent = fn (int $r, int $m) => $m > 0 ? (int) round(($r / $m) * 100) : 0;
        $out = [];
        foreach ($this->catalog as $catName => $group) {
            $out[$catName] = $toPercent((int) ($raw[$catName] ?? 0), (int) ($max[$catName] ?? 0));
        }
        return $out;
    }

    public function getGlobalPercentProperty(): int
    {
        if ($this->useDbQuestions) return 0;
        return $this->maxScore > 0
            ? (int) round(($this->liveScore / $this->maxScore) * 100)
            : 0;
    }

    public function getMaxScoreProperty(): int
    {
        if ($this->useDbQuestions) return 0;
        $total = 0;
        foreach ($this->catalog as $group) {
            foreach ($group as $qKey => $q) {
                if (($q['type'] ?? null) !== 'radio') continue;
                $maxForQuestion = 0;
                foreach (($q['options'] ?? []) as $optKey => $optMeta) {
                    $base = (is_array($optMeta) && array_key_exists('score', $optMeta) && is_int($optMeta['score'])) ? $optMeta['score'] : 0;
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

    public function getLiveScoreProperty(): int
    {
        if ($this->useDbQuestions) return 0;
        $score = 0;
        $flat  = collect($this->catalog)->flatMap(fn ($group) => $group);
        foreach ($this->answers as $qKey => $optValue) {
            $question = $flat[$qKey] ?? null;
            if (!$question || ($question['type'] ?? null) !== 'radio') continue;
            $optMeta = $question['options'][$optValue] ?? null;
            $base = (is_array($optMeta) && array_key_exists('score', $optMeta) && is_int($optMeta['score'])) ? $optMeta['score'] : 0;
            if ($base <= 0) continue;
            $w = (int) ($this->paramMap[$qKey.'|'.$optValue]['weight'] ?? 1);
            $w = max(1, $w);
            $score += $base * $w;
        }
        return $score;
    }

    public function render()
    {
        return view('livewire.dogs.evaluation-form');
    }
}
