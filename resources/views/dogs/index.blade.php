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

      /* Page */
      .kk-page{
        color: var(--kk-navy);
        background-image: linear-gradient(to bottom right, var(--kk-bg), #ffffff 35%, var(--kk-blue-alt) 100%);
        min-height: 100vh;
      }

      /* Header */
      .kk-header h1{
        font-family: "Playfair Display", Georgia, serif;
        letter-spacing: .2px;
        color: var(--kk-navy);
      }

      /* Primary action button (hard corners) */
      .kk-action-btn{
        background: var(--kk-blue);
        color: #fff;
        border: 1px solid var(--kk-blue);
        padding: .625rem 1rem;
        font-weight: 700;
      }
      .kk-action-btn:hover{ filter: brightness(1.06); transition: filter .15s ease; }

      /* Card shell (hard corners, crisp borders, no heavy shadows) */
      .kk-card{
        background: #fff;
        border: 1px solid var(--kk-divider);
      }

      /* Subtle alert alignment with brand */
      .kk-alert{
        border:1px solid var(--kk-success);
        background: #ECFDF5;
        color: var(--kk-navy);
      }
    </style>
    @endpush

    <div class="kk-page">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            {{-- Page header --}}
            <div class="kk-header mb-6 flex items-center justify-between gap-4">
                <h1 class="text-3xl font-extrabold">Dogs</h1>

                {{-- Primary action --}}
                <div class="flex items-center gap-3">
                    <a href="{{ route('dogs.create') }}" class="kk-action-btn inline-flex items-center">
                        + Add Dog
                    </a>
                </div>
            </div>

            @if (session('status'))
                <div class="kk-alert mb-4 px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Card container around the table --}}
            <section class="kk-card p-4 sm:p-6 md:p-8">
                {{-- Context bar (kept minimal; table has its own toolbar now) --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 mb-4">
                    <div class="text-sm opacity-80">Click a row to open a dog profile.</div>
                </div>

                {{-- The table (Livewire component includes brand toolbar and filters) --}}
                <livewire:dogs.table />
            </section>
        </div>
    </div>
</x-app-layout>
