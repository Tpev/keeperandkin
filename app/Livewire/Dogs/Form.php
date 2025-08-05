<?php

namespace App\Livewire\Dogs;

use Livewire\Component;
use App\Models\Dog;
use Illuminate\Support\Facades\Auth;

class Form extends Component
{
    public $name, $breed, $age, $sex, $description;

    protected $rules = [
        'name'        => 'required|string|max:255',
        'breed'       => 'nullable|string|max:255',
        'age'         => 'nullable|integer|min:0|max:30',
        'sex'         => 'nullable|in:male,female',
        'description' => 'nullable|string',
    ];

    public function save()
    {
        $data = $this->validate();

        Auth::user()->currentTeam->dogs()->create($data);

        session()->flash('success', 'Dog added!');
        return redirect()->route('dogs.index');
    }

    public function render()
    {
        return view('livewire.dogs.form');
    }
}
