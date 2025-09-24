<?php

namespace App\Livewire\Transfers;

use App\Models\DogTransfer;
use App\Services\DogTransferService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class AcceptTransfer extends Component
{
    public DogTransfer $transfer;

    /** Raw token from ?t=... */
    public string $token = '';

    /** Existing team selection (required) */
    public ?int $destination_team_id = null;

    /** Must be checked */
    public bool $confirm_authority = false;

    public function mount(DogTransfer $transfer): void
    {
        $this->transfer = $transfer;
        $this->token = (string) request()->query('t', '');
        if ($this->token === '') {
            $this->addError('token', 'Missing token.');
        }

        // Preselect the current user's first team if available
        if (Auth::check()) {
            $firstTeam = Auth::user()->allTeams()->first();
            if ($firstTeam && !$this->destination_team_id) {
                $this->destination_team_id = (int) $firstTeam->id;
            }
        }
    }

    public function getTeamsProperty()
    {
        return Auth::check() ? Auth::user()->allTeams() : collect();
    }

    public function confirmAccept(DogTransferService $svc)
    {
        // Basic link guards
        if (!$this->transfer->isPending() || $this->transfer->isExpired()) {
            $this->addError('token','This link is no longer valid.');
            return;
        }
        if ($this->token === '' || !$svc->validateToken($this->transfer, $this->token)) {
            $this->addError('token','Invalid token.');
            return;
        }
        if (!Auth::check()) {
            return redirect()->route('login', ['redirect' => request()->fullUrl()]);
        }

        // Validation: existing team only
        $this->validate([
            'confirm_authority'   => ['accepted'],
            'destination_team_id' => ['required','integer'],
        ], [], [
            'destination_team_id' => 'destination team',
        ]);

        // Rate limit after validation
        $key = 'accept:'.$this->transfer->id.':'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 20)) {
            $this->addError('token','Too many attempts. Try later.');
            return;
        }
        RateLimiter::hit($key, 300);

        // Accept (existing team only)
        $svc->accept($this->transfer, (int) $this->destination_team_id, Auth::id());

        session()->flash('success', 'Transfer accepted. You now own the dog.');
        return redirect()->route('dogs.show', $this->transfer->dog_id);
    }

    public function decline(DogTransferService $svc)
    {
        if (!auth()->check()) {
            return redirect()->route('login', ['redirect' => request()->fullUrl()]);
        }
        $svc->decline($this->transfer, auth()->id());
        session()->flash('success','Transfer declined.');
        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.transfers.accept-transfer', [
            'teams' => $this->teams,
        ])->layout('layouts.app');
    }
}
