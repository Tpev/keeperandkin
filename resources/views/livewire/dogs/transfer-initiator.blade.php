@php
    // Keeper & Kin palette
    $KK_NAVY     = '#03314C';
    $KK_BLUE     = '#076BA8';
    $KK_BLUE_ALT = '#DAEEFF';
    $KK_DIVIDER  = '#E2E8F0';
@endphp

{{-- Alpine root with a single entangled flag --}}
<div x-data="{ open: @entangle('showModal').live }" class="inline">

    {{-- Trigger (disabled if pending) --}}
    @if($pending)
        <button type="button"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold border opacity-60 cursor-not-allowed hard-corners"
                style="background:#FFFFFF; color:#6B7280; border-color: {{ $KK_DIVIDER }};"
                title="A transfer is already pending to {{ $pending->to_email }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-4-4m0 0a4 4 0 014-4m-4 4h14M7 8l4-4m0 0l4 4m-4-4v12" />
            </svg>
            In transfer…
        </button>
    @else
        <button type="button" @click="open = true"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold border hard-corners"
                style="background:#FFFFFF; color: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }};">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: {{ $KK_BLUE }}">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-4-4m0 0a4 4 0 014-4m-4 4h14M7 8l4-4m0 0l4 4m-4-4v12" />
            </svg>
            Transfer ownership
        </button>
    @endif

    {{-- Teleport the modal to <body> so Livewire nesting can't fight it --}}
    <template x-teleport="body">
        <div x-cloak
             x-show="open"
             x-transition.opacity
             @keydown.escape.window="open = false; $wire.close()"
             class="fixed inset-0 z-[100] flex items-center justify-center"
             role="dialog" aria-modal="true"
             wire:ignore>
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/40" @click="open = false; $wire.close()"></div>

            {{-- Dialog --}}
            <div class="relative z-10 w-full max-w-lg border bg-white shadow-xl hard-corners"
                 style="border-color: {{ $KK_DIVIDER }};">

                {{-- Header --}}
                <div class="px-5 py-4 border-b hard-corners"
                     style="border-color: {{ $KK_DIVIDER }}; background: {{ $KK_BLUE_ALT }};">
                    <h3 class="text-lg font-semibold" style="font-family:'Playfair Display',serif; color: {{ $KK_NAVY }}">
                        Transfer ownership
                    </h3>
                </div>

                {{-- Body --}}
                <div class="p-5 space-y-4" style="font-family:'Raleway',sans-serif; color: {{ $KK_NAVY }}">
                    <div>
                        <label class="text-sm font-medium">Recipient email</label>
                        <input type="email" wire:model.defer="to_email"
                               class="mt-1 w-full border px-3 py-2 hard-corners"
                               style="border-color: {{ $KK_DIVIDER }};"
                               placeholder="someone@example.org" />
                        @error('to_email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-6">
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" wire:model="include_private_notes" class="hard-corners">
                            Include private notes
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" wire:model="include_adopter_pii" class="hard-corners">
                            Include adopter PII
                        </label>
                    </div>

                    <p class="text-xs" style="color:#64748B">
                        A magic-link email will be sent. The link expires in {{ config('dog_transfer.ttl_days', 7) }} days.
                    </p>
                </div>

                {{-- Footer --}}
                <div class="px-5 py-4 border-t flex items-center justify-end gap-2 hard-corners"
                     style="border-color: {{ $KK_DIVIDER }};">
                    <button type="button"
                            @click="open = false; $wire.close()"
                            class="px-3 py-2 text-sm border hard-corners"
                            style="background:#FFFFFF; color:#374151; border-color: {{ $KK_DIVIDER }};">
                        Cancel
                    </button>
                    <button type="button"
                            @click="$wire.startTransfer()"
                            class="px-3 py-2 text-sm font-semibold border hard-corners"
                            style="background: {{ $KK_BLUE }}; color:#FFFFFF; border-color: {{ $KK_BLUE }};">
                        Send invite
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
