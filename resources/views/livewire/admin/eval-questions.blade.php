<div class="max-w-7xl mx-auto px-4 py-8 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Admin — Evaluation Question Parameters</h1>
            <p class="text-sm text-slate-500">Configure flags, training category, and weights.</p>
        </div>
        <div class="flex items-center gap-2">
            <x-ts-input
                placeholder="Search questions…"
                icon="magnifying-glass"
                wire:model.live.debounce.300ms="search"
            />
        </div>
    </div>

    @if (session('success'))
        <x-ts-alert icon="check-circle" title="Saved">{{ session('success') }}</x-ts-alert>
    @endif

    <x-ts-card class="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-2 text-left">Category</th>
                        <th class="px-4 py-2 text-left">Question Key</th>
                        <th class="px-4 py-2 text-left">Text</th>
                        <th class="px-4 py-2 text-left">Type</th>
                        <th class="px-4 py-2 text-left w-28">Weight</th>
                        <th class="px-4 py-2 text-left w-48">Training Category</th>
                        <th class="px-4 py-2 text-left w-64">Flags (comma)</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $row)
                    <tr class="border-b align-top">
                        <td class="px-4 py-2">{{ $row['category_key'] }}</td>
                        <td class="px-4 py-2 font-mono">{{ $row['question_key'] }}</td>
                        <td class="px-4 py-2">{{ $row['text'] }}</td>
                        <td class="px-4 py-2">{{ $row['type'] }}</td>

                        <td class="px-4 py-2">
                            <input type="number" min="0" step="1"
                                   class="w-20 ts-input"
                                   wire:model.lazy="form.{{ $row['question_key'] }}.weight"
                                   wire:change="saveRow('{{ $row['question_key'] }}')" />
                        </td>

                        <td class="px-4 py-2">
                            <input type="text" class="w-48 ts-input"
                                   placeholder="e.g. handling, social"
                                   wire:model.lazy="form.{{ $row['question_key'] }}.training_category"
                                   wire:change="saveRow('{{ $row['question_key'] }}')" />
                        </td>

                        <td class="px-4 py-2">
                            <input type="text" class="w-64 ts-input"
                                   placeholder="red_flag,safety,needs_supervision"
                                   wire:model.lazy="form.{{ $row['question_key'] }}.flags_str"
                                   wire:change="saveRow('{{ $row['question_key'] }}')" />
                        </td>

                        <td class="px-4 py-2 text-right">
                            <x-ts-button size="sm" wire:click="saveRow('{{ $row['question_key'] }}')">
                                Save
                            </x-ts-button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-6 text-center text-slate-500">No questions found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t">
            {{ $params->links() }}
        </div>
    </x-ts-card>
</div>
