<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = \App\Models\User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Test Admin',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'is_admin' => true,
            ]
        );

        // Create a personal team if missing
        $team = Team::firstOrCreate(
            ['user_id' => $admin->id, 'personal_team' => true],
            ['name' => "{$admin->name}'s Team"]
        );

        // Set as current team if not set
        if (! $admin->current_team_id) {
            $admin->forceFill(['current_team_id' => $team->id])->save();
        }

        // (Optional) ensure owner appears in pivot and marked as 'owner'
        if (! \DB::table('team_user')->where('team_id', $team->id)->where('user_id', $admin->id)->exists()) {
            \DB::table('team_user')->insert([
                'team_id'    => $team->id,
                'user_id'    => $admin->id,
                'role'       => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
