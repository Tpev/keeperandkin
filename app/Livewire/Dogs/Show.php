<?php

namespace App\Livewire\Dogs;

use Livewire\Component;
use App\Models\Dog;

class Show extends Component
{
    public Dog $dog;               // passed in from the Blade page

    public function mount(Dog $dog): void
    {
        $this->dog = $dog;
    }

    public function render()
    {
        // This view is just the *inner* markup, no layout wrapper
        return view('livewire.dogs.show');
    }
}
