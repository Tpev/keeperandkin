<?php

namespace App\Livewire\Dogs;

use Livewire\Component;
use App\Models\Dog;
use App\Actions\Training\GenerateDogTrainingPlan;

class TrainingPlanButton extends Component
{
    public Dog $dog;
    public bool $working = false;
    public ?string $message = null;

    public function mount(Dog $dog): void
    {
        $this->dog = $dog;
    }

    public function generate(GenerateDogTrainingPlan $action): void
    {
        $this->working = true;
        $this->message = null;

        try {
            $count = $action->handle($this->dog);
            $this->message = $count > 0
                ? "Training plan generated/updated: {$count} session(s)."
                : "No sessions matched the latest evaluation. Check mappings?";
            $this->dispatch('training-plan-updated'); // optional for other components
        } catch (\Throwable $e) {
            report($e);
            $this->message = "Failed to generate training plan.";
        } finally {
            $this->working = false;
        }
    }

    public function render()
    {
        return view('livewire.dogs.training-plan-button');
    }
}
