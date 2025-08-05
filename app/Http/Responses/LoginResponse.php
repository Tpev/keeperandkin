<?php
// app/Http/Responses/LoginResponse.php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use App\Enums\Role;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        // Decide where to land
        $redirect = match (true) {
            $user->role === Role::ADMIN      => '/admin/dashboard',
            $user->ownsAnyTeam()             => '/shelter/dashboard',
            default                          => '/dashboard',
        };

        // JSON or normal web redirect
        return $request->wantsJson()
            ? new JsonResponse(['redirect' => $redirect], 200)
            : redirect()->intended($redirect);
    }
}
