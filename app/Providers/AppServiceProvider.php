<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Enums\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
