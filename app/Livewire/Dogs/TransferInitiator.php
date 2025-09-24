<?php

namespace App\Livewire\Dogs;

use App\Models\Dog;
use App\Models\DogTransfer;
use App\Services\DogTransferService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Validate;
use Livewire\Component;

class TransferInitiator extends Component
{
    use AuthorizesRequests;

    public Dog $dog;

    /** Modal state (default closed) */
    public bool $showModal = false;

    #[Validate('required|email')]
    public string $to_email = '';

    public bool $include_private_notes = false;
    public bool $include_adopter_pii   = false;

    public function mount(Dog $dog): void
    {
        $this->dog = $dog;
        $this->showModal = false; // ensure closed on mount
    }

    public function open(): void
    {
        $this->resetValidation();
        $this->showModal = true;
    }

    public function close(): void
    {
        $this->showModal = false;
    }

    public function startTransfer(DogTransferService $svc): void
    {
        $this->authorize('create', [\App\Models\DogTransfer::class, $this->dog]);
        $this->validate();

        $key = sprintf('transfer:%d:%s', $this->dog->id, strtolower($this->to_email));
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('to_email', 'Too many invites. Try again later.');
            return;
        }
        RateLimiter::hit($key, 60);

        $svc->initiate(
            dog: $this->dog,
            fromTeamId: $this->dog->team_id,
            toEmail: $this->to_email,
            opts: [
                'include_private_notes' => $this->include_private_notes,
                'include_adopter_pii'   => $this->include_adopter_pii,
            ],
            initiatorUserId: auth()->id()
        );

        // Reset + close
        $this->reset(['to_email','include_private_notes','include_adopter_pii']);
        $this->showModal = false;

        session()->flash('success', 'Transfer invite sent.');
        $this->dispatch('transfer-started');
    }

    public function render()
    {
        $pending = DogTransfer::where('dog_id', $this->dog->id)->where('status','pending')->first();
        return view('livewire.dogs.transfer-initiator', compact('pending'));
    }
}
