<div class="max-w-6xl mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold">Training Flags</h1>
        <a href="{{ route('admin.training.sessions') }}" class="px-3 py-2 rounded border">Manage Sessions</a>
    </div>

    @if (session('success'))
        <div class="p-2 bg-green-50 text-green-700 rounded">{{ session('success') }}</div>
    @endif

    <div class="bg-white border rounded p-4 space-y-3">
        <h2 class="font-semibold">{{ $editId ? 'Edit Flag' : 'New Flag' }}</h2>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-sm">Name</label>
                <input type="text" wire:model.defer="name" class="border rounded w-full px-2 py-1">
                @error('name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-end">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" wire:model="is_active"> Active
                </label>
            </div>
            <div class="col-span-2">
                <label class="text-sm">Description</label>
                <textarea wire:model.defer="description" rows="2" class="border rounded w-full px-2 py-1"></textarea>
            </div>
        </div>
        <div class="flex gap-2 justify-end">
            <button class="px-3 py-1 border rounded" wire:click="$set('editId', null)">Reset</button>
            <button class="px-3 py-1 rounded bg-blue-600 text-white" wire:click="save">Save</button>
        </div>
    </div>

    <div class="bg-white border rounded p-4">
        <h2 class="font-semibold mb-3">All Flags</h2>
        <div class="grid md:grid-cols-2 gap-4">
            @foreach($flags as $f)
                <div class="border rounded p-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-semibold">{{ $f->name }}</div>
                            <div class="text-xs text-gray-500">slug: {{ $f->slug }} · Sessions: {{ $f->sessions_count }} · Options: {{ $f->answer_options_count }}</div>
                        </div>
                        <div class="flex gap-2">
                            <button class="px-2 py-1 border rounded" wire:click="edit({{ $f->id }})">Edit</button>
                            <button class="px-2 py-1 border rounded text-red-600" wire:click="delete({{ $f->id }})">Delete</button>
                        </div>
                    </div>

                    @if($selectedFlag && $selectedFlag->id === $f->id)
                        <div class="mt-3 border-t pt-3">
                            <h3 class="font-medium mb-2">Sessions</h3>
                            <div class="flex items-end gap-2 mb-2">
                                <select class="border rounded px-2 py-1" wire:model="sessionToAttach">
                                    <option value="">— choose session —</option>
                                    @foreach($sessions as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                                <button class="px-2 py-1 border rounded" wire:click="attachSession" @disabled(!$sessionToAttach)>Attach</button>
                            </div>

                            <div class="space-y-2">
                                @forelse($selectedFlag->sessions as $s)
                                    <div class="flex items-center justify-between border rounded p-2">
                                        <div>{{ $s->name }}</div>
                                        <div class="flex gap-2">
                                            <button class="px-2 py-1 border rounded" wire:click="moveSession({{ $s->id }}, 'up')">Up</button>
                                            <button class="px-2 py-1 border rounded" wire:click="moveSession({{ $s->id }}, 'down')">Down</button>
                                            <button class="px-2 py-1 border rounded text-red-600" wire:click="detachSession({{ $s->id }})">Detach</button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-sm text-gray-500">No sessions yet.</div>
                                @endforelse
                            </div>

                            <h3 class="font-medium mt-4 mb-2">Attach Answer Options</h3>
                            <div class="flex items-end gap-2 mb-2">
                                <input type="text" placeholder="Search options…" wire:model.debounce.400ms="searchOptions" class="border rounded px-2 py-1">
                                <select class="border rounded px-2 py-1" wire:model="optionToAttach">
                                    <option value="">— pick —</option>
                                    @foreach($options as $o)
                                        <option value="{{ $o->id }}">#{{ $o->id }} — {{ \Illuminate\Support\Str::limit($o->label, 50) }}</option>
                                    @endforeach
                                </select>
                                <button class="px-2 py-1 border rounded" wire:click="attachOption" @disabled(!$optionToAttach)>Attach</button>
                            </div>

                            <div class="space-y-1">
                                @forelse($selectedFlag->answerOptions as $o)
                                    <div class="flex items-center justify-between border rounded p-2">
                                        <div>#{{ $o->id }} — {{ $o->label }}</div>
                                        <button class="px-2 py-1 border rounded text-red-600" wire:click="detachOption({{ $o->id }})">Detach</button>
                                    </div>
                                @empty
                                    <div class="text-sm text-gray-500">No options attached.</div>
                                @endforelse
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
