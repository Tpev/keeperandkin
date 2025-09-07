<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Contracts\Auth\MustVerifyEmail; // keep if you plan to verify
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /* -----------------------------------------------------------------
     |  Mass-assignable attributes
     | ----------------------------------------------------------------- */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',            // 👈 NEW
    ];

    /* -----------------------------------------------------------------
     |  Hidden on arrays / JSON
     | ----------------------------------------------------------------- */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /* -----------------------------------------------------------------
     |  Accessors appended to model array form
     | ----------------------------------------------------------------- */
    protected $appends = [
        'profile_photo_url',
    ];

    /* -----------------------------------------------------------------
     |  Attribute casting
     | ----------------------------------------------------------------- */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'role'              => Role::class,   // 👈 NEW (enum cast)
			 'is_admin' => 'boolean',
        ];
    }

    /* -----------------------------------------------------------------
     |  Convenience helpers
     | ----------------------------------------------------------------- */
    public function isAdmin(): bool
    {
        return $this->role === Role::ADMIN;
    }

    public function isShelterAdmin(): bool
    {
        return in_array($this->role, [Role::ADMIN, Role::SHELTER_ADMIN], true);
    }

    public function isUser(): bool
    {
        return $this->role === Role::USER;
    }
}
