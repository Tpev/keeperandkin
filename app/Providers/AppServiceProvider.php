<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Enums\Role;

// Fortify response bindings for onboarding redirect
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Contracts\LoginResponse;
use App\Http\Responses\RedirectToOnboardingRegisterResponse;
use App\Http\Responses\RedirectToOnboardingLoginResponse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ────── Redirect new users to onboarding ──────
        $this->app->singleton(RegisterResponse::class, RedirectToOnboardingRegisterResponse::class);
        $this->app->singleton(LoginResponse::class, RedirectToOnboardingLoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ────── Role-based Gates ──────
        Gate::define('admin-only', fn (User $user) =>
            $user->role === Role::ADMIN
        );

        Gate::define('shelter-admin', fn (User $user) =>
            in_array($user->role, [Role::ADMIN, Role::SHELTER_ADMIN], true)
        );
    }
}
