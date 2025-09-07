{{-- resources/views/livewire/dogs/vet-corner.blade.php --}}
@php
    $KK_NAVY     = '#03314C';
    $KK_BLUE     = '#076BA8';
    $KK_BLUE_ALT = '#DAEEFF';
    $KK_DIVIDER  = '#E2E8F0';

    $weightChip  = number_format($current_weight ?? ($dog->vetMetric->current_weight ?? 0), 1);
    $bcsChip     = $bcs ?? ($dog->vetMetric->bcs ?? null);
    $nextVac     = $next_vaccine_date ?? optional($dog->vetMetric?->next_vaccine_date)->format('Y-m-d');
@endphp

<section class="max-w-7xl mx-auto mt-16 rounded-3xl ring-1 ring-black/5 p-8 shadow-lg" style="background:#FFF7ED;">
    <h2 class="text-xl font-bold mb-6">Vet Corner</h2>

    {{-- Summary chips --}}
    <div class="flex flex-wrap gap-4 mb-6">
        <span class="inline-flex items-center gap-2 text-xs font-medium text-white px-3 py-1 rounded-full" style="background: {{ $KK_BLUE }};">
            {{-- icon --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422A12.083 12.083 0 0119 13.714L12 21l-7-7.286a12.083 12.083 0 01.84-3.136L12 14z"/></svg>
            {{ $weightChip > 0 ? $weightChip.' kg' : '— kg' }}
        </span>
        <span class="inline-flex items-center gap-2 text-xs font-medium text-white px-3 py-1 rounded-full" style="background:#7C3AED;">
            BCS {{ $bcsChip ? $bcsChip.'/9' : '—/9' }}
        </span>
        <span class="inline-flex items-center gap-2 text-xs font-medium text-white px-3 py-1 rounded-full" style="background:#16A34A;">
            Next vaccine {{ $nextVac ? \Carbon\Carbon::parse($nextVac)->format('M d') : '—' }}
        </span>
    </div>

    {{-- Metrics form --}}
    <div class="rounded-2xl ring-1 ring-black/5 p-6 mb-8" style="background:#fff;">
        <h3 class="font-semibold mb-4">Update metrics</h3>
        <form wire:submit.prevent="saveMetrics" class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <x-ts-input label="Current weight (kg)" type="number" step="0.1" min="0" wire:model.defer="current_weight" />
            <x-ts-input label="BCS (1–9)" type="number" min="1" max="9" wire:model.defer="bcs" />
            <x-ts-input label="Next vaccine date" type="date" wire:model.defer="next_vaccine_date" />
            <div class="sm:col-span-3 flex justify-end">
                <x-ts-button type="submit" class="rounded-xl shadow" style="background: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }};">
                    Save metrics
                </x-ts-button>
            </div>
        </form>
    </div>

    {{-- Visits --}}
    <div class="text-center">
        <button x-data @click="$dispatch('toggle-vet-visits')" class="font-semibold" style="color: {{ $KK_BLUE }};">
            {{ $showVisits ? 'Hide vet visits' : 'Show vet visits' }}
        </button>
    </div>

    <div
        x-data="{ open: @entangle('showVisits') }"
        x-on:toggle-vet-visits.window="open = !open"
        x-show="open" x-cloak
        class="mt-6">

        {{-- Add visit form --}}
        <div class="rounded-2xl ring-1 ring-black/5 p-6 mb-6" style="background:#fff;">
            <h3 class="font-semibold mb-4">Add a visit</h3>
            <form wire:submit.prevent="addVisit" class="grid grid-cols-1 sm:grid-cols-4 gap-5">
                <x-ts-input label="Date" type="date" wire:model.defer="visit_date" />
                <x-ts-input label="Reason" wire:model.defer="reason" />
                <x-ts-input label="Weight (kg)" type="number" step="0.1" min="0" wire:model.defer="visit_weight" />
                <div class="sm:col-span-4">
                    <x-ts-textarea label="Outcome / Notes" rows="3" wire:model.defer="outcome" />
                </div>
                <div class="sm:col-span-4 flex justify-end">
                    <x-ts-button type="submit" class="rounded-xl shadow" style="background: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }};">
                        Add visit
                    </x-ts-button>
                </div>
            </form>
        </div>

        {{-- Visits table --}}
        <div class="overflow-x-auto rounded-lg shadow">
            <table class="min-w-full text-sm divide-y" style="background: rgba(255,255,255,.95); border-color: {{ $KK_DIVIDER }};">
                <thead style="background: {{ $KK_BLUE_ALT }};">
                    <tr>
                        <th class="py-2 px-3 text-left">Date</th>
                        <th class="py-2 px-3 text-left">Reason</th>
                        <th class="py-2 px-3 text-left">Outcome</th>
                        <th class="py-2 px-3 text-left">Weight</th>
                        <th class="py-2 px-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: {{ $KK_DIVIDER }};">
                    @forelse($dog->vetVisits as $v)
                        <tr>
                            <td class="py-2 px-3 font-medium">{{ $v->visit_date->format('M d, Y') }}</td>
                            <td class="py-2 px-3">{{ $v->reason }}</td>
                            <td class="py-2 px-3">{{ $v->outcome }}</td>
                            <td class="py-2 px-3">{{ $v->weight ? number_format($v->weight,1) : '—' }}</td>
                            <td class="py-2 px-3 text-right">
                                <x-ts-button wire:click="deleteVisit({{ $v->id }})" size="sm" variant="danger" class="rounded-lg">
                                    Delete
                                </x-ts-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="py-3 px-3 text-sm text-gray-500" colspan="5">No vet visits yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

@push('styles')
<style>[x-cloak]{ display:none !important; }</style>
@endpush
