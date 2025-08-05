<?php

// app/Livewire/Dogs/Table.php
namespace App\Livewire\Dogs;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Dog;
use Illuminate\Support\Facades\Auth;

class Table extends Component
{
    use WithPagination;

    public function render()
    {
        $rows = Auth::user()
            ->currentTeam
            ->dogs()
            ->latest()
            ->paginate(10);

        $headers = [
            ['index' => 'name',  'label' => 'Name'],
            ['index' => 'breed', 'label' => 'Breed'],
            ['index' => 'age',   'label' => 'Age'],
            ['index' => 'sex',   'label' => 'Sex'],
            ['index' => 'action'],            // action column (slot)
        ];

        return view('livewire.dogs.table', compact('headers', 'rows'));
    }
}
