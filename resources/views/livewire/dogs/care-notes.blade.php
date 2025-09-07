@php
    use Illuminate\Support\Carbon;

    $KK_NAVY     = '#03314C';
    $KK_BLUE     = '#076BA8';
    $KK_BLUE_ALT = '#DAEEFF';
    $KK_WARNING  = '#F59E0B';
    $KK_DIVIDER  = '#E2E8F0';

    // Helper to render body safely (supports simple line breaks)
    $renderBody = fn(string $text) => nl2br(e($text));
@endphp

<section class="max-w-7xl mx-auto mt-16 rounded-3xl ring-1 ring-black/5 p-8 shadow-lg" style="background: {{ $KK_BLUE_ALT }};">
    <h2 class="text-xl font-bold mb-6">Care-Team Notes</h2>

    {{-- Add note form --}}
    <div class="rounded-2xl ring-1 ring-black/5 p-6 mb-6" style="background: #fff;">
        <h3 class="font-semibold mb-4">Add a note</h3>
        <form wire:submit.prevent="addNote" class="grid grid-cols-1 sm:grid-cols-6 gap-4">
            <div class="sm:col-span-2">
                <x-ts-input label="Author (optional)" wire:model.defer="author_name" placeholder="e.g., J. Trainer" />
            </div>
            <div class="sm:col-span-4">
                <x-ts-textarea label="Note" rows="3" wire:model.defer="body" placeholder="Observation, plan, context…" />
            </div>
            <div class="sm:col-span-3 flex items-center gap-3">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" wire:model.defer="pin" class="rounded">
                    <span class="text-sm">Pin this note</span>
                </label>
            </div>
            <div class="sm:col-span-3 flex justify-end">
                <x-ts-button type="submit" class="rounded-xl shadow" style="background: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }};">
                    Save note
                </x-ts-button>
            </div>
        </form>
    </div>

    {{-- Pinned note (if any) --}}
    @if($pinned)
        <div class="rounded-xl p-4 mb-6 shadow-sm" style="background: #FEF3C7; border-left: 4px solid {{ $KK_WARNING }};">
            <div class="flex justify-between items-start">
                <p class="font-semibold">Pinned</p>
                <span class="text-xs" style="color: {{ $KK_NAVY }}B3">
                    {{ optional($pinned->created_at)->diffForHumans() }}
                </span>
            </div>
            <div class="prose max-w-none text-sm mt-2">{!! $renderBody($pinned->body) !!}</div>
            <p class="mt-2 text-xs" style="color: {{ $KK_NAVY }}B3">— {{ $pinned->author_name ?? ($pinned->user->name ?? 'Unknown') }}</p>

            <div class="mt-3 flex gap-2">
                <x-ts-button wire:click="unpin({{ $pinned->id }})" size="sm" variant="secondary" class="rounded-lg">Unpin</x-ts-button>
                <x-ts-button wire:click="delete({{ $pinned->id }})" size="sm" variant="danger" class="rounded-lg">Delete</x-ts-button>
            </div>
        </div>
    @endif

    {{-- Toggle + list --}}
    <div class="text-center">
        <button
            x-data
            @click="$dispatch('toggle-care-notes')"
            class="font-semibold"
            style="color: {{ $KK_BLUE }};">
            {{ $showNotes ? 'Hide recent notes' : 'Show recent notes' }}
        </button>
    </div>

    <div
        x-data="{ open: @entangle('showNotes') }"
        x-on:toggle-care-notes.window="open = !open"
        x-show="open" x-cloak
        class="mt-6">

        <ul class="space-y-4">
            @forelse($notes as $note)
                <li class="p-4 rounded-xl shadow ring-1 ring-black/5" style="background: rgba(255,255,255,.95);">
                    <div class="flex justify-between items-center">
                        <p class="font-semibold">{{ $note->author_name ?? ($note->user->name ?? 'Unknown') }}</p>
                        <span class="text-xs" style="color: {{ $KK_NAVY }}99">
                            {{ optional($note->created_at)->diffForHumans() }}
                        </span>
                    </div>
                    <div class="prose max-w-none text-sm mt-2">{!! $renderBody($note->body) !!}</div>

                    <div class="mt-3 flex gap-2">
                        @if(!$note->pinned_at)
                            <x-ts-button wire:click="pin({{ $note->id }})" size="sm" class="rounded-lg">Pin</x-ts-button>
                        @else
                            <x-ts-button wire:click="unpin({{ $note->id }})" size="sm" variant="secondary" class="rounded-lg">Unpin</x-ts-button>
                        @endif
                        <x-ts-button wire:click="delete({{ $note->id }})" size="sm" variant="danger" class="rounded-lg">Delete</x-ts-button>
                    </div>
                </li>
            @empty
                <li class="p-4 rounded-xl shadow ring-1 ring-black/5 text-sm text-gray-500" style="background: rgba(255,255,255,.95);">
                    No notes yet.
                </li>
            @endforelse
        </ul>
    </div>
</section>

@push('styles')
<style>[x-cloak]{ display:none !important; }</style>
@endpush
