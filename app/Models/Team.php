<?php

namespace App\Models;

use App\Enums\TeamRole;
use App\Enums\TeamSetupType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;

class Team extends JetstreamTeam
{
    /** @use HasFactory<\Database\Factories\TeamFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'personal_team',
        'setup_type', // added for onboarding
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'personal_team' => 'boolean',
            'setup_type'    => TeamSetupType::class, // enum cast
        ];
    }

    /**
     * Domain relations
     */
    public function dogs(): HasMany
    {
        return $this->hasMany(Dog::class);
    }

    /**
     * Has this team finished onboarding?
     */
    public function isSetupComplete(): bool
    {
        return !is_null($this->setup_type);
    }

    /**
     * Assign the registering user's initial role based on the team's setup_type.
     * Expects that the user is already attached to the team (Jetstream does this).
     */
    public function assignInitialAdminRoleForUser(User $user): void
    {
        if (!$this->setup_type instanceof TeamSetupType) {
            return;
        }

        $role = match ($this->setup_type) {
            TeamSetupType::SANCTUARY_RESCUE => TeamRole::RESCUE_ADMIN,
            TeamSetupType::FOSTER_RESCUE    => TeamRole::FOSTER_ADMIN,
            TeamSetupType::ADOPTER          => TeamRole::ADOPTER,
        };

        // Jetstream uses team_user pivot with a 'role' column by default.
        $this->users()->updateExistingPivot($user->id, ['role' => $role->value]);
    }

    /**
     * (Optional) Which roles are allowed to be invited for this team?
     * Useful to filter role options in your "Invite member" UI.
     *
     * @return array<int, string>
     */
    public function allowedInviteRoles(): array
    {
        if (!$this->setup_type instanceof TeamSetupType) {
            return [];
        }

        return match ($this->setup_type) {
            TeamSetupType::SANCTUARY_RESCUE => [
                TeamRole::RESCUE_STAFF->value,
                TeamRole::RESCUE_VOLUNTEER->value,
            ],
            TeamSetupType::FOSTER_RESCUE => [
                TeamRole::FOSTER_STAFF->value,
                TeamRole::FOSTER_FOSTER->value,
            ],
            TeamSetupType::ADOPTER => [
                // typically no additional roles
            ],
        };
    }
}
