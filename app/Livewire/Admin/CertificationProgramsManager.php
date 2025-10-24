<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\CertificationProgram;
use App\Models\TrainingFlag;

class CertificationProgramsManager extends Component
{
    // Create/edit fields
    public ?int $editId = null;
    public string $title = '';
    public string $description = '';
    public bool $is_active = true;
    public string $visibility_mode = 'public'; // public|role_gated (prep only)
    public string $required_roles_csv = '';     // UI helper, stored as JSON array
    public ?string $difficulty = null;          // beginner|intermediate|advanced|null

    // Attach flags
    public ?int $programId = null;
    public ?int $flagToAttach = null;
    public string $flagSearch = '';
    public string $flagAudienceFilter = 'people'; // only show people flags by default
    public array $availableDifficulties = [];

    public function mount(): void
    {
        $this->availableDifficulties = CertificationProgram::DIFFICULTIES;
    }

    public function save(): void
    {
        $this->validate([
            'title'           => ['required','string','max:255'],
            'description'     => ['nullable','string'],
            'is_active'       => ['boolean'],
            'visibility_mode' => ['required', Rule::in([CertificationProgram::VIS_PUBLIC, CertificationProgram::VIS_ROLEGATED])],
            'difficulty'      => ['nullable', Rule::in(CertificationProgram::DIFFICULTIES)],
        ]);

        $roles = $this->parseRolesCsv($this->required_roles_csv);

        if ($this->editId) {
            $p = CertificationProgram::findOrFail($this->editId);
            $p->title = $this->title;
            $p->description = $this->description ?: null;
            $p->is_active = $this->is_active;
            $p->visibility_mode = $this->visibility_mode;
            $p->required_roles = !empty($roles) ? array_values($roles) : null;
            $p->difficulty = $this->difficulty ?: null;
            if (!$p->slug) $p->slug = Str::slug($p->title);
            $p->save();
        } else {
            $p = CertificationProgram::create([
                'title'           => $this->title,
                'slug'            => Str::slug($this->title),
                'description'     => $this->description ?: null,
                'is_active'       => $this->is_active,
                'visibility_mode' => $this->visibility_mode,
                'required_roles'  => !empty($roles) ? array_values($roles) : null,
                'difficulty'      => $this->difficulty ?: null,
            ]);
            $this->edit($p->id);
        }

        $this->reset(['title','description','is_active','visibility_mode','difficulty','required_roles_csv','editId']);
        $this->is_active = true;
        $this->visibility_mode = CertificationProgram::VIS_PUBLIC;
        session()->flash('success', 'Program saved.');
    }

    public function edit(int $id): void
    {
        $p = CertificationProgram::with(['flags'])->findOrFail($id);
        $this->editId = $p->id;
        $this->programId = $p->id;
        $this->title = $p->title;
        $this->description = $p->description ?? '';
        $this->is_active = (bool) $p->is_active;
        $this->visibility_mode = $p->visibility_mode ?? CertificationProgram::VIS_PUBLIC;
        $this->difficulty = $p->difficulty ?? null;
        $this->required_roles_csv = $p->required_roles ? implode(', ', $p->required_roles) : '';
    }

    public function delete(int $id): void
    {
        CertificationProgram::where('id', $id)->delete();
        if ($this->programId === $id) $this->reset(['programId','editId']);
        session()->flash('success', 'Program deleted.');
    }

    public function attachFlag(): void
    {
        if (!$this->programId || !$this->flagToAttach) return;

        $program = CertificationProgram::findOrFail($this->programId);
        $flag = TrainingFlag::findOrFail($this->flagToAttach);

        // Only allow audience=people flags
        if ($flag->audience !== 'people') {
            session()->flash('error', 'Only people-audience flags can be attached.');
            return;
        }

        $currentMax = $program->flags()->max('cert_program_flag.position') ?? 0;
        $program->flags()->syncWithoutDetaching([
            $flag->id => ['position' => $currentMax + 1]
        ]);

        $this->flagToAttach = null;
        $this->edit($program->id);
    }

    public function detachFlag(int $flagId): void
    {
        if (!$this->programId) return;

        $program = CertificationProgram::findOrFail($this->programId);
        $program->flags()->detach($flagId);

        $this->edit($program->id);
    }

    public function moveFlag(int $flagId, string $dir): void
    {
        $program = CertificationProgram::with('flags')->findOrFail($this->programId);
        $rows = $program->flags()->orderBy('cert_program_flag.position')->get();

        $arr = $rows->map(fn($f)=>['id'=>$f->id, 'pos'=>$f->pivot->position])->values();

        $idx = $arr->search(fn($r)=>$r['id'] === $flagId);
        if ($idx === false) return;

        $swapIdx = $dir === 'up' ? $idx - 1 : $idx + 1;
        if ($swapIdx < 0 || $swapIdx >= $arr->count()) return;

        [$arr[$idx]['pos'], $arr[$swapIdx]['pos']] = [$arr[$swapIdx]['pos'], $arr[$idx]['pos']];

        foreach ($arr as $r) {
            $program->flags()->updateExistingPivot($r['id'], ['position' => $r['pos']]);
        }

        $this->edit($program->id);
    }

    public function render()
    {
        // Programs list with light analytics (counts)
        $programs = CertificationProgram::query()
            ->withCount(['flags'])
            ->orderBy('title')
            ->get();

        // Available flags to attach (only people audience)
        $attachableFlags = TrainingFlag::query()
            ->where('audience', 'people')
            ->when($this->flagSearch, fn($q) =>
                $q->where('name', 'like', '%'.$this->flagSearch.'%')
                  ->orWhere('slug', 'like', '%'.$this->flagSearch.'%')
            )
            ->orderBy('name')
            ->limit(100)
            ->get();

        $selectedProgram = $this->programId
            ? CertificationProgram::with(['flags'])->find($this->programId)
            : null;

        return view('livewire.admin.certification-programs-manager', compact(
            'programs',
            'attachableFlags',
            'selectedProgram'
        ))->layout('layouts.app');
    }

    private function parseRolesCsv(?string $csv): array
    {
        if (!$csv) return [];
        $parts = array_filter(array_map('trim', explode(',', $csv)));
        $uniq = array_values(array_unique($parts));
        return array_slice($uniq, 0, 50);
    }
}
