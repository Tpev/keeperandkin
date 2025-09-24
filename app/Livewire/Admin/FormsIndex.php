<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\EvaluationForm;
use App\Models\EvaluationSection;
use App\Models\EvaluationFormQuestion;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class FormsIndex extends Component
{
    public $search = '';
    public $teamId = null; // Optional: allow team scoping later
    public $createModal = false;

    // create form fields
    public $name = '';
    public $slug = '';
    public $is_active = false;

    public function mount(): void
    {
        // default listing includes global + team-specific (if you later add team picker)
    }

    public function updatedName()
    {
        if (!$this->slug) {
            $this->slug = \Str::slug($this->name);
        }
    }

    public function openCreate()
    {
        $this->resetValidation();
        $this->name = '';
        $this->slug = '';
        $this->is_active = false;
        $this->createModal = true;
    }

    public function createForm()
    {
        $this->validate([
            'name' => ['required','string','max:255'],
            'slug' => ['required','string','max:255', Rule::unique('evaluation_forms','slug')->where(function($q){
                return $q->whereNull('team_id')->where('version', 1);
            })],
            'is_active' => ['boolean'],
        ]);

        DB::transaction(function () {
            if ($this->is_active) {
                // Deactivate any other active global forms
                EvaluationForm::query()->whereNull('team_id')->where('is_active', true)->update(['is_active' => false]);
            }

            $form = EvaluationForm::create([
                'team_id'   => null,
                'name'      => $this->name,
                'slug'      => $this->slug,
                'version'   => 1,
                'is_active' => (bool)$this->is_active,
            ]);

            // Default 3 sections, positioned
            $sections = [
                ['title' => 'Comfort & Confidence', 'slug' => 'comfort-confidence', 'position' => 1],
                ['title' => 'Sociability',          'slug' => 'sociability',         'position' => 2],
                ['title' => 'Trainability',         'slug' => 'trainability',        'position' => 3],
            ];
            foreach ($sections as $s) {
                EvaluationSection::create([
                    'form_id'  => $form->id,
                    'title'    => $s['title'],
                    'slug'     => $s['slug'],
                    'position' => $s['position'],
                ]);
            }

            $this->createModal = false;
            session()->flash('success', 'Form created.');
            return redirect()->route('admin.forms.edit', $form);
        });
    }

    public function cloneAsDraft(int $formId)
    {
        $orig = EvaluationForm::with(['sections','formQuestions'])->findOrFail($formId);

        DB::transaction(function () use ($orig) {
            $newVersion = (int)($orig->version + 1);

            $draft = EvaluationForm::create([
                'team_id'   => $orig->team_id,
                'name'      => $orig->name.' (v'.$newVersion.' draft)',
                'slug'      => $orig->slug,
                'version'   => $newVersion,
                'is_active' => false,
            ]);

            // Clone sections
            $mapSection = [];
            foreach ($orig->sections()->orderBy('position')->get() as $sec) {
                $copy = EvaluationSection::create([
                    'form_id'  => $draft->id,
                    'title'    => $sec->title,
                    'slug'     => $sec->slug,
                    'position' => $sec->position,
                ]);
                $mapSection[$sec->id] = $copy->id;
            }

            // Clone form questions (pointing to the same Question IDs — immutable question bank)
            foreach ($orig->formQuestions()->orderBy('position')->get() as $fq) {
                EvaluationFormQuestion::create([
                    'form_id'    => $draft->id,
                    'section_id' => $mapSection[$fq->section_id] ?? null,
                    'question_id'=> $fq->question_id,
                    'position'   => $fq->position,
                    'required'   => $fq->required,
                    'visibility' => $fq->visibility,
                    'meta'       => $fq->meta,
                ]);
            }

            session()->flash('success', "Draft v{$newVersion} created.");
            return redirect()->route('admin.forms.edit', $draft);
        });
    }

    public function publish(int $formId)
    {
        $form = EvaluationForm::findOrFail($formId);

        DB::transaction(function () use ($form) {
            // Deactivate existing active form in same scope
            EvaluationForm::query()
                ->where('slug', $form->slug)
                ->where('team_id', $form->team_id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $form->is_active = true;
            $form->save();

            session()->flash('success', "Published v{$form->version}.");
        });
    }

    public function unpublish(int $formId)
    {
        $form = EvaluationForm::findOrFail($formId);
        $form->is_active = false;
        $form->save();

        session()->flash('success', "Unpublished v{$form->version}.");
    }

    public function deleteForm(int $formId)
    {
        $form = EvaluationForm::withCount('formQuestions')->findOrFail($formId);
        if ($form->is_active) {
            session()->flash('error', 'Cannot delete an active form. Unpublish first.');
            return;
        }
        // (Optional) also block delete if evaluations reference this form
        $form->delete();
        session()->flash('success', 'Form deleted.');
    }

    public function render()
    {
        $forms = EvaluationForm::query()
            ->when($this->teamId !== null, fn($q) => $q->where('team_id', $this->teamId))
            ->when($this->search, fn($q) => $q->where(function($qq){
                $qq->where('name','like','%'.$this->search.'%')
                   ->orWhere('slug','like','%'.$this->search.'%');
            }))
            ->orderBy('slug')
            ->orderBy('version', 'desc')
            ->paginate(20);

        return view('livewire.admin.forms-index', [
            'forms' => $forms,
        ])->layout('layouts.app');
    }
}
