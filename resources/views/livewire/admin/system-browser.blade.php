<div class="max-w-7xl mx-auto px-4 py-8 space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Admin — System Browser</h1>
            <p class="text-sm text-slate-500">Browse users, teams, and memberships.</p>
        </div>
        <div class="flex items-center gap-2">
            <x-ts-input
                placeholder="Search…"
                icon="magnifying-glass"
                wire:model.live.debounce.300ms="search"
            />
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex items-center gap-2">
        @php $tabs = ['users'=>'Users', 'teams'=>'Teams', 'memberships'=>'Memberships']; @endphp
        @foreach($tabs as $k=>$label)
            <x-ts-button
                size="sm"
                variant="{{ $tab === $k ? 'primary' : 'secondary' }}"
                wire:click="$set('tab','{{ $k }}')"
            >{{ $label }}</x-ts-button>
        @endforeach
    </div>

    {{-- Content --}}
    <x-ts-card class="p-0 overflow-hidden">
        @if($tab === 'users')
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-4 py-2 text-left">ID</th>
                            <th class="px-4 py-2 text-left">Name</th>
                            <th class="px-4 py-2 text-left">Email</th>
                            <th class="px-4 py-2 text-left">Admin</th>
                            <th class="px-4 py-2 text-left">Owned Teams</th>
                            <th class="px-4 py-2 text-left">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $u)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $u->id }}</td>
                            <td class="px-4 py-2">{{ $u->name }}</td>
                            <td class="px-4 py-2">{{ $u->email }}</td>
                            <td class="px-4 py-2">
                                @if($u->is_admin)
                                    <x-ts-badge color="green">Yes</x-ts-badge>
                                @else
                                    <x-ts-badge color="slate">No</x-ts-badge>
                                @endif
                            </td>
                            <td class="px-4 py-2">{{ $u->owned_teams_count }}</td>
                            <td class="px-4 py-2">{{ $u->created_at?->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $users->links() }}</div>

        @elseif($tab === 'teams')
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-4 py-2 text-left">ID</th>
                            <th class="px-4 py-2 text-left">Name</th>
                            <th class="px-4 py-2 text-left">Owner</th>
                            <th class="px-4 py-2 text-left">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($teams as $t)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $t->id }}</td>
                            <td class="px-4 py-2">{{ $t->name }}</td>
                            <td class="px-4 py-2">
                                {{ $t->owner?->name }}
                                <span class="text-slate-400">({{ $t->owner?->email }})</span>
                            </td>
                            <td class="px-4 py-2">{{ $t->created_at?->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $teams->links() }}</div>

        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-4 py-2 text-left">Team</th>
                            <th class="px-4 py-2 text-left">User</th>
                            <th class="px-4 py-2 text-left">Email</th>
                            <th class="px-4 py-2 text-left">Role</th>
                            <th class="px-4 py-2 text-left">Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($memberships as $m)
                        <tr class="border-b">
                            <td class="px-4 py-2">#{{ $m->team_id }} — {{ $m->team_name }}</td>
                            <td class="px-4 py-2">#{{ $m->user_id }} — {{ $m->user_name }}</td>
                            <td class="px-4 py-2">{{ $m->email }}</td>
                            <td class="px-4 py-2">
                                <x-ts-badge color="{{ $m->role ? 'indigo' : 'slate' }}">{{ $m->role ?? 'member' }}</x-ts-badge>
                            </td>
                            <td class="px-4 py-2">{{ \Illuminate\Support\Carbon::parse($m->created_at)->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $memberships->links() }}</div>
        @endif
    </x-ts-card>
</div>
