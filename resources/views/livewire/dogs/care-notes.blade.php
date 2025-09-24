{{-- resources/views/livewire/dogs/care-notes.blade.php --}}
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

<section class="max-w-7xl mx-auto mt-12 border" style="background: {{ $KK_BLUE_ALT }}; border-color: {{ $KK_DIVIDER }};">
    {{-- Section header --}}
    <div class="px-6 py-4" style="background:#fff; border-bottom:1px solid {{ $KK_DIVIDER }};">
        <h2 class="text-xl font-bold" style="color: {{ $KK_NAVY }}">Care-Team Notes</h2>
    </div>

    <div class="p-6">
        {{-- Add note form --}}
        <div class="border mb-8" style="background:#fff; border-color: {{ $KK_DIVIDER }};">
            <div class="px-4 py-3" style="background: {{ $KK_BLUE_ALT }}; border-bottom:1px solid {{ $KK_DIVIDER }}">
                <h3 class="font-semibold" style="color: {{ $KK_NAVY }}">Add a note</h3>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="addNote" class="grid grid-cols-1 sm:grid-cols-6 gap-4">
                    <div class="sm:col-span-2">
                        <x-ts-input label="Author (optional)" wire:model.defer="author_name" placeholder="e.g., J. Trainer" />
                    </div>
                    <div class="sm:col-span-4">
                        <x-ts-textarea label="Note" rows="3" wire:model.defer="body" placeholder="Observation, plan, context…" />
                    </div>
                    <div class="sm:col-span-3 flex items-center gap-3">
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" wire:model.defer="pin" class="border" style="border-color: {{ $KK_DIVIDER }}">
                            <span class="text-sm" style="color: {{ $KK_NAVY }}">Pin this note</span>
                        </label>
                    </div>
                    <div class="sm:col-span-3 flex justify-end">
                        <button type="submit"
                                class="px-4 py-2 text-sm font-semibold border text-white"
                                style="background: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }};">
                            Save note
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Pinned note (if any) --}}
        @if($pinned)
            <div class="border mb-8" style="background:#FEF3C7; border-color: {{ $KK_WARNING }};">
                <div class="px-4 py-3" style="background:#FFF; border-bottom:1px solid {{ $KK_WARNING }}">
                    <div class="flex justify-between items-start">
                        <p class="font-semibold" style="color: {{ $KK_NAVY }}">Pinned</p>
                        <span class="text-xs" style="color: {{ $KK_NAVY }}B3">
                            {{ optional($pinned->created_at)->diffForHumans() }}
                        </span>
                    </div>
                </div>
                <div class="p-4">
                    <div class="prose max-w-none text-sm" style="color: {{ $KK_NAVY }};">{!! $renderBody($pinned->body) !!}</div>
                    <p class="mt-2 text-xs" style="color: {{ $KK_NAVY }}B3">— {{ $pinned->author_name ?? ($pinned->user->name ?? 'Unknown') }}</p>

                    <div class="mt-3 flex gap-2">
                        <button wire:click="unpin({{ $pinned->id }})"
                                class="px-3 py-1 text-sm font-semibold border"
                                style="background:#fff; color: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }};">
                            Unpin
                        </button>
                        <button wire:click="delete({{ $pinned->id }})"
                                class="px-3 py-1 text-sm font-semibold border"
                                style="background:#fff; color:#DC2626; border-color:#DC2626;">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Toggle + list --}}
        <div class="text-center">
            <button
                x-data
                @click="$dispatch('toggle-care-notes')"
                class="px-3 py-1 text-sm font-semibold border"
                style="color: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }}; background:#fff;">
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
                    <li class="border" style="background:#fff; border-color: {{ $KK_DIVIDER }};">
                        <div class="px-4 py-3" style="background: {{ $KK_BLUE_ALT }}; border-bottom:1px solid {{ $KK_DIVIDER }}">
                            <div class="flex justify-between items-center">
                                <p class="font-semibold" style="color: {{ $KK_NAVY }}">{{ $note->author_name ?? ($note->user->name ?? 'Unknown') }}</p>
                                <span class="text-xs" style="color: {{ $KK_NAVY }}99">
                                    {{ optional($note->created_at)->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        <div class="p-4">
                            <div class="prose max-w-none text-sm" style="color: {{ $KK_NAVY }};">{!! $renderBody($note->body) !!}</div>

                            <div class="mt-3 flex gap-2">
                                @if(!$note->pinned_at)
                                    <button wire:click="pin({{ $note->id }})"
                                            class="px-3 py-1 text-sm font-semibold border"
                                            style="background:#fff; color: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }};">
                                        Pin
                                    </button>
                                @else
                                    <button wire:click="unpin({{ $note->id }})"
                                            class="px-3 py-1 text-sm font-semibold border"
                                            style="background:#fff; color: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }};">
                                        Unpin
                                    </button>
                                @endif
                                <button wire:click="delete({{ $note->id }})"
                                        class="px-3 py-1 text-sm font-semibold border"
                                        style="background:#fff; color:#DC2626; border-color:#DC2626;">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="border text-sm" style="background:#fff; border-color: {{ $KK_DIVIDER }}; color:#6B7280;">
                        <div class="p-4">No notes yet.</div>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
</section>

@push('styles')
<style>[x-cloak]{ display:none !important; }</style>
@endpush
