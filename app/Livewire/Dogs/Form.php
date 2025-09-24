<?php

namespace App\Livewire\Dogs;

use App\Models\Dog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads;

    // ===== Basic fields =====
    public ?string $serial_number = null;
    public ?string $name = null;
    public ?string $breed = null;
    public ?float  $age = null;
    /** @var 'male'|'female'|null */
    public ?string $sex = null;
    public ?string $description = null;

    // ===== New profile fields =====
    public ?string $location = null;
    /** Accepts 'MM/DD/YYYY' or 'YYYY-MM-DD'; model mutator normalizes to DATE */
    public ?string $approx_dob = null;

    /** Stored as nullable boolean; UI uses '', '1', '0' */
    public string|bool|null $fixed = null;

    public ?string $color = null;
    public ?string $size = null;
    public ?string $microchip = null;

    public ?string $heartworm = null; // "Heatworm" in request; using "heartworm"
    public ?string $fiv_l = null;
    public ?string $flv = null;

    public ?string $housetrained = null;
    public ?string $good_with_dogs = null;
    public ?string $good_with_cats = null;
    public ?string $good_with_children = null;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $photo = null;

    public function mount(): void
    {
        // Default to male so a non-interacted select still saves correctly
        $this->sex = 'male';
    }

    protected function rules(): array
    {
        return [
            'serial_number'     => ['nullable','string','max:50','unique:dogs,serial_number'],
            'name'              => ['required','string','max:255'],
            'breed'             => ['nullable','string','max:255'],
            'age'               => ['nullable','numeric','min:0','max:30'],
            'sex'               => ['required','in:male,female'],
            'description'       => ['nullable','string'],

            'location'          => ['nullable','string','max:255'],
            // Let the model mutator handle parsing; keep validation permissive
            'approx_dob'        => ['nullable','string','max:10'],

            // Accept '', '0', '1' from the select; cast later
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

            'photo'             => ['nullable','image','max:4096'], // 4MB
        ];
    }

    public function save()
    {
        // Ensure valid inputs first
        $data = $this->validate();

        // Normalize 'fixed' to nullable boolean
        // '' => null, '1'/'0' => true/false
        $fixed = $this->fixed;
        if ($fixed === '' || $fixed === null) {
            $data['fixed'] = null;
        } else {
            $data['fixed'] = in_array((string) $fixed, ['1', 'true'], true) ? true : false;
        }

        // Photo upload
        if ($this->photo) {
            $data['photo'] = $this->photo->store('dogs', 'public'); // e.g., "dogs/abc.jpg"
        }

        // Create under current team
        Auth::user()->currentTeam->dogs()->create([
            // Existing core
            'serial_number'      => $data['serial_number'] ?? null,
            'name'               => $data['name'],
            'breed'              => $data['breed'] ?? null,
            'age'                => $data['age'] ?? null,
            'sex'                => $data['sex'],
            'description'        => $data['description'] ?? null,
            'photo'              => $data['photo'] ?? null,

            // New fields
            'location'           => $data['location'] ?? null,
            'approx_dob'         => $data['approx_dob'] ?? null, // model mutator will normalize to Y-m-d
            'fixed'              => $data['fixed'],               // normalized above
            'color'              => $data['color'] ?? null,
            'size'               => $data['size'] ?? null,
            'microchip'          => $data['microchip'] ?? null,

            'heartworm'          => $data['heartworm'] ?? null,
            'fiv_l'              => $data['fiv_l'] ?? null,
            'flv'                => $data['flv'] ?? null,

            'housetrained'       => $data['housetrained'] ?? null,
            'good_with_dogs'     => $data['good_with_dogs'] ?? null,
            'good_with_cats'     => $data['good_with_cats'] ?? null,
            'good_with_children' => $data['good_with_children'] ?? null,
        ]);

        session()->flash('success', 'Dog added!');
        return redirect()->route('dogs.index');
    }

    public function render()
    {
        return view('livewire.dogs.form');
    }
}
