<?php

namespace App\Livewire\ShelterAdmin;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.shelter-admin.dashboard')->layout('layouts.app');
    }
}
