@php
    $KK_NAVY     = '#03314C';
    $KK_BLUE     = '#076BA8';
    $KK_BLUE_ALT = '#DAEEFF';
    $KK_DIVIDER  = '#E2E8F0';
@endphp

<div class="max-w-2xl mx-auto space-y-6">

    {{-- Error panel --}}
    @if ($errors->any())
        <div class="border-l-4 p-3" style="border-color:#DC2626; background:#FEF2F2; color:#7F1D1D">
            <div class="text-sm font-semibold">There was a problem:</div>
            <ul class="mt-1 text-sm list-disc list-inside">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-lg border p-4" style="border-color: {{ $KK_DIVIDER }}; background:#fff;">
        <h1 class="text-xl font-semibold mb-2" style="color:{{ $KK_NAVY }}">Accept transfer: {{ $transfer->dog->name }}</h1>
        <p class="text-sm" style="color:#475569">
            From: <strong>{{ $transfer->fromTeam->name }}</strong> ·
            Expires: {{ $transfer->expires_at->toDayDateTimeString() }}
        </p>
        <ul class="mt-3 text-sm list-disc list-inside" style="color:#334155">
            <li>{{ $transfer->count_evaluations }} evaluations</li>
            <li>{{ $transfer->count_files }} files</li>
            <li>{{ $transfer->count_notes }} notes</li>
            <li>Private notes: {{ $transfer->include_private_notes ? 'included' : 'excluded' }}</li>
            <li>Adopter PII: {{ $transfer->include_adopter_pii ? 'included' : 'scrubbed' }}</li>
        </ul>
    </div>

    @guest
        <div class="rounded-lg border p-4" style="border-color: {{ $KK_DIVIDER }}; background: {{ $KK_BLUE_ALT }};">
            <p class="text-sm">You need to sign in to continue.</p>
            <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}"
               class="inline-block mt-2 px-4 py-2 border"
               style="background: {{ $KK_BLUE }}; color:#fff; border-color: {{ $KK_BLUE }};">
               Sign in
            </a>
        </div>
    @else
        <div class="rounded-lg border p-4 space-y-4" style="border-color: {{ $KK_DIVIDER }}; background:#fff;">
            <div>
                <label class="text-sm font-medium">Destination team</label>
                <select wire:model.defer="destination_team_id"
                        class="mt-1 w-full border px-3 py-2 hard-corners"
                        style="border-color: {{ $KK_DIVIDER }};">
                    <option value="">Select a team…</option>
                    @foreach($teams as $team)
                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                    @endforeach
                </select>
                @error('destination_team_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-2">
                <input type="checkbox" wire:model.live="confirm_authority" class="hard-corners">
                <span>I have authority to accept this transfer.</span>
            </label>
            @error('confirm_authority') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror

            <div class="flex items-center gap-3">
                <button
                    wire:click.prevent="confirmAccept"
                    wire:loading.attr="disabled"
                    wire:target="confirmAccept"
                    class="border px-4 py-2 hard-corners"
                    style="background: {{ $KK_BLUE }}; color:#fff; border-color: {{ $KK_BLUE }};">
                    <span wire:loading.remove wire:target="confirmAccept">Accept</span>
                    <span wire:loading wire:target="confirmAccept">Processing…</span>
                </button>

                <button
                    wire:click.prevent="decline"
                    wire:loading.attr="disabled"
                    wire:target="decline"
                    class="border px-4 py-2 hard-corners"
                    style="background:#fff; color:#374151; border-color: {{ $KK_DIVIDER }};">
                    <span wire:loading.remove wire:target="decline">Decline</span>
                    <span wire:loading wire:target="decline">Working…</span>
                </button>
            </div>
        </div>
    @endguest
</div>
