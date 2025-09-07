<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SystemBrowser extends Component
{
    use WithPagination;

    #[Url] public string $tab = 'users';   // users|teams|memberships
    #[Url] public string $search = '';

    public int $perPage = 15; // fixed; no UI control

    public function updatingSearch() { $this->resetPage(); }
    public function updatingTab()    { $this->resetPage(); }

    public function render()
    {
        $search = trim($this->search);
        $users = $teams = $memberships = null;

        if ($this->tab === 'users') {
            $users = User::query()
                ->when($search, fn($q) =>
                    $q->where(function($qq) use ($search) {
                        $qq->where('name', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
                    })
                )
                ->withCount(['ownedTeams'])
                ->orderBy('id')
                ->paginate($this->perPage);

        } elseif ($this->tab === 'teams') {
            $teams = Team::query()
                ->when($search, fn($q) =>
                    $q->where(function($qq) use ($search) {
                        $qq->where('name', 'like', "%{$search}%")
                           ->orWhere('id', 'like', "%{$search}%");
                    })
                )
                ->with('owner:id,name,email')
                ->orderBy('id')
                ->paginate($this->perPage);

                } else { // memberships
            // Build a UNION:
            //  1) All members from team_user
            //  2) Owners for teams that don't already appear in team_user as members (avoid duplicates)

            $membersQuery = DB::table('team_user as tu')
                ->join('teams as t', 't.id', '=', 'tu.team_id')
                ->join('users as u', 'u.id', '=', 'tu.user_id')
                ->select([
                    'tu.team_id',
                    't.name as team_name',
                    'tu.user_id',
                    'u.name as user_name',
                    'u.email',
                    'tu.role',
                    'tu.created_at',
                ]);

            $ownersQuery = DB::table('teams as t')
                ->join('users as u', 'u.id', '=', 't.user_id') // owner column on teams
                ->leftJoin('team_user as tu', function ($join) {
                    $join->on('tu.team_id', '=', 't.id')
                        ->on('tu.user_id', '=', 't.user_id');
                })
                ->whereNull('tu.user_id') // owner not already present in team_user (avoid dup)
                ->select([
                    't.id as team_id',
                    't.name as team_name',
                    'u.id as user_id',
                    'u.name as user_name',
                    'u.email',
                    DB::raw("'owner' as role"),
                    // fallback to team's created_at as the "joined" date for owners
                    't.created_at',
                ]);

            $union = $membersQuery->unionAll($ownersQuery);

            // Wrap union in a subquery so we can search + paginate
            $wrapped = DB::query()->fromSub($union, 'm');

            if ($search) {
                $like = "%{$search}%";
                $wrapped->where(function ($q) use ($like) {
                    $q->where('team_name', 'like', $like)
                      ->orWhere('user_name', 'like', $like)
                      ->orWhere('email', 'like', $like)
                      ->orWhere('role', 'like', $like);
                });
            }

            $memberships = $wrapped
                ->orderByDesc('created_at')
                ->paginate($this->perPage);
        }

        return view('livewire.admin.system-browser', compact('users', 'teams', 'memberships'))->layout('layouts.app');
    }
}
