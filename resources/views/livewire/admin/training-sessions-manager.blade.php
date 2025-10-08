<div class="max-w-5xl mx-auto p-6 space-y-6">
    <h1 class="text-xl font-bold">Training Sessions</h1>

    @if (session('success'))
        <div class="p-2 bg-green-50 text-green-700 rounded">{{ session('success') }}</div>
    @endif

    <div class="bg-white border rounded p-4 space-y-3">
        <h2 class="font-semibold">{{ $editId ? 'Edit Session' : 'New Session' }}</h2>

        <div class="grid gap-3 grid-cols-2">
            <div>
                <label class="text-sm">Name</label>
                <input type="text" wire:model.defer="name" class="border rounded w-full px-2 py-1">
                @error('name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm">Video URL</label>
                <input type="text" wire:model.defer="video_url" class="border rounded w-full px-2 py-1">
                @error('video_url') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm">PDF (upload)</label>
                <input type="file" wire:model="pdf_upload" class="border rounded w-full px-2 py-1">
                @error('pdf_upload') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm">Duration (minutes)</label>
                <input type="number" wire:model.defer="duration_minutes" class="border rounded w-full px-2 py-1">
                @error('duration_minutes') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2">
                <label class="text-sm">Description</label>
                <textarea wire:model.defer="description" rows="3" class="border rounded w-full px-2 py-1"></textarea>
            </div>
            <div class="col-span-2">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" wire:model="is_active"> Active
                </label>
            </div>
        </div>

        <div class="flex gap-2 justify-end">
            <button class="px-3 py-1 border rounded" wire:click="$set('editId', null)">Reset</button>
            <button class="px-3 py-1 rounded bg-blue-600 text-white" wire:click="save">Save</button>
        </div>
    </div>

    <div class="bg-white border rounded p-4">
        <h2 class="font-semibold mb-2">All Sessions</h2>
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-2 text-left">Name</th>
                    <th class="p-2 text-left">Active</th>
                    <th class="p-2 text-left">Video</th>
                    <th class="p-2 text-left">PDF</th>
                    <th class="p-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($sessions as $s)
                    <tr>
                        <td class="p-2">{{ $s->name }}</td>
                        <td class="p-2">{{ $s->is_active ? 'Yes' : 'No' }}</td>
                        <td class="p-2">
                            @if($s->video_url)
                                <a href="{{ $s->video_url }}" target="_blank" class="text-blue-600 underline">open</a>
                            @endif
                        </td>
                        <td class="p-2">
                            @if($s->pdf_url)
                                <a href="{{ $s->pdf_url }}" target="_blank" class="text-blue-600 underline">pdf</a>
                            @endif
                        </td>
                        <td class="p-2 text-right">
                            <button class="px-2 py-1 border rounded" wire:click="edit({{ $s->id }})">Edit</button>
                            <button class="ml-1 px-2 py-1 border rounded" wire:click="toggleActive({{ $s->id }})">
                                {{ $s->is_active ? 'Disable' : 'Enable' }}
                            </button>
                            <button class="ml-1 px-2 py-1 border rounded text-red-600" wire:click="delete({{ $s->id }})">Delete</button>
                        </td>
                    </tr>
                @endforeach
                @if($sessions->isEmpty())
                    <tr><td class="p-3 text-sm text-gray-500" colspan="5">No sessions yet.</td></tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
