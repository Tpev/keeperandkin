<div> {{-- single root --}}
    @php $canDelete = auth()->user()?->currentTeam?->id === $dog->team_id; @endphp

    @if($canDelete)
        <x-ts-button
            color="danger"
            icon="trash"
            wire:click="delete"
            wire:loading.attr="disabled"
            wire:target="delete"
        >
            <span wire:loading.remove wire:target="delete">Remove dog</span>
            <span wire:loading wire:target="delete">Removing…</span>
        </x-ts-button>
    @endif
</div>
