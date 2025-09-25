{{-- resources/views/livewire/dogs/edit.blade.php --}}
@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{
    --kk-navy:#03314C;
    --kk-blue:#076BA8;
    --kk-blue-alt:#DAEEFF;
    --kk-bg:#eaeaea;
    --kk-divider:#E2E8F0;
    --kk-danger:#DC2626;
  }
  .kk *{ border-radius:0 !important; }   /* hard corners */
  .kk{ color:var(--kk-navy); }
  .kk h1,.kk h2,.kk h3,.kk h4{ font-family:'Playfair Display',serif; }
  .kk, .kk input, .kk select, .kk textarea, .kk label{ font-family:'Raleway',sans-serif; }

  .kk-actionbar{ background:#fff; border:1px solid var(--kk-divider); }
  .kk-btn{ border:1px solid var(--kk-blue); color:var(--kk-blue); background:#fff; font-weight:700; padding:.6rem .9rem; }
  .kk-btn--primary{ background:var(--kk-blue); color:#fff; }

  .kk-section{ background:#fff; border:1px solid var(--kk-divider); }
  .kk-section__head{ background:var(--kk-blue-alt); padding:.75rem 1rem; border-bottom:1px solid var(--kk-divider); }
  .kk-section__title{ font-weight:800; letter-spacing:.2px; }

  .kk-error{ font-size:.85rem; color:var(--kk-danger); }
  .kk-help{ font-size:.75rem; color:#475569; }

  .kk-progress{ height:.5rem; background:#e5e7eb; overflow:hidden; border:1px solid var(--kk-divider);}
  .kk-progress__bar{ height:100%; background:var(--kk-blue); transition:width .2s ease; }

  [x-cloak]{ display:none !important; }
</style>
@endpush

@php
  $photoUrl = $dog->photo_url ?? 'https://placehold.co/160x160?text=Dog';
@endphp

<div class="kk min-h-screen py-8 px-4 sm:px-6 lg:px-8"
     style="background-image: linear-gradient(to bottom right, var(--kk-bg), #ffffff 35%, var(--kk-blue-alt) 100%);">

  {{-- Top action bar --}}
  <div class="kk-actionbar max-w-7xl mx-auto mb-4 p-3 flex items-center justify-between gap-3">
    <div class="text-sm">
      <a href="{{ route('dogs.show', $dog) }}" class="kk-btn">← Back to profile</a>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('dogs.show', $dog) }}" class="kk-btn">View</a>
      <livewire:dogs.delete-button :dog="$dog" />
    </div>
  </div>

  <form wire:submit.prevent="update" class="max-w-7xl mx-auto space-y-8">
    {{-- Header --}}
    <section class="kk-section">
      <div class="kk-section__head">
        <h2 class="kk-section__title">Edit Dog</h2>
      </div>
      <div class="p-5 flex flex-col md:flex-row gap-6">
        <div class="flex items-start gap-4">
          {{-- Current profile photo --}}
          <img src="{{ $photoUrl }}" alt="Current photo"
               class="w-24 h-24 md:w-28 md:h-28 object-cover border"
               style="border-color: var(--kk-blue-alt);" loading="lazy">

          {{-- Upload a new one --}}
          <div class="flex-1">
            <label class="block text-sm font-semibold mb-1">Change Profile Photo</label>
            <input
              type="file"
              accept="image/*"
              wire:model="new_photo"
              class="block w-full text-sm file:mr-4 file:border-0 file:bg-[var(--kk-blue-alt)] file:px-3 file:py-2 file:font-semibold file:text-[var(--kk-blue)]
                     border border-[var(--kk-divider)] px-3 py-2"
            />
            @error('new_photo')
              <p class="kk-error mt-1">{{ $message }}</p>
            @enderror

            {{-- Upload progress --}}
            <div
              class="mt-2"
              x-data="{ isUploading: false, progress: 0 }"
              x-on:livewire-upload-start="isUploading = true"
              x-on:livewire-upload-finish="isUploading = false; progress = 0"
              x-on:livewire-upload-error="isUploading = false"
              x-on:livewire-upload-progress="progress = $event.detail.progress"
            >
              <div x-show="isUploading" class="kk-progress">
                <div class="kk-progress__bar" :style="`width:${progress}%`"></div>
              </div>
            </div>

            {{-- New preview --}}
            @if ($new_photo)
              <div class="mt-3 flex items-center gap-3">
                <img src="{{ $new_photo->temporaryUrl() }}" alt="New preview"
                     class="w-20 h-20 object-cover border"
                     style="border-color: var(--kk-blue-alt);">
                <button type="button" wire:click="$set('new_photo', null)" class="kk-btn">Remove</button>
              </div>
            @endif

            <p class="kk-help mt-2">PNG or JPG up to 4MB. If no image is uploaded, the current photo is kept.</p>
          </div>
        </div>
      </div>
    </section>

    {{-- ===== Basic Info ===== --}}
    <section class="kk-section">
      <div class="kk-section__head">
        <h2 class="kk-section__title">Basic Info</h2>
      </div>
      <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
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
        <x-ts-input label="Approx. Date of Birth (US)" placeholder="MM/DD/YYYY" wire:model.defer="approx_dob" />

        @php
          $fixedOptions = [
            ['label' => 'Unknown', 'value' => ''],
            ['label' => 'Yes',     'value' => '1'],
            ['label' => 'No',      'value' => '0'],
          ];
        @endphp
        <x-ts-select.native label="Altered" :options="$fixedOptions" wire:model.defer="fixed" />
        <x-ts-input label="Color" wire:model.defer="color" />
        <x-ts-input label="Size" wire:model.defer="size" />
        <x-ts-input label="Microchip" wire:model.defer="microchip" />
      </div>
    </section>

    {{-- ===== Health ===== --}}
    <section class="kk-section">
      <div class="kk-section__head">
        <h2 class="kk-section__title">Health</h2>
      </div>
      <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <x-ts-input label="Heartworm" wire:model.defer="heartworm" />
        <x-ts-input label="FIV/L" wire:model.defer="fiv_l" />
        <x-ts-input label="FLV" wire:model.defer="flv" />
      </div>
    </section>

    {{-- ===== Home Compatibility ===== --}}
    <section class="kk-section">
      <div class="kk-section__head">
        <h2 class="kk-section__title">Home Compatibility</h2>
      </div>
      <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <x-ts-input label="Housetrained?" wire:model.defer="housetrained" />
        <x-ts-input label="Good with Dogs?" wire:model.defer="good_with_dogs" />
        <x-ts-input label="Good with Cats?" wire:model.defer="good_with_cats" />
        <x-ts-input label="Good with Children?" wire:model.defer="good_with_children" />
      </div>
    </section>

    {{-- ===== Notes ===== --}}
    <section class="kk-section">
      <div class="kk-section__head">
        <h2 class="kk-section__title">Notes</h2>
      </div>
      <div class="p-5">
        <x-ts-textarea label="Description" rows="5" wire:model.defer="description" />
        <p class="kk-help mt-1">Keep it objective and helpful for foster/adopter decisions.</p>
      </div>
    </section>

    {{-- Bottom action bar --}}
    <div class="kk-actionbar p-3 flex items-center justify-end gap-2">
      <a href="{{ route('dogs.show', $dog) }}" class="kk-btn">Cancel</a>
      <button type="submit" class="kk-btn kk-btn--primary">Save changes</button>
    </div>
  </form>
</div>
