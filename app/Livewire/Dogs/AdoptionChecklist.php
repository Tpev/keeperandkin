<?php

namespace App\Livewire\Dogs;

use App\Models\AdoptionRequirement;
use App\Models\Dog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AdoptionChecklist extends Component
{
    public Dog $dog;

    /** Default checklist (created once per dog) */
    public static array $DEFAULTS = [
        'Accept a harness without resistance',
        'Remain calm when a leash is clipped on',
        'Functionally tolerate handling, including being picked up and touched, without posing a danger to themselves or others',
        'Participate in short car rides around the property without distress',
        'Transition through doorways and thresholds voluntarily',
    ];

    public function mount(Dog $dog): void
    {
        $this->dog = $dog;

        // Seed defaults if no rows exist for this dog
        if (!AdoptionRequirement::where('dog_id', $dog->id)->exists()) {
            foreach (self::$DEFAULTS as $idx => $label) {
                AdoptionRequirement::create([
                    'dog_id'   => $dog->id,
                    'label'    => $label,
                    'position' => $idx,
                ]);
            }
        }
    }

    public function toggle(int $id): void
    {
        $row = AdoptionRequirement::where('dog_id', $this->dog->id)->findOrFail($id);

        if ($row->completed_at) {
            // Uncheck
            $row->completed_at = null;
            $row->completed_by = null;
        } else {
            // Check
            $row->completed_at = now();
            $row->completed_by = Auth::id();
        }
        $row->save();

        // Optional: small toast UX if you have a toast system
        $this->dispatch('toast', type: 'success', message: 'Checklist updated.');
    }

    public function render()
    {
        $items = AdoptionRequirement::with('completedBy')
            ->where('dog_id', $this->dog->id)
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        return view('livewire.dogs.adoption-checklist', [
            'items' => $items,
        ]);
    }
}
