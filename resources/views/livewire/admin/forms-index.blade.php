<div class="max-w-6xl mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Evaluation Forms</h1>
        <button wire:click="openCreate" class="px-4 py-2 rounded bg-blue-600 text-white shadow">New Form</button>
    </div>

    <div class="flex gap-3">
        <input type="text" wire:model.debounce.400ms="search" placeholder="Search forms…" class="border rounded px-3 py-2 w-72">
    </div>

    @if (session('success'))
        <div class="p-3 rounded bg-green-50 text-green-700">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="p-3 rounded bg-red-50 text-red-700">{{ session('error') }}</div>
    @endif

    <div class="overflow-x-auto bg-white rounded border">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left p-2">Name</th>
                    <th class="text-left p-2">Slug</th>
                    <th class="text-left p-2">Version</th>
                    <th class="text-left p-2">Scope</th>
                    <th class="text-left p-2">Active</th>
                    <th class="text-left p-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($forms as $f)
                    <tr>
                        <td class="p-2 font-medium">{{ $f->name }}</td>
                        <td class="p-2">{{ $f->slug }}</td>
                        <td class="p-2">v{{ $f->version }}</td>
                        <td class="p-2">{{ $f->team_id ? 'Team '.$f->team_id : 'Global' }}</td>
                        <td class="p-2">
                            @if($f->is_active)
                                <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-700">Active</span>
                            @else
                                <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-700">Draft</span>
                            @endif
                        </td>
                        <td class="p-2">
                            <div class="flex gap-2">
                                <a href="{{ route('admin.forms.edit', $f) }}" class="px-2 py-1 rounded border">Edit</a>
                                <button wire:click="cloneAsDraft({{ $f->id }})" class="px-2 py-1 rounded border">Clone as Draft</button>
                                @if(!$f->is_active)
                                    <button wire:click="publish({{ $f->id }})" class="px-2 py-1 rounded bg-blue-600 text-white">Publish</button>
                                @else
                                    <button wire:click="unpublish({{ $f->id }})" class="px-2 py-1 rounded border">Unpublish</button>
                                @endif
                                <button wire:click="deleteForm({{ $f->id }})" class="px-2 py-1 rounded border text-red-600">Delete</button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-3">
            {{ $forms->links() }}
        </div>
    </div>

    {{-- Create modal --}}
    @if($createModal)
        <div class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-[560px] shadow-xl">
                <h2 class="text-lg font-semibold mb-4">Create Form</h2>

                <div class="space-y-3">
                    <div>
                        <label class="text-sm">Name</label>
                        <input type="text" wire:model="name" class="border rounded w-full px-3 py-2">
                        @error('name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm">Slug</label>
                        <input type="text" wire:model="slug" class="border rounded w-full px-3 py-2">
                        @error('slug') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model="is_active" id="is_active">
                        <label for="is_active" class="text-sm">Publish immediately</label>
                    </div>
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <button class="px-3 py-2 rounded border" wire:click="$set('createModal', false)">Cancel</button>
                    <button class="px-3 py-2 rounded bg-blue-600 text-white" wire:click="createForm">Create</button>
                </div>
            </div>
        </div>
    @endif
</div>
