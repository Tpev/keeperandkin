<div class="inline-flex items-center gap-2">
    <button wire:click="generate"
            wire:loading.attr="disabled"
            class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold border"
            style="background: #FFFFFF; color: #076BA8; border-color: #076BA8;">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v6m0 8v6m6-12h6M0 12h6"/>
        </svg>
        {{ $working ? 'Generating…' : 'Generate Training Plan' }}
    </button>

    @if($message)
        <span class="text-sm">{{ $message }}</span>
    @endif
</div>
