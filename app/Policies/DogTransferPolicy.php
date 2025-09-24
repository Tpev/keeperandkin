<?php

namespace App\Policies;

use App\Models\Dog;
use App\Models\DogTransfer;
use App\Models\User;

class DogTransferPolicy
{
    // Initiator must belong to dog's current team
    public function create(User $user, Dog $dog): bool
    {
        return $user->currentTeam && $user->currentTeam->id === $dog->team_id;
    }

    // Cancel allowed if initiator or same team admin
    public function cancel(User $user, DogTransfer $transfer): bool
    {
        return $user->id === $transfer->initiator_user_id
            || ($user->currentTeam && $user->currentTeam->id === $transfer->from_team_id);
    }

    // Accept: user logged in and email matches invite (or same inbox) – we finalize at Accept screen
    public function accept(User $user, DogTransfer $transfer): bool
    {
        return $transfer->isPending() && !$transfer->isExpired();
    }

    public function decline(User $user, DogTransfer $transfer): bool
    {
        return $transfer->isPending() && !$transfer->isExpired();
    }
}
