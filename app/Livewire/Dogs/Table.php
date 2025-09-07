<?php

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
// App\Livewire\Dogs\Table.php
$rows = Auth::user()
    ->currentTeam
    ->dogs()
    ->with('latestEvaluation')   // ← no column slice = no ambiguity
    ->withCount('evaluations')
    ->latest()
    ->paginate(10);


    $headers = [
        ['index' => 'name',  'label' => 'Dog'],
        ['index' => 'age',   'label' => 'Age'],
        ['index' => 'sex',   'label' => 'Sex'],
        ['index' => 'score', 'label' => 'Score'],
        ['index' => 'flag',  'label' => 'Flag'],
        ['index' => 'action'],
    ];

    return view('livewire.dogs.table', compact('headers', 'rows'));
}

}
