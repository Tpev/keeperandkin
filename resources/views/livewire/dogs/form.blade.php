{{-- resources/views/livewire/dogs/form.blade.php --}}
<div>
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
    --kk-success:#16A34A;
  }
  .kk *{ border-radius:0 !important; }          /* hard corners */
  .kk{ color:var(--kk-navy); }
  .kk h1,.kk h2,.kk h3,.kk h4{ font-family:'Playfair Display',serif; }
  .kk, .kk input, .kk select, .kk textarea, .kk label{ font-family:'Raleway',sans-serif; }

  .kk-section{ background:#fff; border:1px solid var(--kk-divider); }
  .kk-section__head{ background:var(--kk-blue-alt); padding:.75rem 1rem; border-bottom:1px solid var(--kk-divider); }
  .kk-section__title{ font-weight:800; letter-spacing:.2px; }
  .kk-muted{ color: color-mix(in oklab, var(--kk-navy) 70%, #000 0%); opacity:.9; }

  .kk-actionbar{ background:#fff; border:1px solid var(--kk-divider); }
  .kk-btn{ border:1px solid var(--kk-blue); color:var(--kk-blue); background:#fff; font-weight:700; padding:.6rem .9rem; }
  .kk-btn--primary{ background:var(--kk-blue); color:#fff; }

  .kk-help{ font-size:.75rem; color:#475569; }
  .kk-error{ font-size:.8rem; color:var(--kk-danger); }

  /* Upload progress */
  .kk-progress{ height:.5rem; background:#e5e7eb; overflow:hidden; border:1px solid var(--kk-divider);}
  .kk-progress__bar{ height:100%; background:var(--kk-blue); transition:width .2s ease; }
</style>
@endpush

<div class="kk min-h-screen py-8 px-4 sm:px-6 lg:px-8"
     style="background-image: linear-gradient(to bottom right, var(--kk-bg), #ffffff 35%, var(--kk-blue-alt) 100%);">

  {{-- Action bar --}}
  <div class="kk-actionbar max-w-7xl mx-auto mb-4 p-3 flex items-center justify-between gap-3">
    <div class="text-sm">
      <span class="font-semibold">Dogs</span>
      <span class="mx-2">/</span>
      <span class="opacity-80">Create</span>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('dogs.index') }}" class="kk-btn">← Cancel</a>
      <button form="dog-create-form" type="submit" class="kk-btn kk-btn--primary">Save</button>
    </div>
  </div>

  <form id="dog-create-form" wire:submit.prevent="save" class="max-w-7xl mx-auto space-y-8">

    {{-- ===== Basic Info ===== --}}
    <section class="kk-section">
      <div class="kk-section__head">
        <h2 class="kk-section__title">Basic Info</h2>
      </div>
      <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <x-ts-input label="Name" placeholder="e.g., Daisy" wire:model.defer="name" required />
        <x-ts-input label="Breed" placeholder="e.g., Beagle mix" wire:model.defer="breed" />
        <x-ts-input label="Age (years)" type="number" min="0" max="30" step="0.1" wire:model.defer="age" />
        <x-ts-input label="Serial Number" placeholder="e.g., SN-2024-0001" wire:model.defer="serial_number" />
        <x-ts-input label="Location" placeholder="e.g., Cheyenne, WY" wire:model.defer="location" />

        @php
          $sexOptions = [
            ['label' => 'Male',   'value' => 'male'],
            ['label' => 'Female', 'value' => 'female'],
          ];
        @endphp
        <x-ts-select.native
          label="Sex"
          :options="$sexOptions"
          wire:model="sex"
          :value="$sex ?? 'male'"
          :clearable="false"
        />

        <x-ts-input label="Approx. Date of Birth (US)" placeholder="MM/DD/YYYY" wire:model.defer="approx_dob" />

        <div class="sm:col-span-2 lg:col-span-1">
          @php
            $fixedOptions = [
              ['label' => 'Unknown', 'value' => ''],
              ['label' => 'Yes',     'value' => '1'],
              ['label' => 'No',      'value' => '0'],
            ];
          @endphp
          <x-ts-select.native
            label="Fixed"
            :options="$fixedOptions"
            wire:model="fixed"
            :clearable="true"
          />
          <p class="kk-help mt-1">Select sterilization status if known.</p>
        </div>

        <x-ts-input label="Color" placeholder="e.g., Tricolor" wire:model.defer="color" />
        <x-ts-input label="Size" placeholder="e.g., Small / Medium / Large" wire:model.defer="size" />
        <x-ts-input label="Microchip" placeholder="e.g., 985112003421234" wire:model.defer="microchip" />
      </div>
    </section>

    {{-- ===== Photo Upload ===== --}}
    <section class="kk-section">
      <div class="kk-section__head">
        <h2 class="kk-section__title">Photo</h2>
      </div>
      <div class="p-5 space-y-3">
        <label class="block text-sm font-semibold">Main Photo</label>

        <input
          type="file"
          accept="image/*"
          wire:model="photo"
          class="block w-full text-sm file:mr-4 file:border-0 file:bg-[var(--kk-blue-alt)] file:px-3 file:py-2 file:font-semibold file:text-[var(--kk-blue)]
                 border border-[var(--kk-divider)] px-3 py-2"
        />

        @error('photo')
          <p class="kk-error">{{ $message }}</p>
        @enderror

        <div
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

        @if ($photo)
          <div class="mt-3 flex items-start gap-3">
            <img src="{{ $photo->temporaryUrl() }}" alt="Preview"
                 class="h-24 w-24 object-cover border"
                 style="border-color: var(--kk-blue-alt);" />
            <div class="flex flex-col">
              <span class="text-xs kk-muted">Preview (not saved yet)</span>
              <button type="button" wire:click="$set('photo', null)"
                      class="kk-btn mt-2">Remove</button>
            </div>
          </div>
        @endif
      </div>
    </section>

    {{-- ===== Health ===== --}}
    <section class="kk-section">
      <div class="kk-section__head">
        <h2 class="kk-section__title">Health</h2>
      </div>
      <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <x-ts-input label="Heartworm" placeholder="e.g., Negative (tested 08/2024)" wire:model.defer="heartworm" />
        <x-ts-input label="FIV/L" placeholder="e.g., Negative" wire:model.defer="fiv_l" />
        <x-ts-input label="FLV" placeholder="e.g., Negative" wire:model.defer="flv" />
      </div>
    </section>

    {{-- ===== Home Compatibility ===== --}}
    <section class="kk-section">
      <div class="kk-section__head">
        <h2 class="kk-section__title">Home Compatibility</h2>
      </div>
      <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <x-ts-input label="Housetrained?" placeholder="e.g., Yes / Mostly / In progress" wire:model.defer="housetrained" />
        <x-ts-input label="Good with Dogs?" placeholder="e.g., Yes, with slow intros" wire:model.defer="good_with_dogs" />
        <x-ts-input label="Good with Cats?" placeholder="e.g., Unknown / No cats" wire:model.defer="good_with_cats" />
        <x-ts-input label="Good with Children?" placeholder="e.g., 10+ only" wire:model.defer="good_with_children" />
      </div>
    </section>

    {{-- ===== Description ===== --}}
    <section class="kk-section">
      <div class="kk-section__head">
        <h2 class="kk-section__title">Notes</h2>
      </div>
      <div class="p-5">
        <x-ts-textarea
          label="Description"
          placeholder="Notes about temperament, intake context, preferences…"
          rows="5"
          wire:model.defer="description"
        />
        <p class="kk-help mt-1">Keep it objective and helpful for foster/adopter decisions.</p>
      </div>
    </section>

    {{-- Sticky save bar clone (for long forms) --}}
    <div class="kk-actionbar p-3 flex items-center justify-end gap-2">
      <a href="{{ route('dogs.index') }}" class="kk-btn">Cancel</a>
      <x-ts-button type="submit" class="kk-btn kk-btn--primary">Save</x-ts-button>
    </div>

  </form>
</div>
</div>