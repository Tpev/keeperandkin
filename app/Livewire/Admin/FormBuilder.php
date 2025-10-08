<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\EvaluationForm;
use App\Models\EvaluationSection;
use App\Models\EvaluationFormQuestion;
use App\Models\Question;
use App\Models\AnswerOption;
use App\Models\TrainingFlag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FormBuilder extends Component
{
    public EvaluationForm $form;

    // ----- Simple editor fields -----
    public string $formName = '';

    // Sections
    public string $newSectionTitle = '';
    public string $newSectionSlug  = '';
    public ?int $editingSectionId = null;
    public string $editingSectionTitle = '';

    // Question creation / attach
    public ?int $sectionForQuestion = null; // section_id
    public ?int $existingQuestionId = null;
    public bool $createQuestionModal = false;

    // New question fields
    public string $qPrompt = '';
    public ?string $qHelp = '';
    public string $qType = 'single_choice'; // single_choice|multi_choice|scale|boolean|text

    // Scale config
    public int $qScaleMin = 0;
    public int $qScaleMax = 10;
    public bool $qScaleInvert = false;

    /**
     * Options editor scaffold (for new question)
     * [
     *   [
     *     'label' => '',
     *     'value' => '',
     *     'flags' => [...],                     // red flags (legacy badges)
     *     'training_flag_ids' => [1,2,...],     // NEW: db training flags
     *     'scores' => ['comfort_confidence'=>0,'sociability'=>0,'trainability'=>0]
     *   ],
     *   ...
     * ]
     */
    public array $optionRows = [];

    // Red-flag list (static)
    public array $availableFlags = [
        'Muzzle Conditioning',
        'Leash Skills',
        'Body Handling',
        'Reactivity Trigger',
        'High Arousal',
        'Resource Guarding',
        'Separation Distress',
    ];

    // DB Training flags (loaded for UI)
    public $trainingFlags; // Collection<TrainingFlag>

    // Derived category (from section)
    public string $derivedCategoryKey = 'general'; // comfort_confidence|sociability|trainability|general
    public string $derivedCategoryLabel = 'General';

    public function mount(EvaluationForm $form): void
    {
        $this->form = $form->load([
            'sections' => fn ($q) => $q->orderBy('position'),
            'formQuestions' => fn ($q) => $q->orderBy('position')->with([
                'question',
                'section',
                'question.answerOptions' => fn ($r) => $r->orderBy('position')->with('trainingFlags'),
            ]),
        ]);

        if ($this->form->is_active) {
            session()->flash('error', 'Published forms are immutable. Clone as draft to edit.');
            redirect()->route('admin.forms.index')->send();
            return;
        }

        $this->trainingFlags = TrainingFlag::orderBy('name')->get();

        $this->formName = $this->form->name;
    }

    /* ---------- Form basics ---------- */
    public function saveFormMeta(): void
    {
        $this->validate([
            'formName' => ['required', 'string', 'max:255'],
        ]);

        $this->form->name = $this->formName;
        $this->form->save();

        session()->flash('success', 'Form updated.');
        $this->reload();
    }

    /* ---------- Sections ---------- */
    public function addSection(): void
    {
        $this->validate([
            'newSectionTitle' => ['required', 'string', 'max:255'],
        ]);

        if (blank($this->newSectionSlug)) {
            $this->newSectionSlug = Str::slug($this->newSectionTitle);
        }

        $pos = (int) ($this->form->sections()->max('position') ?? 0) + 1;

        EvaluationSection::create([
            'form_id'  => $this->form->id,
            'title'    => $this->newSectionTitle,
            'slug'     => $this->newSectionSlug,
            'position' => $pos,
        ]);

        $this->newSectionTitle = '';
        $this->newSectionSlug  = '';

        $this->reload();
    }

    public function editSection(int $sectionId): void
    {
        $sec = $this->form->sections->firstWhere('id', $sectionId);
        if (!$sec) return;

        $this->editingSectionId = $sec->id;
        $this->editingSectionTitle = $sec->title;
    }

    public function saveSection(): void
    {
        $sec = $this->form->sections->firstWhere('id', $this->editingSectionId);
        if (!$sec) return;

        $this->validate([
            'editingSectionTitle' => ['required', 'string', 'max:255'],
        ]);

        $sec->title = $this->editingSectionTitle;
        if (blank($sec->slug)) {
            $sec->slug = Str::slug($sec->title);
        }
        $sec->save();

        $this->editingSectionId = null;
               $this->editingSectionTitle = '';
        $this->reload();
    }

    public function deleteSection(int $sectionId): void
    {
        $sec = $this->form->sections->firstWhere('id', $sectionId);
        if (!$sec) return;

        EvaluationFormQuestion::where('form_id', $this->form->id)
            ->where('section_id', $sec->id)->delete();

        $sec->delete();
        $this->reload();
    }

    public function sectionUp(int $sectionId): void
    {
        $sec = $this->form->sections->firstWhere('id', $sectionId);
        if (!$sec) return;

        $prev = $this->form->sections()
            ->where('position', '<', $sec->position)
            ->orderBy('position', 'desc')
            ->first();

        if ($prev) {
            [$sec->position, $prev->position] = [$prev->position, $sec->position];
            $sec->save(); $prev->save();
            $this->reload();
        }
    }

    public function sectionDown(int $sectionId): void
    {
        $sec = $this->form->sections->firstWhere('id', $sectionId);
        if (!$sec) return;

        $next = $this->form->sections()
            ->where('position', '>', $sec->position)
            ->orderBy('position')
            ->first();

        if ($next) {
            [$sec->position, $next->position] = [$next->position, $sec->position];
            $sec->save(); $next->save();
            $this->reload();
        }
    }

    /* ---------- Questions ---------- */

    // Attach from bank — also force category to section and normalize score maps
    public function attachExistingQuestion(int $sectionId): void
    {
        $this->validate([
            'existingQuestionId' => ['required', 'integer', 'exists:questions,id'],
        ]);

        $sec = EvaluationSection::findOrFail($sectionId);
        $derived = $this->inferCategoryFromSection($sec);

        $pos = (int) EvaluationFormQuestion::where('form_id', $this->form->id)
                ->where('section_id', $sectionId)
                ->max('position');
        $pos = $pos + 1;

        DB::transaction(function () use ($sectionId, $pos, $derived) {
            $q = Question::with('answerOptions')->findOrFail($this->existingQuestionId);

            if ($q->category !== $derived) {
                $q->category = $derived;
                $q->save();

                foreach ($q->answerOptions as $opt) {
                    $this->normalizeOptionScoreMap($opt, $derived);
                }
            }

            EvaluationFormQuestion::firstOrCreate([
                'form_id'     => $this->form->id,
                'section_id'  => $sectionId,
                'question_id' => $q->id,
            ], [
                'position'   => $pos,
                'required'   => true,
                'visibility' => 'always',
                'meta'       => null,
            ]);
        });

        $this->existingQuestionId = null;
        $this->reload();
    }

    public function openCreateQuestion(int $sectionId): void
    {
        $this->sectionForQuestion = $sectionId;

        $sec = $this->form->sections->firstWhere('id', $sectionId);
        $cat = $this->inferCategoryFromSection($sec);
        $this->derivedCategoryKey = $cat;
        $this->derivedCategoryLabel = $this->labelForCategory($cat);

        $this->qPrompt = '';
        $this->qHelp = '';
        $this->qType = 'single_choice';

        $this->qScaleMin = 0;
        $this->qScaleMax = 10;
        $this->qScaleInvert = false;

        $this->optionRows = [
            [
                'label'            => '',
                'value'            => '',
                'flags'            => [],
                'training_flag_ids'=> [],
                'scores'           => [
                    'comfort_confidence' => 0,
                    'sociability'        => 0,
                    'trainability'       => 0,
                ],
            ],
        ];

        $this->createQuestionModal = true;
    }

    public function addOptionRow(): void
    {
        $this->optionRows[] = [
            'label'            => '',
            'value'            => '',
            'flags'            => [],
            'training_flag_ids'=> [],
            'scores'           => [
                'comfort_confidence' => 0,
                'sociability'        => 0,
                'trainability'       => 0,
            ],
        ];
    }

    public function removeOptionRow(int $idx): void
    {
        if (!array_key_exists($idx, $this->optionRows)) return;
        unset($this->optionRows[$idx]);
        $this->optionRows = array_values($this->optionRows);
    }

    public function saveNewQuestion(): void
    {
        $this->validate([
            'sectionForQuestion' => ['required', 'integer', 'exists:evaluation_sections,id'],
            'qPrompt'            => ['required', 'string'],
            'qType'              => ['required', 'in:single_choice,multi_choice,scale,boolean,text'],
        ]);

        if ($this->qType === 'scale') {
            $this->validate([
                'qScaleMin' => ['required', 'integer'],
                'qScaleMax' => ['required', 'integer', 'gte:qScaleMin'],
            ]);
        }

        DB::transaction(function () {
            $sec = EvaluationSection::findOrFail($this->sectionForQuestion);
            $cat = $this->inferCategoryFromSection($sec);

            // Auto slug for question
            $base = Str::slug(Str::limit($this->qPrompt, 80, ''));
            if ($base === '') $base = 'question';
            $slug = $this->uniqueSlug('questions', 'slug', $base);

            $meta = null;
            if ($this->qType === 'scale') {
                $meta = [
                    'min'    => $this->qScaleMin,
                    'max'    => $this->qScaleMax,
                    'invert' => $this->qScaleInvert,
                ];
            }

            $q = Question::create([
                'slug'      => $slug,
                'prompt'    => $this->qPrompt,
                'help_text' => $this->qHelp ?: null,
                'type'      => $this->qType,
                'category'  => $cat,
                'meta'      => $meta,
            ]);

            // Options for discrete types
            if (in_array($this->qType, ['single_choice', 'multi_choice', 'boolean'], true)) {
                $pos = 0;
                foreach ($this->optionRows as $row) {
                    $label = trim($row['label'] ?? '');
                    if ($label === '') continue;
                    $pos++;

                    $flags  = array_values(array_filter($row['flags'] ?? []));
                    $scoreVal = (int) data_get($row, "scores.$cat", 0);

                    // score_map only on derived category
                    $map = ['comfort_confidence'=>0,'sociability'=>0,'trainability'=>0];
                    $map[$cat] = $scoreVal;

                    $opt = AnswerOption::create([
                        'question_id' => $q->id,
                        'label'       => $label,
                        'value'       => ($row['value'] ?? null) ?: null,
                        'position'    => $pos,
                        'score_map'   => $map,
                        'flags'       => $flags,
                    ]);

                    // Attach training flags (validated)
                    $tfIds = array_map('intval', (array)($row['training_flag_ids'] ?? []));
                    if (!empty($tfIds)) {
                        $validTfIds = TrainingFlag::whereIn('id', $tfIds)->pluck('id')->all();
                        if (!empty($validTfIds)) {
                            $opt->trainingFlags()->sync($validTfIds);
                        }
                    }
                }
            }

            // Attach to this section
            $position = (int) EvaluationFormQuestion::where('form_id', $this->form->id)
                ->where('section_id', $this->sectionForQuestion)
                ->max('position');
            $position++;

            EvaluationFormQuestion::create([
                'form_id'     => $this->form->id,
                'section_id'  => $this->sectionForQuestion,
                'question_id' => $q->id,
                'position'    => $position,
                'required'    => true,
                'visibility'  => 'always',
                'meta'        => null,
            ]);

            $this->createQuestionModal = false;
            session()->flash('success', 'Question created and attached.');
            $this->reload();
        });
    }

    public function qUp(int $fqId): void
    {
        $fq = EvaluationFormQuestion::with('section')->findOrFail($fqId);
        $prev = EvaluationFormQuestion::where('form_id', $this->form->id)
            ->where('section_id', $fq->section_id)
            ->where('position', '<', $fq->position)
            ->orderBy('position', 'desc')
            ->first();

        if ($prev) {
            [$fq->position, $prev->position] = [$prev->position, $fq->position];
            $fq->save(); $prev->save();
            $this->reload();
        }
    }

    public function qDown(int $fqId): void
    {
        $fq = EvaluationFormQuestion::with('section')->findOrFail($fqId);
        $next = EvaluationFormQuestion::where('form_id', $this->form->id)
            ->where('section_id', $fq->section_id)
            ->where('position', '>', $fq->position)
            ->orderBy('position')
            ->first();

        if ($next) {
            [$fq->position, $next->position] = [$next->position, $fq->position];
            $fq->save(); $next->save();
            $this->reload();
        }
    }

    public function toggleRequired(int $fqId): void
    {
        $fq = EvaluationFormQuestion::findOrFail($fqId);
        $fq->required = !$fq->required;
        $fq->save();
        $this->reload();
    }

    public function setVisibility(int $fqId, string $visibility): void
    {
        if (!in_array($visibility, ['always', 'staff_only', 'public_summary'], true)) return;

        $fq = EvaluationFormQuestion::findOrFail($fqId);
        $fq->visibility = $visibility;
        $fq->save();
        $this->reload();
    }

    public function detachQuestion(int $fqId): void
    {
        EvaluationFormQuestion::where('id', $fqId)->delete();
        $this->reload();
    }

    /* ---------- Option editors for existing questions ---------- */

    public function addOption(int $questionId): void
    {
        $q = Question::findOrFail($questionId);
        $pos = (int) $q->answerOptions()->max('position') + 1;

        $map = ['comfort_confidence'=>0,'sociability'=>0,'trainability'=>0];

        AnswerOption::create([
            'question_id' => $q->id,
            'label'       => 'New option',
            'value'       => null,
            'position'    => $pos,
            'score_map'   => $map,
            'flags'       => [],
        ]);

        $this->reload();
    }

    public function updateOptionField(int $optionId, string $field, $value): void
    {
        $opt = AnswerOption::findOrFail($optionId);
        if (!in_array($field, ['label', 'value'], true)) return;

        $opt->{$field} = $value ?: null;
        $opt->save();
    }

    // Red flags (legacy badge list)
    public function replaceOptionFlags(int $optionId, array $flags): void
    {
        $opt = AnswerOption::findOrFail($optionId);
        $clean = array_values(array_intersect($flags, $this->availableFlags));
        $opt->flags = $clean;
        $opt->save();
    }

    // NEW: DB Training flags for this option
    public function replaceOptionTrainingFlags(int $optionId, array $flagIds): void
    {
        $opt = AnswerOption::findOrFail($optionId);
        $ids = array_values(array_unique(array_map('intval', $flagIds)));
        $valid = TrainingFlag::whereIn('id', $ids)->pluck('id')->all();
        $opt->trainingFlags()->sync($valid);
        // no heavy reload; but ensure relation is fresh for UI that re-renders
        $this->reload();
    }

    public function updateOptionScore(int $optionId, string $domain, $value): void
    {
        $allowed = ['comfort_confidence', 'sociability', 'trainability', 'general'];
        if (!in_array($domain, $allowed, true)) return;
        if ($domain === 'general') return;

        $opt = AnswerOption::findOrFail($optionId);
        // Zero other categories to avoid leakage
        $scores = ['comfort_confidence'=>0,'sociability'=>0,'trainability'=>0];
        $scores[$domain] = (int) $value;

        $opt->score_map = $scores;
        $opt->save();
    }

    public function deleteOption(int $optionId): void
    {
        AnswerOption::where('id', $optionId)->delete();
        $this->reload();
    }

    /* ---------- helpers ---------- */

    private function reload(): void
    {
        $this->form->refresh()->load([
            'sections' => fn ($q) => $q->orderBy('position'),
            'formQuestions' => fn ($q) => $q->orderBy('position')->with([
                'question',
                'section',
                'question.answerOptions' => fn ($r) => $r->orderBy('position')->with('trainingFlags'),
            ]),
        ]);

        // Keep training flag list fresh in case admin added/renamed in another tab
        $this->trainingFlags = TrainingFlag::orderBy('name')->get();
    }

    private function uniqueSlug(string $table, string $column, string $base): string
    {
        $slug = $base;
        $i = 2;
        while (DB::table($table)->where($column, $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }
        return $slug;
    }

    private function labelForCategory(string $key): string
    {
        return [
            'comfort_confidence' => 'Comfort & Confidence',
            'sociability'        => 'Sociability',
            'trainability'       => 'Trainability',
            'general'            => 'General',
        ][$key] ?? 'General';
    }

    public static function inferCategoryStatic(?string $slug): string
    {
        $s = Str::of($slug ?? '')->lower()->toString();
        $map = [
            'comfort-confidence' => 'comfort_confidence',
            'comfort_confidence' => 'comfort_confidence',
            'comfort'            => 'comfort_confidence',
            'confidence'         => 'comfort_confidence',
            'sociability'        => 'sociability',
            'social'             => 'sociability',
            'trainability'       => 'trainability',
            'training'           => 'trainability',
        ];
        if (isset($map[$s])) return $map[$s];

        if (str_contains($s, 'confid')) return 'comfort_confidence';
        if (str_contains($s, 'soci'))   return 'sociability';
        if (str_contains($s, 'train'))  return 'trainability';

        return 'general';
    }

    private function inferCategoryFromSection(EvaluationSection $sec): string
    {
        $slug = $sec->slug ?: Str::slug($sec->title);
        return self::inferCategoryStatic($slug);
    }

    private function normalizeOptionScoreMap(AnswerOption $opt, string $cat): void
    {
        $current = (array) ($opt->score_map ?? []);
        $val = (int) ($current[$cat] ?? 0);
        $opt->score_map = [
            'comfort_confidence' => $cat==='comfort_confidence' ? $val : 0,
            'sociability'        => $cat==='sociability'        ? $val : 0,
            'trainability'       => $cat==='trainability'       ? $val : 0,
        ];
        $opt->save();
    }

    public function render()
    {
        $bank = Question::orderBy('created_at', 'desc')->take(100)->get();

        return view('livewire.admin.form-builder', [
            'bank'           => $bank,
            'availableFlags' => $this->availableFlags,
            'trainingFlags'  => $this->trainingFlags,
        ])->layout('layouts.app');
    }
}
