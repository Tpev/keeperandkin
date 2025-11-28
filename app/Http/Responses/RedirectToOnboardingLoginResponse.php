<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse;

class RedirectToOnboardingLoginResponse implements LoginResponse
{
    public function toResponse($request)
    {
        $user = $request->user();
        $team = $user?->currentTeam;

        if ($team && is_null($team->setup_type)) {
            return redirect()->route('onboarding.team');
        }

        return redirect()->intended(route('dogs.index'));
    }
}
