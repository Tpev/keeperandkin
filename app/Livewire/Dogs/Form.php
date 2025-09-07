<?php

namespace App\Livewire\Dogs;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Dog;
use Illuminate\Support\Facades\Auth;

class Form extends Component
{
    use WithFileUploads;

    public $serial_number;
    public $name, $breed, $age, $sex, $description, $photo;

    protected $rules = [
        'serial_number' => 'nullable|string|max:50|unique:dogs,serial_number',
        'name'          => 'required|string|max:255',
        'breed'         => 'nullable|string|max:255',
        'age'           => 'nullable|numeric|min:0|max:30',
        'sex'           => 'nullable|in:male,female',
        'description'   => 'nullable|string',
        'photo'         => 'nullable|image|max:4096',
    ];

    public function save()
    {
        $data = $this->validate();

        if ($this->photo) {
            $data['photo_path'] = $this->photo->store('dogs', 'public');
        }

        Auth::user()->currentTeam->dogs()->create($data);

        session()->flash('success', 'Dog added!');
        return redirect()->route('dogs.index');
    }

    public function render()
    {
        return view('livewire.dogs.form');
    }
}
