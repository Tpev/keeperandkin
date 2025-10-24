<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\TrainingFlag;
use App\Models\TrainingSession;
use App\Models\AnswerOption;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TrainingFlagsManager extends Component
{
    // Create/edit
    public string $name = '';
    public string $description = '';
    public bool $is_active = true;
    public string $audience = 'dog'; // NEW: default dog
    public ?int $editId = null;

    // Attach sessions & options
    public ?int $flagId = null;
    public ?int $sessionToAttach = null;
    public ?int $optionToAttach = null;

    // Filters & search
    public string $filterAudience = 'all'; // NEW: all|dog|people
    public string $searchOptions = '';

    public function save(): void
    {
        $this->validate([
            'name'        => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'is_active'   => ['boolean'],
            'audience'    => ['required', Rule::in(TrainingFlag::AUDIENCE_VALUES)], // NEW
        ]);

        if ($this->editId) {
            $flag = TrainingFlag::findOrFail($this->editId);
            $flag->name = $this->name;
            $flag->description = $this->description ?: null;
            $flag->is_active = $this->is_active;
            $flag->audience = $this->audience; // NEW
            if (!$flag->slug) $flag->slug = Str::slug($flag->name);
            $flag->save();
        } else {
            TrainingFlag::create([
                'name'        => $this->name,
                'slug'        => Str::slug($this->name),
                'description' => $this->description ?: null,
                'is_active'   => $this->is_active,
                'audience'    => $this->audience, // NEW
            ]);
        }

        $this->reset(['name','description','is_active','audience','editId']);
        $this->audience = 'dog'; // preserve default after reset
        session()->flash('success','Flag saved.');
    }

    public function edit(int $id): void
    {
        $f = TrainingFlag::with(['sessions','answerOptions'])->findOrFail($id);
        $this->editId = $f->id;
        $this->flagId = $f->id;
        $this->name = $f->name;
        $this->description = $f->description ?? '';
        $this->is_active = (bool) $f->is_active;
        $this->audience = $f->audience ?: 'dog'; // NEW
    }

    public function delete(int $id): void
    {
        TrainingFlag::where('id',$id)->delete();
        if ($this->flagId === $id) $this->reset(['flagId','editId']);
        session()->flash('success','Flag deleted.');
    }

    public function attachSession(): void
    {
        if (!$this->flagId || !$this->sessionToAttach) return;
        $flag = TrainingFlag::findOrFail($this->flagId);
        $sess = TrainingSession::findOrFail($this->sessionToAttach);

        $currentMax = $flag->sessions()->max('training_flag_training_session.position') ?? 0;
        $flag->sessions()->syncWithoutDetaching([$sess->id => ['position' => $currentMax + 1]]);
        $this->sessionToAttach = null;

        // Refresh selected flag to reflect change
        $this->edit($flag->id);
    }

    public function detachSession(int $sessionId): void
    {
        if (!$this->flagId) return;
        TrainingFlag::findOrFail($this->flagId)->sessions()->detach($sessionId);
        $this->edit($this->flagId);
    }

    public function moveSession(int $sessionId, string $dir): void
    {
        $flag = TrainingFlag::with('sessions')->findOrFail($this->flagId);
        $rows = $flag->sessions()->orderBy('training_flag_training_session.position')->get();
        $arr = $rows->map(fn($s)=>[
            'id'=>$s->id,
            'pos'=>$s->pivot->position
        ])->values();

        $idx = $arr->search(fn($r)=>$r['id']===$sessionId);
        if ($idx === false) return;

        $swapIdx = $dir === 'up' ? $idx-1 : $idx+1;
        if ($swapIdx < 0 || $swapIdx >= $arr->count()) return;

        [$arr[$idx]['pos'], $arr[$swapIdx]['pos']] = [$arr[$swapIdx]['pos'], $arr[$idx]['pos']];

        foreach ($arr as $r) {
            $flag->sessions()->updateExistingPivot($r['id'], ['position'=>$r['pos']]);
        }

        $this->edit($flag->id);
    }

    public function attachOption(): void
    {
        if (!$this->flagId || !$this->optionToAttach) return;
        $flag = TrainingFlag::findOrFail($this->flagId);
        $flag->answerOptions()->syncWithoutDetaching([$this->optionToAttach]);
        $this->optionToAttach = null;

        $this->edit($flag->id);
    }

    public function detachOption(int $optionId): void
    {
        if (!$this->flagId) return;
        TrainingFlag::findOrFail($this->flagId)->answerOptions()->detach($optionId);
        $this->edit($this->flagId);
    }

    public function render()
    {
        $flags = TrainingFlag::withCount(['sessions','answerOptions'])
            ->when($this->filterAudience !== 'all', fn($q) => $q->where('audience', $this->filterAudience)) // NEW
            ->orderBy('name')
            ->get();

        $sessions = TrainingSession::orderBy('name')->get();

        $selectedFlag = $this->flagId
            ? TrainingFlag::with(['sessions','answerOptions'])->find($this->flagId)
            : null;

        $options = AnswerOption::query()
            ->when($this->searchOptions, fn($q) =>
                $q->where('label','like','%'.$this->searchOptions.'%')
                  ->orWhere('value','like','%'.$this->searchOptions.'%'))
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('livewire.admin.training-flags-manager', compact('flags','sessions','selectedFlag','options'))
            ->layout('layouts.app');
    }
}
