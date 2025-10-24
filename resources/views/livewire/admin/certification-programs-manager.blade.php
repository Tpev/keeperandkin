<div class="max-w-6xl mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold">Certification Programs</h1>
        <a href="{{ route('admin.training.sessions') }}" class="px-3 py-2 rounded border">Manage Sessions</a>
    </div>

    @if (session('success'))
        <div class="p-2 bg-green-50 text-green-700 rounded">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="p-2 bg-red-50 text-red-700 rounded">{{ session('error') }}</div>
    @endif

    {{-- CREATE / EDIT --}}
    <div class="bg-white border rounded p-4 space-y-3">
        <h2 class="font-semibold">{{ $editId ? 'Edit Program' : 'New Program' }}</h2>

        <div class="grid md:grid-cols-2 gap-3">
            <div>
                <label class="text-sm">Title</label>
                <input type="text" class="border rounded w-full px-2 py-1" wire:model.defer="title">
                @error('title') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-end gap-4">
                <div class="flex-1">
                    <label class="text-sm block">Difficulty (optional)</label>
                    <select class="border rounded w-full px-2 py-1" wire:model.defer="difficulty">
                        <option value="">—</option>
                        @foreach($availableDifficulties as $d)
                            <option value="{{ $d }}">{{ ucfirst($d) }}</option>
                        @endforeach
                    </select>
                    @error('difficulty') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" wire:model="is_active"> Active
                </label>
            </div>

            <div class="md:col-span-2">
                <label class="text-sm">Description</label>
                <textarea rows="2" class="border rounded w-full px-2 py-1" wire:model.defer="description"></textarea>
            </div>

            <div>
                <label class="text-sm block">Visibility</label>
                <select class="border rounded w-full px-2 py-1" wire:model.defer="visibility_mode">
                    <option value="public">Public</option>
                    <option value="role_gated">Role-gated</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">
                    Role gating is prepared here; enforcement comes in user flows later.
                </p>
            </div>

            <div>
                <label class="text-sm block">Required roles (CSV, optional)</label>
                <input type="text" class="border rounded w-full px-2 py-1" wire:model.defer="required_roles_csv" placeholder="trainer, coordinator">
                <p class="text-xs text-gray-500 mt-1">Example: <code>trainer, coordinator</code></p>
            </div>
        </div>

        <div class="flex gap-2 justify-end">
            <button class="px-3 py-1 border rounded" wire:click="$set('editId', null)">Reset</button>
            <button class="px-3 py-1 rounded bg-blue-600 text-white" wire:click="save">Save</button>
        </div>
    </div>

    {{-- LIST + ANALYTICS --}}
    <div class="bg-white border rounded p-4">
        <h2 class="font-semibold mb-3">All Programs</h2>

        <div class="grid md:grid-cols-2 gap-4">
            @foreach($programs as $p)
                <div class="border rounded p-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-semibold flex items-center gap-2">
                                {{ $p->title }}
                                @if(!$p->is_active)
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100">Inactive</span>
                                @endif
                                @if($p->difficulty)
                                    <span class="text-xs px-2 py-0.5 rounded-full border">{{ ucfirst($p->difficulty) }}</span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-500">
                                slug: {{ $p->slug }} · Flags: {{ $p->flags_count }}
                                · Visibility: {{ $p->visibility_mode }}
                            </div>
                            <div class="mt-1 text-xs">
                                {{-- Light analytics (calls model helper methods) --}}
                                Enrolled: {{ $p->countEnrolled() }} · In progress: {{ $p->countInProgress() }} · Completed: {{ $p->countCompleted() }}
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button class="px-2 py-1 border rounded" wire:click="edit({{ $p->id }})">Edit</button>
                            <button class="px-2 py-1 border rounded text-red-600" wire:click="delete({{ $p->id }})">Delete</button>
                        </div>
                    </div>

                    @if($selectedProgram && $selectedProgram->id === $p->id)
                        <div class="mt-3 border-t pt-3">
                            {{-- ATTACH FLAGS (people only) --}}
                            <h3 class="font-medium mb-2">Attach People Flags</h3>
                            <div class="flex items-end gap-2 mb-2">
                                <input type="text" class="border rounded px-2 py-1" placeholder="Search people flags…" wire:model.debounce.400ms="flagSearch">
                                <select class="border rounded px-2 py-1" wire:model="flagToAttach">
                                    <option value="">— choose flag —</option>
                                    @foreach($attachableFlags as $f)
                                        <option value="{{ $f->id }}">{{ $f->name }}</option>
                                    @endforeach
                                </select>
                                <button class="px-2 py-1 border rounded" wire:click="attachFlag" @disabled(!$flagToAttach)">Attach</button>
                            </div>

                            {{-- ORDERED FLAGS --}}
                            <div class="space-y-2">
                                @forelse($selectedProgram->flags as $f)
                                    <div class="flex items-center justify-between border rounded p-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs px-2 py-0.5 rounded-full border">People</span>
                                            <span class="font-medium">{{ $f->name }}</span>
                                        </div>
                                        <div class="flex gap-2">
                                            <button class="px-2 py-1 border rounded" wire:click="moveFlag({{ $f->id }}, 'up')">Up</button>
                                            <button class="px-2 py-1 border rounded" wire:click="moveFlag({{ $f->id }}, 'down')">Down</button>
                                            <button class="px-2 py-1 border rounded text-red-600" wire:click="detachFlag({{ $f->id }})">Detach</button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-sm text-gray-500">No flags yet. Attach people-audience flags above.</div>
                                @endforelse
                            </div>

                            {{-- PREVIEW (prep) --}}
                            <div class="mt-4 text-xs text-gray-600">
                                <strong>Preview as user:</strong> Visibility gating isn’t enforced yet; this is a Phase 3–4 feature.
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
