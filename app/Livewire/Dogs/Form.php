<?php

namespace App\Livewire\Dogs;

use App\Models\Dog;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads;

    // Form fields
    public ?string $serial_number = null;
    public ?string $name = null;
    public ?string $breed = null;
    public ?float  $age = null;

    /** @var 'male'|'female'|null */
    public ?string $sex = null;

    public ?string $description = null;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $photo = null;

    /**
     * Default values for create
     */
    public function mount(): void
    {
        // Default to male so a non-interacted select still saves correctly
        $this->sex = 'male';
    }

    /**
     * Validation rules for CREATE.
     * - sex is REQUIRED (no silent null).
     * - photo saved to `photo` column (adjust if your schema uses another name).
     */
    protected function rules(): array
    {
        return [
            'serial_number' => ['nullable','string','max:50','unique:dogs,serial_number'],
            'name'          => ['required','string','max:255'],
            'breed'         => ['nullable','string','max:255'],
            'age'           => ['nullable','numeric','min:0','max:30'],
            'sex'           => ['required','in:male,female'],
            'description'   => ['nullable','string'],
            'photo'         => ['nullable','image','max:4096'], // 4MB
        ];
    }

    public function save()
    {
        // Normalize sex (safety net)
        $this->sex = in_array(strtolower((string) $this->sex), ['male','female'], true)
            ? strtolower($this->sex)
            : 'male';

        $data = $this->validate();

        // Store photo to public disk; save relative path in `photo`
        if ($this->photo) {
            $data['photo'] = $this->photo->store('dogs', 'public'); // e.g. "dogs/abc.jpg"
        }

        // Create under current team
        Auth::user()->currentTeam->dogs()->create($data);

        session()->flash('success', 'Dog added!');
        return redirect()->route('dogs.index');
    }

    public function render()
    {
        return view('livewire.dogs.form');
    }
}
