<?php

namespace App\Livewire\Dogs;

use Livewire\Component;
use App\Models\Dog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DeleteButton extends Component
{
    public Dog $dog;

    public function mount(Dog $dog): void
    {
        $this->dog = $dog;
    }

public function delete(): mixed
{
    $userTeamId = auth()->user()?->currentTeam?->id;
    if ($userTeamId !== $this->dog->team_id) {
        abort(403, 'Not allowed to delete this dog.');
    }

    $this->dog->delete(); // or ->forceDelete()

    session()->flash('status', 'Dog removed successfully.');

    // ✅ Force a real browser navigation so the flash survives
    return $this->redirectRoute('dogs.index', navigate: true);
    // alt: return $this->redirect(route('dogs.index'), navigate: true);
}


    public function render()
    {
        return view('livewire.dogs.delete-button');
    }
}
