<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;             // ← extends this
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Enums\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        /* -----------------------------------------------------------------
         | 1)  Create (or fetch) the global admin account
         | ----------------------------------------------------------------- */
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('password'),
                'role'     => Role::ADMIN,
            ]
        );

        /* -----------------------------------------------------------------
         | 2)  Give that admin a shelter/team if they don’t have one yet
         | ----------------------------------------------------------------- */
        if (! $admin->ownedTeams()->exists()) {
            $shelter = $admin->ownedTeams()->create([
                'name'          => 'Main Shelter',
                'personal_team' => false,   // organisation-style, not personal
            ]);

            $admin->current_team_id = $shelter->id;
            $admin->save();
        }
    }
}
