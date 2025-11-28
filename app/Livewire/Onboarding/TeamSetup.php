<?php

namespace App\Livewire\Onboarding;

use App\Enums\TeamSetupType;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TeamSetup extends Component
{
    public ?string $setup_type = null;

    #[Computed]
    public function team()
    {
        return auth()->user()->currentTeam;
    }

    public function mount()
    {
        if ($this->team->isSetupComplete()) {
            return redirect()->route('dashboard');
        }
    }

    public function save()
    {
        $this->validate([
            'setup_type' => ['required', 'in:'.implode(',', array_column(TeamSetupType::cases(), 'value'))],
        ]);

        $team = $this->team;
        $team->setup_type = $this->setup_type;
        $team->save();

        // Assign the registering user’s role based on team type.
        $team->assignInitialAdminRoleForUser(auth()->user());

        session()->flash('success', 'Team configured. You can now invite other roles.');
        return redirect()->route('dogs.index');
    }

    public function render()
    {
        return view('livewire.onboarding.team-setup', [
            'types' => TeamSetupType::cases(),
        ]);
    }
}
