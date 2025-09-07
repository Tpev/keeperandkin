<?php

namespace App\Livewire\Dogs;

use Livewire\Component;
use App\Models\Dog;
use App\Models\DietProfile;
use App\Models\DietEntry;
use Illuminate\Validation\Rule;

class Dietician extends Component
{
    public Dog $dog;

    // Profile form
    public ?string $food_brand = null;
    public ?string $food_name = null;
    public ?string $food_type = null;           // kibble/wet/raw/home-cooked
    public ?int    $daily_calories = null;      // kcal/day target
    public ?int    $meals_per_day = null;
    public ?float  $portion_grams_per_meal = null;
    public string  $allergies_csv = '';         // CSV to/from JSON
    public string  $supplements_csv = '';
    public ?string $notes = null;
    public ?string $last_reviewed_at = null;    // Y-m-d

    // Entry form
    public ?string $fed_at = null;              // Y-m-d\TH:i
    public ?string $meal = null;
    public ?string $entry_food = null;
    public ?float  $grams = null;
    public ?int    $calories = null;
    public ?int    $appetite = null;            // 1–5
    public ?string $comment = null;

    public bool $showEntries = false;

    public function mount(Dog $dog): void
    {
        $this->dog = $dog->load(['dietProfile','dietEntries']);

        if ($dog->dietProfile) {
            $p = $dog->dietProfile;
            $this->food_brand = $p->food_brand;
            $this->food_name  = $p->food_name;
            $this->food_type  = $p->food_type;
            $this->daily_calories = $p->daily_calories;
            $this->meals_per_day  = $p->meals_per_day;
            $this->portion_grams_per_meal = $p->portion_grams_per_meal;
            $this->allergies_csv  = implode(', ', $p->allergies ?? []);
            $this->supplements_csv= implode(', ', $p->supplements ?? []);
            $this->notes          = $p->notes;
            $this->last_reviewed_at = optional($p->last_reviewed_at)->format('Y-m-d');
        }
    }

    public function saveProfile(): void
    {
        $data = $this->validate([
            'food_brand' => ['nullable','string','max:120'],
            'food_name'  => ['nullable','string','max:160'],
            'food_type'  => ['nullable','string','max:60'],
            'daily_calories' => ['nullable','integer','between:0,5000'],
            'meals_per_day'  => ['nullable','integer','between:1,6'],
            'portion_grams_per_meal' => ['nullable','numeric','between:0,2000'],
            'allergies_csv'   => ['nullable','string'],
            'supplements_csv' => ['nullable','string'],
            'notes'           => ['nullable','string'],
            'last_reviewed_at'=> ['nullable','date'],
        ]);

        $payload = [
            'food_brand' => $data['food_brand'] ?? null,
            'food_name'  => $data['food_name'] ?? null,
            'food_type'  => $data['food_type'] ?? null,
            'daily_calories' => $data['daily_calories'] ?? null,
            'meals_per_day'  => $data['meals_per_day'] ?? null,
            'portion_grams_per_meal' => $data['portion_grams_per_meal'] ?? null,
            'allergies'   => $this->toArrayFromCsv($this->allergies_csv),
            'supplements' => $this->toArrayFromCsv($this->supplements_csv),
            'notes'       => $data['notes'] ?? null,
            'last_reviewed_at' => $data['last_reviewed_at'] ?? null,
        ];

        DietProfile::updateOrCreate(['dog_id' => $this->dog->id], $payload);

        $this->dog->load('dietProfile');
        $this->dispatch('toast', type: 'success', message: 'Diet profile saved.');
    }

    public function addEntry(): void
    {
        $data = $this->validate([
            'fed_at'   => ['required','date'],
            'meal'     => ['nullable','string','max:40'],
            'entry_food' => ['nullable','string','max:160'],
            'grams'    => ['nullable','numeric','between:0,2000'],
            'calories' => ['nullable','integer','between:0,4000'],
            'appetite' => ['nullable','integer','between:1,5'],
            'comment'  => ['nullable','string'],
        ]);

        DietEntry::create([
            'dog_id'   => $this->dog->id,
            'fed_at'   => $data['fed_at'],
            'meal'     => $data['meal'] ?? null,
            'food'     => $data['entry_food'] ?? null,
            'grams'    => $data['grams'] ?? null,
            'calories' => $data['calories'] ?? null,
            'appetite' => $data['appetite'] ?? null,
            'comment'  => $data['comment'] ?? null,
        ]);

        // reset entry form
        $this->fed_at = $this->meal = $this->entry_food = $this->comment = null;
        $this->grams = $this->calories = $this->appetite = null;

        $this->dog->load('dietEntries');
        $this->showEntries = true;
        $this->dispatch('toast', type: 'success', message: 'Feeding entry added.');
    }

    public function deleteEntry(int $id): void
    {
        $entry = DietEntry::where('dog_id', $this->dog->id)->findOrFail($id);
        $entry->delete();
        $this->dog->load('dietEntries');
        $this->dispatch('toast', type: 'success', message: 'Entry removed.');
    }

    private function toArrayFromCsv(?string $csv): array
    {
        if (!$csv) return [];
        return collect(explode(',', $csv))
            ->map(fn($s)=>trim($s))
            ->filter()
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.dogs.dietician');
    }
}
