<x-app-layout>
    @push('styles')
    <style>
      :root{
        --kk-navy:#03314C;
        --kk-blue:#076BA8;
        --kk-blue-alt:#DAEEFF;
        --kk-bg:#eaeaea;
        --kk-divider:#E2E8F0;
        --kk-success:#16A34A;
        --kk-warning:#F59E0B;
        --kk-danger:#DC2626;
        --kk-lav:#6366F1;
      }
      .kk-card{
        background: rgba(255,255,255,.92);
        border-radius: 1.5rem; /* rounded-3xl */
        box-shadow: 0 10px 25px rgba(3,49,76,.06);
        backdrop-filter: blur(2px);
        border: 1px solid rgba(0,0,0,.05);
      }
      .kk-header h1{
        font-family: "Playfair Display", Georgia, serif;
        letter-spacing: .2px;
        color: var(--kk-navy);
      }
      .kk-action-btn{
        background: var(--kk-blue);
        color: #fff;
        border-radius: .875rem; /* rounded-xl */
        padding: .625rem 1rem;
        box-shadow: 0 6px 16px rgba(7,107,168,.18);
      }
      .kk-action-btn:hover{ filter: brightness(1.05); transform: translateY(-1px); transition: .15s; }
      .kk-page{
        color: var(--kk-navy);
        background-image: linear-gradient(to bottom right, var(--kk-bg), #ffffff 35%, var(--kk-blue-alt) 100%);
        min-height: 100vh;
      }
    </style>
    @endpush

    <div class="kk-page">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            {{-- Page header --}}
            <div class="kk-header mb-6 flex items-center justify-between gap-4">
                <h1 class="text-3xl font-extrabold">Dogs</h1>
                {{-- NEW button (keeps your TallStack button, but we also show a styled native fallback) --}}
                <div class="flex items-center gap-3">
                    <x-ts-button href="{{ route('dogs.create') }}" class="kk-action-btn">
                        + Add Dog
                    </x-ts-button>
                </div>
            </div>
@if (session('status'))
    <x-ts-alert color="success" class="mb-4">
        {{ session('status') }}
    </x-ts-alert>
@endif

            {{-- Card container around the table --}}
            <section class="kk-card p-4 sm:p-6 md:p-8">
                {{-- Optional top bar (search/filters) – leave placeholders for now --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 mb-4">
                    <div class="text-sm opacity-80">Click a row to open a dog profile.</div>
                    {{-- You can wire a search box later if your Livewire component supports it --}}
                    {{-- <input type="text" wire:model.debounce.400ms="search" placeholder="Search dogs…" class="rounded-xl border border-[var(--kk-divider)] px-3 py-2 w-full sm:w-64"> --}}
                </div>

                {{-- The table (Livewire component) --}}
                <livewire:dogs.table />
            </section>
        </div>
    </div>
</x-app-layout>
