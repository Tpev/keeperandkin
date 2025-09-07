<?php

// app/Livewire/Dogs/VetCorner.php
namespace App\Livewire\Dogs;

use Livewire\Component;
use App\Models\Dog;
use App\Models\VetMetric;
use App\Models\VetVisit;

class VetCorner extends Component
{
    public Dog $dog;

    // Metrics form
    public ?float $current_weight = null;
    public ?int   $bcs = null; // 1–9
    public ?string $next_vaccine_date = null; // 'Y-m-d'

    // Visit form
    public ?string $visit_date = null;
    public ?string $reason = null;
    public ?string $outcome = null;
    public ?float $visit_weight = null;

    public bool $showVisits = false;

    public function mount(Dog $dog): void
    {
        $this->dog = $dog->load(['vetMetric','vetVisits']);

        if ($dog->vetMetric) {
            $this->current_weight    = $dog->vetMetric->current_weight;
            $this->bcs               = $dog->vetMetric->bcs;
            $this->next_vaccine_date = optional($dog->vetMetric->next_vaccine_date)->format('Y-m-d');
        }
    }

    public function saveMetrics(): void
    {
        $data = $this->validate([
            'current_weight'    => ['nullable','numeric','between:0,200'],
            'bcs'               => ['nullable','integer','between:1,9'],
            'next_vaccine_date' => ['nullable','date'],
        ]);

        VetMetric::updateOrCreate(
            ['dog_id' => $this->dog->id],
            $data
        );

        $this->dispatch('toast', type: 'success', message: 'Vet metrics saved.');
        $this->dog->load('vetMetric');
    }

    public function addVisit(): void
    {
        $data = $this->validate([
            'visit_date'   => ['required','date'],
            'reason'       => ['required','string','max:160'],
            'outcome'      => ['nullable','string'],
            'visit_weight' => ['nullable','numeric','between:0,200'],
        ]);

        VetVisit::create([
            'dog_id'     => $this->dog->id,
            'visit_date' => $data['visit_date'],
            'reason'     => $data['reason'],
            'outcome'    => $data['outcome'] ?? null,
            'weight'     => $data['visit_weight'] ?? null,
        ]);

        // reset tiny form
        $this->visit_date = $this->reason = $this->outcome = null;
        $this->visit_weight = null;

        $this->dispatch('toast', type: 'success', message: 'Visit added.');
        $this->dog->load('vetVisits');
        $this->showVisits = true;
    }

    public function deleteVisit(int $id): void
    {
        $visit = VetVisit::where('dog_id', $this->dog->id)->findOrFail($id);
        $visit->delete();
        $this->dispatch('toast', type: 'success', message: 'Visit removed.');
        $this->dog->load('vetVisits');
    }

    public function render()
    {
        return view('livewire.dogs.vet-corner');
    }
}
