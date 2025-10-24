<?php

namespace App\Livewire\Learn;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\CertificationProgram;
use App\Models\CertificationEnrollment;
use Illuminate\Database\Eloquent\Collection;

class ProgramsIndex extends Component
{
    /** @var Collection<CertificationProgram> */
    public Collection $programs;

    public function mount(): void
    {
        $user = Auth::user();

        $all = CertificationProgram::query()
            ->withCount(['flags'])
            ->orderBy('title')
            ->get();

        // Respect is_active + role gating
        $this->programs = $all->filter(fn($p) => $p->visibleTo($user))->values();
    }

    public function render()
    {
        // Map enrollments for progress display
        $user = Auth::user();
        $enroll = CertificationEnrollment::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('cert_program_id');

        return view('livewire.learn.programs-index', [
            'enrollments' => $enroll,
        ])->layout('layouts.app');
    }
}
