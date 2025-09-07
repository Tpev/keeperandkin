<x-app-layout>
    @push('styles')
    <style>
      /* ===== Keeper & Kin theme vars ===== */
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
      .kk-page{
        color:var(--kk-navy);
        background-image:linear-gradient(to bottom right,var(--kk-bg),#ffffff 35%,var(--kk-blue-alt) 100%);
        min-height:100vh;
      }
      .kk-card{
        background:rgba(255,255,255,.92);
        border-radius:1.5rem;
        box-shadow:0 10px 25px rgba(3,49,76,.06);
        border:1px solid rgba(0,0,0,.05);
        backdrop-filter:blur(2px);
      }
      .kk-h1{ font-family:"Playfair Display", Georgia, serif; color:var(--kk-navy); }
      .kk-link{ color:var(--kk-blue); font-weight:600; }
      /* sticky save bar */
      .kk-sticky{
        position:sticky; bottom:1rem; z-index:30;
        background:rgba(255,255,255,.96);
        border:1px solid var(--kk-divider);
        border-radius:1rem; padding:.75rem 1rem;
        box-shadow:0 10px 30px rgba(3,49,76,.12);
      }
    </style>
    @endpush>

    <div class="kk-page">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="kk-h1 text-3xl font-extrabold">Evaluate {{ $dog->name }}</h2>
                <a href="{{ route('dogs.show', $dog) }}" class="kk-link text-sm">Back to profile</a>
            </div>

            <div class="kk-card p-5 sm:p-8">
                <livewire:dogs.evaluation-form :dog="$dog" />
            </div>
        </div>
    </div>
</x-app-layout>
