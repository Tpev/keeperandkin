{{-- resources/views/dogs/edit-wrapper.blade.php --}}
<x-app-layout>
    @push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Raleway', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Playfair Display', serif; }
        [x-cloak]{ display:none !important; }
    </style>
    @endpush

    @php
        $KK_NAVY     = '#03314C';
        $KK_BLUE     = '#076BA8';
        $KK_BLUE_ALT = '#DAEEFF';
    @endphp

    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('dogs.index') }}" class="text-sm" style="color: {{ $KK_BLUE }};">← Back</a>
            <h2 class="text-2xl font-semibold">Edit Dog: {{ $dog->name }}</h2>
        </div>
    </x-slot>

    {{-- Full-width gradient section with a wide centered card --}}
    <div class="min-h-[calc(100vh-4rem)] py-8 px-4 sm:px-6 lg:px-8"
         style="color: {{ $KK_NAVY }}; background-image: linear-gradient(to bottom right, #eaeaea, #ffffff 35%, {{ $KK_BLUE_ALT }} 100%);">

        <div class="max-w-5xl mx-auto">
            <div class="rounded-3xl ring-1 ring-black/5 shadow-lg p-6 md:p-10"
                 style="background: rgba(255,255,255,0.92);">
                <div class="mb-6 flex items-start justify-between">
                    <div>
                        <h1 class="text-3xl font-extrabold">Edit details</h1>
                        <p class="mt-1 text-sm" style="color: {{ $KK_NAVY }}B3">
                            Update core info; changes will reflect on the profile page.
                        </p>
                    </div>
                    <a href="{{ route('dogs.show', $dog) }}"
                       class="inline-flex items-center px-3 py-2 rounded-xl text-sm font-semibold ring-1 transition"
                       style="color: {{ $KK_BLUE }}; background: {{ $KK_BLUE_ALT }}; ring-color: #E2E8F0;">
                        View profile
                    </a>
                </div>

                {{-- Your existing Livewire form component (now wide) --}}
                <div class="max-w-none">
                    <livewire:dogs.edit :dog="$dog" />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
