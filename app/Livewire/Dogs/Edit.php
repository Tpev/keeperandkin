<?php

namespace App\Livewire\Dogs;

use Livewire\Component;
use App\Models\Dog;

class Edit extends Component
{
    public Dog $dog;

    public $name;
    public $breed;
    public $age;
    public $sex;
    public $description;

    protected function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'breed'       => ['nullable', 'string', 'max:255'],
            'age'         => ['nullable', 'integer', 'min:0', 'max:30'],
            'sex'         => ['nullable', 'in:male,female'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function mount(Dog $dog): void
    {
        $this->dog        = $dog;
        $this->name       = $dog->name;
        $this->breed      = $dog->breed;
        $this->age        = $dog->age;
        $this->sex        = $dog->sex;
        $this->description= $dog->description;
    }

    public function update(): void
    {
        $data = $this->validate();
        $this->dog->update($data);

        session()->flash('success', 'Dog updated!');
        $this->redirectRoute('dogs.index');
    }

    public function render()
    {
        return view('livewire.dogs.edit');
    }
}
