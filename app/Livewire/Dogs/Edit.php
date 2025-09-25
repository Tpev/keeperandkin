<?php

namespace App\Livewire\Dogs;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Dog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class Edit extends Component
{
    use WithFileUploads;

    public Dog $dog;

    // Core
    public $serial_number;
    public $name;
    public $breed;
    public $age;
    public $sex;
    public $description;

    // New fields
    public $location;
    public $approx_dob;      // shown as m/d/Y in the form; model mutator accepts m/d/Y or Y-m-d
    public $fixed;           // '', '1', '0' | true/false/null accepted by rules
    public $color;
    public $size;
    public $microchip;

    public $heartworm;
    public $fiv_l;
    public $flv;

    public $housetrained;
    public $good_with_dogs;
    public $good_with_cats;
    public $good_with_children;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $new_photo = null;  // for uploading a new profile picture

    protected function rules(): array
    {
        return [
            'serial_number'     => ['nullable', 'string', 'max:50', Rule::unique('dogs','serial_number')->ignore($this->dog->id)],
            'name'              => ['required', 'string', 'max:255'],
            'breed'             => ['nullable', 'string', 'max:255'],
            'age'               => ['nullable', 'numeric', 'min:0', 'max:30'],
            'sex'               => ['required', 'in:male,female'],
            'description'       => ['nullable', 'string'],

            'location'          => ['nullable', 'string', 'max:255'],
            'approx_dob'        => ['nullable','string','max:10'],
            'fixed'             => ['nullable', Rule::in(['', '0', '1', 0, 1, true, false])],
            'color'             => ['nullable','string','max:255'],
            'size'              => ['nullable','string','max:255'],
            'microchip'         => ['nullable','string','max:255'],

            'heartworm'         => ['nullable','string','max:255'],
            'fiv_l'             => ['nullable','string','max:255'],
            'flv'               => ['nullable','string','max:255'],

            'housetrained'      => ['nullable','string','max:255'],
            'good_with_dogs'    => ['nullable','string','max:255'],
            'good_with_cats'    => ['nullable','string','max:255'],
            'good_with_children'=> ['nullable','string','max:255'],

            // New photo (optional)
            'new_photo'         => ['nullable','image','max:4096'], // 4MB
        ];
    }

    public function mount(Dog $dog): void
    {
        $this->dog = $dog;

        // Core
        $this->serial_number     = $dog->serial_number;
        $this->name              = $dog->name;
        $this->breed             = $dog->breed;
        $this->age               = $dog->age;
        $this->sex               = $dog->sex;
        $this->description       = $dog->description;

        // New
        $this->location          = $dog->location;
        $this->approx_dob        = $dog->approx_dob?->format('m/d/Y'); // show as US format
        $this->fixed             = $dog->fixed;                        // can be null/true/false
        $this->color             = $dog->color;
        $this->size              = $dog->size;
        $this->microchip         = $dog->microchip;

        $this->heartworm         = $dog->heartworm;
        $this->fiv_l             = $dog->fiv_l;
        $this->flv               = $dog->flv;

        $this->housetrained      = $dog->housetrained;
        $this->good_with_dogs    = $dog->good_with_dogs;
        $this->good_with_cats    = $dog->good_with_cats;
        $this->good_with_children= $dog->good_with_children;
    }

    public function update(): void
    {
        $data = $this->validate();

        // Normalize fixed to nullable boolean
        if ($data['fixed'] === '' || $data['fixed'] === null) {
            $data['fixed'] = null;
        } else {
            $data['fixed'] = in_array((string) $data['fixed'], ['1','true'], true);
        }

        // Handle profile photo replacement (optional)
        if ($this->new_photo) {
            // Store new image to public disk
            $newPath = $this->new_photo->store('dogs', 'public'); // e.g., "dogs/abc.jpg"

            // Delete old file if it exists and is on the public disk
            if ($this->dog->photo_path && \Storage::disk('public')->exists($this->dog->photo_path)) {
                \Storage::disk('public')->delete($this->dog->photo_path);
            }

            $data['photo_path'] = $newPath;
        }

        // Update the dog
        $this->dog->update($data);

        session()->flash('success', 'Dog updated!');
        $this->redirectRoute('dogs.show', $this->dog);
    }

    public function render()
    {
        // Use a Livewire view under resources/views/livewire/dogs/edit.blade.php
        return view('livewire.dogs.edit');
    }
}
