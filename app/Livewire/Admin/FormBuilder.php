<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\EvaluationForm;
use App\Models\EvaluationSection;
use App\Models\EvaluationFormQuestion;
use App\Models\Question;
use App\Models\AnswerOption;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class FormBuilder extends Component
{
    public EvaluationForm $form;

    // editing fields
    public $formName = '';

    // section create/edit
    public $newSectionTitle = '';
    public $newSectionSlug  = '';
    public $editingSectionId = null;
    public $editingSectionTitle = '';

    // question add/create
    public $sectionForQuestion = null;   // section_id
    public $existingQuestionId = null;   // pick from bank
    public $createQuestionModal = false;

    // new question fields
    public $qSlug = '';
    public $qPrompt = '';
    public $qHelp = '';
    public $qType = 'single_choice'; // single_choice|multi_choice|scale|boolean|text
    public $qCategory = 'general';   // comfort_confidence|sociability|trainability|general
    public $qMeta = '';              // JSON

    // add options inline for new/existing question
    public $optionRows = []; // [['label'=>'','value'=>'','score_map'=>'{}','flags'=>'[]'], ...]

    public function mount(EvaluationForm $form): void
    {
        $this->form = $form->load(['sections' => fn($q)=>$q->orderBy('position'),
                                   'formQuestions' => fn($q)=>$q->orderBy('position')->with(['question','section','question.answerOptions' => fn($r)=>$r->orderBy('position')])]);

        if ($this->form->is_active) {
            session()->flash('error', 'Published forms are immutable. Clone as draft to edit.');
            redirect()->route('admin.forms.index')->send();
            return;
        }

        $this->formName = $this->form->name;
    }

    /* ---------- Form basics ---------- */
    public function saveFormMeta()
    {
        $this->validate([
            'formName' => ['required','string','max:255'],
        ]);

        $this->form->name = $this->formName;
        $this->form->save();

        session()->flash('success', 'Form updated.');
        $this->reload();
    }

    /* ---------- Sections ---------- */
    public function addSection()
    {
        $this->validate([
            'newSectionTitle' => ['required','string','max:255'],
        ]);
        if (!$this->newSectionSlug) $this->newSectionSlug = \Str::slug($this->newSectionTitle);

        $pos = ($this->form->sections()->max('position') ?? 0) + 1;

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

    public function editSection(int $sectionId)
    {
        $sec = $this->form->sections->firstWhere('id', $sectionId);
        if (!$sec) return;
        $this->editingSectionId = $sec->id;
        $this->editingSectionTitle = $sec->title;
    }

    public function saveSection()
    {
        $sec = $this->form->sections->firstWhere('id', $this->editingSectionId);
        if (!$sec) return;
        $this->validate([
            'editingSectionTitle' => ['required','string','max:255'],
        ]);
        $sec->title = $this->editingSectionTitle;
        $sec->save();

        $this->editingSectionId = null;
        $this->editingSectionTitle = '';
        $this->reload();
    }

    public function deleteSection(int $sectionId)
    {
        $sec = $this->form->sections->firstWhere('id', $sectionId);
        if (!$sec) return;

        // Move any formQuestions out (or delete them). We'll delete associations only.
        EvaluationFormQuestion::where('form_id', $this->form->id)->where('section_id', $sec->id)->delete();
        $sec->delete();
        $this->reload();
    }

    public function sectionUp(int $sectionId)
    {
        $sec = $this->form->sections->firstWhere('id', $sectionId);
        if (!$sec) return;

        $prev = $this->form->sections()->where('position','<',$sec->position)->orderBy('position','desc')->first();
        if ($prev) {
            $p = $sec->position;
            $sec->position = $prev->position;
            $prev->position = $p;
            $sec->save(); $prev->save();
            $this->reload();
        }
    }
    public function sectionDown(int $sectionId)
    {
        $sec = $this->form->sections->firstWhere('id', $sectionId);
        if (!$sec) return;

        $next = $this->form->sections()->where('position','>',$sec->position)->orderBy('position')->first();
        if ($next) {
            $p = $sec->position;
            $sec->position = $next->position;
            $next->position = $p;
            $sec->save(); $next->save();
            $this->reload();
        }
    }

    /* ---------- Questions ---------- */
    public function attachExistingQuestion()
    {
        $this->validate([
            'sectionForQuestion' => ['required','integer','exists:evaluation_sections,id'],
            'existingQuestionId' => ['required','integer','exists:questions,id'],
        ]);

        $pos = (int) EvaluationFormQuestion::where('form_id', $this->form->id)->where('section_id',$this->sectionForQuestion)->max('position');
        $pos = $pos + 1;

        EvaluationFormQuestion::firstOrCreate([
            'form_id'     => $this->form->id,
            'section_id'  => $this->sectionForQuestion,
            'question_id' => $this->existingQuestionId,
        ], [
            'position'   => $pos,
            'required'   => true,
            'visibility' => 'always',
            'meta'       => null,
        ]);

        $this->existingQuestionId = null;
        $this->reload();
    }

    public function openCreateQuestion(int $sectionId)
    {
        $this->sectionForQuestion = $sectionId;
        $this->qSlug = '';
        $this->qPrompt = '';
        $this->qHelp = '';
        $this->qType = 'single_choice';
        $this->qCategory = 'general';
        $this->qMeta = '';
        $this->optionRows = [
            ['label'=>'','value'=>'','score_map'=>'{}','flags'=>'[]'],
        ];
        $this->createQuestionModal = true;
    }

    public function addOptionRow()
    {
        $this->optionRows[] = ['label'=>'','value'=>'','score_map'=>'{}','flags'=>'[]'];
    }
    public function removeOptionRow($idx)
    {
        unset($this->optionRows[$idx]);
        $this->optionRows = array_values($this->optionRows);
    }

    public function saveNewQuestion()
    {
        $this->validate([
            'sectionForQuestion' => ['required','integer','exists:evaluation_sections,id'],
            'qSlug'   => ['required','string','max:255', Rule::unique('questions','slug')],
            'qPrompt' => ['required','string'],
            'qType'   => ['required','in:single_choice,multi_choice,scale,boolean,text'],
            'qCategory' => ['required','in:comfort_confidence,sociability,trainability,general'],
        ]);

        DB::transaction(function () {
            $meta = $this->qMeta ? json_decode($this->qMeta, true) : null;

            $q = Question::create([
                'slug'      => $this->qSlug,
                'prompt'    => $this->qPrompt,
                'help_text' => $this->qHelp ?: null,
                'type'      => $this->qType,
                'category'  => $this->qCategory,
                'meta'      => $meta,
            ]);

            // Options (if needed)
            $pos = 0;
            foreach ($this->optionRows as $row) {
                $label = trim($row['label'] ?? '');
                if ($label === '') continue;
                $pos++;
                $value = $row['value'] ?? null;
                $scoreMap = $row['score_map'] ? json_decode($row['score_map'], true) : null;
                $flags    = $row['flags'] ? json_decode($row['flags'], true) : null;

                AnswerOption::create([
                    'question_id' => $q->id,
                    'label'       => $label,
                    'value'       => $value ?: null,
                    'position'    => $pos,
                    'score_map'   => $scoreMap ?: null,
                    'flags'       => $flags ?: null,
                ]);
            }

            // Attach into form section
            $position = (int) EvaluationFormQuestion::where('form_id', $this->form->id)
                        ->where('section_id',$this->sectionForQuestion)->max('position');
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

    public function qUp(int $fqId)
    {
        $fq = EvaluationFormQuestion::with('section')->findOrFail($fqId);
        $prev = EvaluationFormQuestion::where('form_id',$this->form->id)
                ->where('section_id',$fq->section_id)
                ->where('position','<',$fq->position)
                ->orderBy('position','desc')->first();
        if ($prev) {
            $p = $fq->position;
            $fq->position = $prev->position;
            $prev->position = $p;
            $fq->save(); $prev->save();
            $this->reload();
        }
    }
    public function qDown(int $fqId)
    {
        $fq = EvaluationFormQuestion::with('section')->findOrFail($fqId);
        $next = EvaluationFormQuestion::where('form_id',$this->form->id)
                ->where('section_id',$fq->section_id)
                ->where('position','>',$fq->position)
                ->orderBy('position')->first();
        if ($next) {
            $p = $fq->position;
            $fq->position = $next->position;
            $next->position = $p;
            $fq->save(); $next->save();
            $this->reload();
        }
    }

    public function toggleRequired(int $fqId)
    {
        $fq = EvaluationFormQuestion::findOrFail($fqId);
        $fq->required = !$fq->required;
        $fq->save();
        $this->reload();
    }

    public function setVisibility(int $fqId, string $visibility)
    {
        if (!in_array($visibility, ['always','staff_only','public_summary'], true)) return;
        $fq = EvaluationFormQuestion::findOrFail($fqId);
        $fq->visibility = $visibility;
        $fq->save();
        $this->reload();
    }

    public function detachQuestion(int $fqId)
    {
        EvaluationFormQuestion::where('id',$fqId)->delete();
        $this->reload();
    }

    /* ---------- Options on existing Question ---------- */
    public function addOption(int $questionId)
    {
        $q = Question::findOrFail($questionId);
        $pos = (int) $q->answerOptions()->max('position') + 1;

        AnswerOption::create([
            'question_id' => $q->id,
            'label'       => 'New option',
            'value'       => null,
            'position'    => $pos,
            'score_map'   => ['comfort_confidence'=>0,'sociability'=>0,'trainability'=>0],
            'flags'       => [],
        ]);
        $this->reload();
    }

    public function saveOption(int $optionId, string $field, $value)
    {
        $opt = AnswerOption::findOrFail($optionId);
        if ($field === 'label' || $field === 'value') {
            $opt->{$field} = $value;
        } elseif ($field === 'score_map') {
            $opt->score_map = $value ? json_decode($value, true) : null;
        } elseif ($field === 'flags') {
            $opt->flags = $value ? json_decode($value, true) : null;
        }
        $opt->save();
        // no reload needed for inline edits
    }

    public function deleteOption(int $optionId)
    {
        AnswerOption::where('id',$optionId)->delete();
        $this->reload();
    }

    /* ---------- helpers ---------- */
    private function reload(): void
    {
        $this->form->refresh()->load([
            'sections' => fn($q)=>$q->orderBy('position'),
            'formQuestions' => fn($q)=>$q->orderBy('position')->with(['question','section','question.answerOptions' => fn($r)=>$r->orderBy('position')])
        ]);
    }

    public function render()
    {
        // question bank to attach
        $bank = Question::orderBy('created_at','desc')->take(100)->get();

        return view('livewire.admin.form-builder', [
            'bank' => $bank,
        ])->layout('layouts.app');
    }
}
