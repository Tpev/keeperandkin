<?php

namespace App\Actions\Fortify;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Events\TeamMemberAdded;
use Laravel\Jetstream\Features;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\TeamInvitation;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms'    => Jetstream::hasTermsAndPrivacyPolicyFeature()
                ? ['accepted', 'required']
                : '',
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name'     => $input['name'],
                'email'    => $input['email'],
                'password' => Hash::make($input['password']),
            ]);

            // Find any pending team invitations for this email
            $invitations = TeamInvitation::where('email', $input['email'])->get();

            if (Features::hasTeamFeatures() && $invitations->isNotEmpty()) {
                // Auto-accept invitations: attach user to team(s), switch to the first, then delete invites
                foreach ($invitations as $i => $invite) {
                    $invite->team->users()->attach($user, ['role' => $invite->role]);
                    TeamMemberAdded::dispatch($invite->team, $user);

                    if ($i === 0) {
                        $user->switchTeam($invite->team); // sets current_team_id
                    }

                    $invite->delete();
                }
            } else {
                // Normal signup: create a personal team (unchanged behavior)
                if (Features::hasTeamFeatures()) {
                    $this->createTeam($user);
                }
            }

            return $user;
        });
    }

    /**
     * Create a personal team for the user.
     */
    protected function createTeam(User $user): void
    {
        $user->ownedTeams()->save(Team::forceCreate([
            'user_id'       => $user->id,
            'name'          => explode(' ', $user->name, 2)[0]."'s Team",
            'personal_team' => true,
        ]));
    }
}
