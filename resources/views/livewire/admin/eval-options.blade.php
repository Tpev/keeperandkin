<div class="max-w-7xl mx-auto px-4 py-8 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Admin — Evaluation Option Parameters</h1>
            <p class="text-sm text-slate-500">Configure flags, training tags & red flags per answer.</p>
        </div>
        <div class="flex items-center gap-2">
            {{-- keep your TallStack input if you want, or swap to native --}}
            <x-ts-input
                placeholder="Search… (category, question, option)"
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
                        <th class="px-4 py-2 text-left">Question</th>
                        <th class="px-4 py-2 text-left">Option Key</th>
                        <th class="px-4 py-2 text-left">Option Label</th>
                        <th class="px-4 py-2 text-left w-24">Weight</th>
                        <th class="px-4 py-2 text-left w-64">Training Tags</th>
                        <th class="px-4 py-2 text-left w-64">Red Flags</th>
                        <th class="px-4 py-2 w-28"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $row)
                    <tr class="border-b align-top">
                        <td class="px-4 py-2">{{ $row['category_key'] }}</td>
                        <td class="px-4 py-2">
                            <div class="font-mono">{{ $row['question_key'] }}</div>
                            <div class="text-xs text-slate-500">{{ $row['text'] }}</div>
                        </td>
                        <td class="px-4 py-2 font-mono">{{ $row['option_key'] }}</td>
                        <td class="px-4 py-2">{{ $row['label'] }}</td>

                        {{-- Weight --}}
                        <td class="px-4 py-2">
                            <input type="number" min="0" step="1"
                                   class="w-20 block rounded-md border border-gray-300 bg-white p-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   wire:model.lazy="form.{{ $row['compound'] }}.weight"
                                   wire:change="saveRow('{{ $row['question_key'] }}','{{ $row['option_key'] }}')" />
                        </td>

                        {{-- Training tags (native multi-select) --}}
                        <td class="px-4 py-2">
                            <select multiple size="5"
                                    class="w-64 block rounded-md border border-gray-300 bg-white p-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    wire:model.live="form.{{ $row['compound'] }}.training_tags"
                                    wire:change="saveRow('{{ $row['question_key'] }}','{{ $row['option_key'] }}')">
                                @foreach($trainingOptions as $opt)
                                    <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                @endforeach
                            </select>

                            {{-- chips preview (optional) --}}
                            @php $sel = $form[$row['compound']]['training_tags'] ?? []; @endphp
                            @if(!empty($sel))
                                <div class="mt-1 flex flex-wrap gap-1">
                                    @foreach($sel as $tag)
                                        <span class="inline-flex items-center rounded bg-blue-50 px-2 py-0.5 text-xs text-blue-700">
                                            {{ str_replace('_',' ', ucfirst($tag)) }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </td>

                        {{-- Red flags (native multi-select) --}}
                        <td class="px-4 py-2">
                            <select multiple size="5"
                                    class="w-64 block rounded-md border border-gray-300 bg-white p-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                                    wire:model.live="form.{{ $row['compound'] }}.red_flags"
                                    wire:change="saveRow('{{ $row['question_key'] }}','{{ $row['option_key'] }}')">
                                @foreach($redFlagOptions as $opt)
                                    <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                @endforeach
                            </select>

                            {{-- chips preview (optional) --}}
                            @php $rf = $form[$row['compound']]['red_flags'] ?? []; @endphp
                            @if(!empty($rf))
                                <div class="mt-1 flex flex-wrap gap-1">
                                    @foreach($rf as $tag)
                                        <span class="inline-flex items-center rounded bg-rose-50 px-2 py-0.5 text-xs text-rose-700">
                                            {{ str_replace('_',' ', ucfirst($tag)) }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </td>

                        <td class="px-4 py-2 text-right">
                            <x-ts-button size="sm"
                                wire:click="saveRow('{{ $row['question_key'] }}','{{ $row['option_key'] }}')">
                                Save
                            </x-ts-button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-6 text-center text-slate-500">No options found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t">
            {{ $params->links() }}
        </div>
    </x-ts-card>
</div>
