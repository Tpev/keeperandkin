{{-- resources/views/dogs/edit.blade.php --}}
<div>
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
    $KK_DIVIDER  = '#E2E8F0';
@endphp

<div class="min-h-screen py-10 px-4 sm:px-6 lg:px-8"
     style="color: {{ $KK_NAVY }}; background-image: linear-gradient(to bottom right, #eaeaea, #ffffff 35%, {{ $KK_BLUE_ALT }} 100%);">

    {{-- Top bar --}}
    <div class="max-w-4xl mx-auto mb-6 flex items-center gap-3">
        <a href="{{ route('dogs.show', $dog) }}"
           class="inline-flex items-center text-sm font-medium"
           style="color: {{ $KK_BLUE }};">
            ← Back to profile
        </a>
    </div>

    {{-- Header card --}}
    <section class="max-w-4xl mx-auto backdrop-blur rounded-3xl ring-1 ring-black/5 shadow-lg p-6 md:p-8"
             style="background: rgba(255,255,255,0.9);">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold">Edit Dog</h1>
                <p class="mt-1 text-sm" style="color: {{ $KK_NAVY }}B3">
                    Update details. Changes save to the dog’s profile.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('dogs.show', $dog) }}"
                   class="inline-flex items-center px-3 py-2 rounded-xl text-sm font-semibold ring-1 transition"
                   style="color: {{ $KK_BLUE }}; background: {{ $KK_BLUE_ALT }}; ring-color: {{ $KK_DIVIDER }};">
                    View
                </a>
                <livewire:dogs.delete-button :dog="$dog" />
            </div>
        </div>

        {{-- Form card --}}
        <div class="mt-6 rounded-2xl ring-1 ring-black/5 p-6 md:p-8 shadow-sm"
             style="background: #ffffff;">
            @if ($errors->any())
                <div class="mb-4 rounded-xl p-4 text-sm"
                     style="background: #FEF2F2; color: #991B1B; border:1px solid #FCA5A5;">
                    <strong class="block mb-1">Please fix the following:</strong>
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form wire:submit.prevent="update" class="space-y-8">
                {{-- Basic Info --}}
                <div>
                    <h3 class="font-semibold text-base">Basic Info</h3>
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        <x-ts-input label="Name" wire:model.defer="name" required />
                        <x-ts-input label="Breed" wire:model.defer="breed" />
                        <x-ts-input label="Age (years)" type="number" min="0" max="30" step="0.1" wire:model.defer="age" />
                        <x-ts-input label="Serial Number" wire:model.defer="serial_number" />
                        <x-ts-input label="Location" wire:model.defer="location" />
                        <x-ts-select.native
                            label="Sex"
                            :options="[
                                ['label' => 'Male', 'value' => 'male'],
                                ['label' => 'Female', 'value' => 'female'],
                            ]"
                            wire:model.defer="sex"
                        />
                        <x-ts-input label="Approx. Date of Birth (US)" wire:model.defer="approx_dob" placeholder="MM/DD/YYYY"/>
                        @php
                            $fixedOptions = [
                                ['label' => 'Unknown', 'value' => ''],
                                ['label' => 'Yes', 'value' => '1'],
                                ['label' => 'No', 'value' => '0'],
                            ];
                        @endphp
                        <x-ts-select.native label="Fixed" :options="$fixedOptions" wire:model.defer="fixed" />
                        <x-ts-input label="Color" wire:model.defer="color" />
                        <x-ts-input label="Size" wire:model.defer="size" />
                        <x-ts-input label="Microchip" wire:model.defer="microchip" />
                    </div>
                </div>

                {{-- Health --}}
                <div>
                    <h3 class="font-semibold text-base">Health</h3>
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        <x-ts-input label="Heartworm" wire:model.defer="heartworm" />
                        <x-ts-input label="FIV/L" wire:model.defer="fiv_l" />
                        <x-ts-input label="FLV" wire:model.defer="flv" />
                    </div>
                </div>

                {{-- Home Compatibility --}}
                <div>
                    <h3 class="font-semibold text-base">Home Compatibility</h3>
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        <x-ts-input label="Housetrained?" wire:model.defer="housetrained" />
                        <x-ts-input label="Good with Dogs?" wire:model.defer="good_with_dogs" />
                        <x-ts-input label="Good with Cats?" wire:model.defer="good_with_cats" />
                        <x-ts-input label="Good with Children?" wire:model.defer="good_with_children" />
                    </div>
                </div>

                <div>
                    <x-ts-textarea label="Description" rows="5" wire:model.defer="description" />
                </div>

                {{-- Save bar --}}
                <div class="border-t pt-4 mt-6 flex items-center justify-between">
                    <p class="text-xs" style="color: {{ $KK_NAVY }}99">
                        Make sure details reflect the latest evaluation.
                    </p>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('dogs.show', $dog) }}"
                           class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold ring-1 transition"
                           style="background:#FFFFFF; color: {{ $KK_NAVY }}; ring-color: {{ $KK_DIVIDER }};">
                            Cancel
                        </a>
                        <x-ts-button type="submit" class="rounded-xl shadow"
                                     style="background: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }};">
                            Save changes
                        </x-ts-button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>
</div>
