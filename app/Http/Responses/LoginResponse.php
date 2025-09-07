<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Where to send the user after login.
     */
    public function toResponse($request)
    {
        // Example: redirect to dogs.index
        return redirect()->intended(route('dogs.index'));
    }
}
