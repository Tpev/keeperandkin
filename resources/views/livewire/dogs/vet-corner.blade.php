{{-- resources/views/livewire/dogs/vet-corner.blade.php --}}
@php
    $KK_NAVY     = '#03314C';
    $KK_BLUE     = '#076BA8';   // brand primary
    $KK_BLUE_ALT = '#DAEEFF';
    $KK_DIVIDER  = '#E2E8F0';

    $weightChip  = number_format($current_weight ?? ($dog->vetMetric->current_weight ?? 0), 1);
    $bcsChip     = $bcs ?? ($dog->vetMetric->bcs ?? null);
    $nextVac     = $next_vaccine_date ?? optional($dog->vetMetric?->next_vaccine_date)->format('Y-m-d');
@endphp

<section class="max-w-7xl mx-auto mt-12 border"
         style="background:#FFF7ED; border-color: {{ $KK_DIVIDER }}; color: {{ $KK_NAVY }};">
    {{-- Section header --}}
    <div class="px-6 py-4" style="background: {{ $KK_BLUE_ALT }}; border-bottom: 1px solid {{ $KK_DIVIDER }}">
        <h2 class="text-xl font-bold">Veterinary Information</h2>
    </div>

    <div class="p-6">
        {{-- Summary strip --}}
        <div class="flex flex-wrap gap-2 mb-8">
            <span class="inline-flex items-center gap-2 text-xs font-semibold text-white px-3 py-1 border"
                  style="background: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }};">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422A12.083 12.083 0 0119 13.714L12 21l-7-7.286a12.083 12.083 0 01.84-3.136L12 14z"/></svg>
                {{ $weightChip > 0 ? $weightChip.' lb' : '— lb' }}
            </span>

            <span class="inline-flex items-center gap-2 text-xs font-semibold text-white px-3 py-1 border"
                  style="background:#7C3AED; border-color:#7C3AED;">
                BCS {{ $bcsChip ? $bcsChip.'/9' : '—/9' }}
            </span>

            <span class="inline-flex items-center gap-2 text-xs font-semibold text-white px-3 py-1 border"
                  style="background:#16A34A; border-color:#16A34A;">
                Next vaccine {{ $nextVac ? \Carbon\Carbon::parse($nextVac)->format('M d') : '—' }}
            </span>
        </div>

        {{-- Metrics card --}}
        <div class="border mb-10" style="border-color: {{ $KK_DIVIDER }}; background:#fff;">
            <div class="px-4 py-3" style="background: {{ $KK_BLUE_ALT }}; border-bottom:1px solid {{ $KK_DIVIDER }}">
                <h3 class="font-semibold">Update metrics</h3>
            </div>

            <div class="p-6">
                <form wire:submit.prevent="saveMetrics" class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <x-ts-input label="Current weight (lb)" type="number" step="0.1" min="0" wire:model.defer="current_weight" />
                    <x-ts-input label="BCS (1–9)" type="number" min="1" max="9" wire:model.defer="bcs" />
                    <x-ts-input label="Next vaccine date" type="date" wire:model.defer="next_vaccine_date" />
                    <div class="sm:col-span-3 flex justify-end">
                        <button type="submit"
                                class="px-4 py-2 text-sm font-semibold border text-white"
                                style="background: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }};">
                            Save metrics
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Toggle --}}
        <div class="text-center">
            <button x-data @click="$dispatch('toggle-vet-visits')"
                    class="px-3 py-1 text-sm font-semibold border"
                    style="color: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }}; background:#fff;">
                {{ $showVisits ? 'Hide vet visits' : 'Show vet visits' }}
            </button>
        </div>

        {{-- Visits --}}
        <div x-data="{ open: @entangle('showVisits') }"
             x-on:toggle-vet-visits.window="open = !open"
             x-show="open" x-cloak
             class="mt-8">

            {{-- Add visit card --}}
            <div class="border mb-6" style="border-color: {{ $KK_DIVIDER }}; background:#fff;">
                <div class="px-4 py-3" style="background: {{ $KK_BLUE_ALT }}; border-bottom:1px solid {{ $KK_DIVIDER }}">
                    <h3 class="font-semibold">Add a visit</h3>
                </div>

                <div class="p-6">
                    <form wire:submit.prevent="addVisit" class="grid grid-cols-1 sm:grid-cols-4 gap-5">
                        <x-ts-input label="Date" type="date" wire:model.defer="visit_date" />
                        <x-ts-input label="Reason" wire:model.defer="reason" />
                        <x-ts-input label="Weight (lb)" type="number" step="0.1" min="0" wire:model.defer="visit_weight" />

                        {{-- File upload --}}
                        <div>
                            <label class="block text-sm font-medium mb-1" style="color: {{ $KK_NAVY }}">Attachment (PDF/JPG/PNG, max 10 MB)</label>
                            <input type="file" wire:model="visit_file" accept=".pdf,image/*"
                                   class="block w-full text-sm border px-3 py-2"
                                   style="border-color: {{ $KK_DIVIDER }}; background:#fff;" />
                            @error('visit_file')
                                <p class="text-xs mt-1" style="color:#DC2626">{{ $message }}</p>
                            @enderror

                            <div x-data="{ isUploading: false, progress: 0 }"
                                 x-on:livewire-upload-start="isUploading = true"
                                 x-on:livewire-upload-finish="isUploading = false; progress = 0"
                                 x-on:livewire-upload-error="isUploading = false"
                                 x-on:livewire-upload-progress="progress = $event.detail.progress"
                                 class="mt-2">
                                <div x-show="isUploading" class="w-full h-2 overflow-hidden" style="background:#e5e7eb;">
                                    <div class="h-full" :style="`width: ${progress}%; background: {{ $KK_BLUE }};`"></div>
                                </div>
                            </div>

                            @if ($visit_file && str_starts_with($visit_file->getMimeType(), 'image/'))
                                <img src="{{ $visit_file->temporaryUrl() }}"
                                     alt="Preview"
                                     class="mt-2 h-24 w-24 object-cover border"
                                     style="border-color: {{ $KK_DIVIDER }};">
                            @endif
                        </div>

                        <div class="sm:col-span-4">
                            <x-ts-textarea label="Outcome / Notes" rows="3" wire:model.defer="outcome" />
                        </div>

                        <div class="sm:col-span-4 flex justify-end">
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-semibold border text-white"
                                    style="background: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }};">
                                Add visit
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Visits table --}}
            <div class="border" style="border-color: {{ $KK_DIVIDER }}; background:#fff;">
                <div class="px-4 py-3" style="background: {{ $KK_BLUE_ALT }}; border-bottom:1px solid {{ $KK_DIVIDER }}">
                    <h3 class="font-semibold">Visit history</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm" style="border-collapse: collapse;">
                        <thead>
                            <tr style="background: {{ $KK_BLUE_ALT }}; border-bottom:1px solid {{ $KK_DIVIDER }};">
                                <th class="py-2 px-3 text-left">Date</th>
                                <th class="py-2 px-3 text-left">Reason</th>
                                <th class="py-2 px-3 text-left">Outcome</th>
                                <th class="py-2 px-3 text-left">Weight</th>
                                <th class="py-2 px-3 text-left">Attachment</th>
                                <th class="py-2 px-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dog->vetVisits as $v)
                                <tr style="border-top:1px solid {{ $KK_DIVIDER }};">
                                    <td class="py-2 px-3 font-medium">{{ $v->visit_date->format('M d, Y') }}</td>
                                    <td class="py-2 px-3">{{ $v->reason }}</td>
                                    <td class="py-2 px-3">{{ $v->outcome }}</td>
                                    <td class="py-2 px-3">{{ $v->weight ? number_format($v->weight,1) : '—' }}</td>
                                    <td class="py-2 px-3">
                                        @if($v->document_url)
                                            <a href="{{ $v->document_url }}" target="_blank"
                                               class="inline-flex items-center gap-1 text-sm font-semibold border px-2 py-1"
                                               style="color: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }}; background:#fff;">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: {{ $KK_BLUE }}">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6.75l-6.364 6.364a3 3 0 104.243 4.243l6.364-6.364a4.5 4.5 0 00-6.364-6.364L6 8.25" />
                                                </svg>
                                                View
                                            </a>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="py-2 px-3 text-right">
                                        <button wire:click="deleteVisit({{ $v->id }})"
                                                class="px-3 py-1 text-sm font-semibold border"
                                                style="background:#fff; border-color:#DC2626; color:#DC2626;">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr style="border-top:1px solid {{ $KK_DIVIDER }};">
                                    <td class="py-3 px-3 text-sm text-gray-500" colspan="6">No vet visits yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</section>

@push('styles')
<style>[x-cloak]{ display:none !important; }</style>
@endpush
