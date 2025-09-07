<?php

// app/Livewire/Dogs/Show.php
namespace App\Livewire\Dogs;

use Livewire\Component;
use App\Models\Dog;

class Show extends Component
{
    public Dog $dog;

    public function mount(Dog $dog): void
    {
        $this->dog = $dog->loadMissing('latestEvaluation');
    }

    public function render()
    {
        return view('livewire.dogs.show', [
            'latestEval' => $this->dog->latestEvaluation, // ✅ pass it
        ]);
    }
}
