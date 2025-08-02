<?php

namespace App\Livewire;

use Livewire\Component;

class TallstackTest extends Component
{
    public function render()
    {
        return view('livewire.tallstack-test')->layout('layouts.guest');
    }
}
