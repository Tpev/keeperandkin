<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTeamSetup
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) return $next($request);

        $team = $user->currentTeam;
        if ($team && !$team->setup_type && !$request->routeIs('onboarding.team')) {
            return redirect()->route('onboarding.team');
        }

        return $next($request);
    }
}
