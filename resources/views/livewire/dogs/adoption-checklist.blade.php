@php
    $KK_NAVY     = '#03314C';
    $KK_BLUE     = '#076BA8';
    $KK_BLUE_ALT = '#DAEEFF';
    $KK_SUCCESS  = '#16A34A';
    $KK_DIVIDER  = '#E2E8F0';
@endphp

<section class="max-w-7xl mx-auto mt-12 border p-0"
         style="background: #FFFFFF; border-color: {{ $KK_DIVIDER }};">
    {{-- Section header (brand-aligned, hard corners) --}}
    <div class="px-6 py-4" style="background: {{ $KK_BLUE_ALT }}; border-bottom:1px solid {{ $KK_DIVIDER }}">
        <h2 class="text-xl font-bold" style="color: {{ $KK_NAVY }}">Adoption Requirements Checklist</h2>
    </div>

    {{-- Body --}}
    <div class="p-6">
        @php $total = $items->count(); $done = $items->whereNotNull('completed_at')->count(); @endphp

        {{-- Progress bar (optional but useful) --}}
        <div class="mb-6">
            <div class="flex items-center justify-between text-sm mb-2">
                <span style="color: {{ $KK_NAVY }}B3">{{ $done }} of {{ $total }} completed</span>
                <span class="font-semibold" style="color: {{ $KK_NAVY }}B3">
                    {{ $total ? intval(($done / max(1,$total)) * 100) : 0 }}%
                </span>
            </div>
            <div class="h-2 w-full border overflow-hidden" style="border-color: {{ $KK_DIVIDER }}; background: #F1F5F9;">
                <div class="h-full" style="width: {{ $total ? ($done/$total)*100 : 0 }}%; background: {{ $KK_BLUE }};"></div>
            </div>
        </div>

        {{-- List --}}
        <ul class="text-sm">
            @forelse ($items as $idx => $item)
                <li class="flex items-start gap-3 py-3 border-b"
                    style="border-color: {{ $KK_DIVIDER }}; {{ $loop->last ? 'border-bottom: none;' : '' }}">
                    {{-- Checkbox --}}
                    <button
                        type="button"
                        wire:click="toggle({{ $item->id }})"
                        class="w-5 h-5 border flex items-center justify-center"
                        style="border-color: {{ $KK_DIVIDER }}; background: #fff;"
                        title="Toggle"
                    >
                        @if($item->completed_at)
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="3" style="color: {{ $KK_SUCCESS }}">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        @endif
                    </button>

                    {{-- Label + meta --}}
                    <div class="flex-1">
                        <span class="leading-snug block" style="color: {{ $KK_NAVY }}">{{ $item->label }}</span>

                        @if($item->completed_at)
                            <span class="mt-1 inline-flex items-center gap-2 text-xs"
                                  style="color: {{ $KK_NAVY }}99;">
                                Completed by
                                <span class="font-medium" style="color: {{ $KK_NAVY }}">
                                    {{ optional($item->completedBy)->name ?? 'Unknown' }}
                                </span>
                                on
                                <span class="font-medium" style="color: {{ $KK_NAVY }}">
                                    {{ $item->completed_at->format('M d, Y') }}
                                </span>
                            </span>
                        @endif
                    </div>
                </li>
            @empty
                <li class="py-3 text-sm" style="color:#6B7280;">No requirements defined.</li>
            @endforelse
        </ul>
    </div>
</section>
