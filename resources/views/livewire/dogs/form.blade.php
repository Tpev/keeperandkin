{{-- resources/views/livewire/dogs/form.blade.php --}}
<form wire:submit.prevent="save" class="space-y-8">
    {{-- ====== Basic Info ====== --}}
    <div>
        <h3 class="text-base font-semibold text-gray-900">Basic Info</h3>
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <x-ts-input
                label="Name"
                placeholder="e.g., Daisy"
                wire:model.defer="name"
                required
            />

            <x-ts-input
                label="Breed"
                placeholder="e.g., Beagle mix"
                wire:model.defer="breed"
            />

            <x-ts-input
                label="Age (years)"
                type="number"
                min="0"
                max="30"
                step="0.1"
                wire:model.defer="age"
            />

            <x-ts-input
                label="Serial Number"
                placeholder="e.g., SN-2024-0001"
                wire:model.defer="serial_number"
            />

            <x-ts-input
                label="Location"
                placeholder="e.g., Cheyenne, WY"
                wire:model.defer="location"
            />

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

            <x-ts-input
                label="Approx. Date of Birth (US)"
                placeholder="MM/DD/YYYY"
                wire:model.defer="approx_dob"
            />
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
            </div>

            <x-ts-input
                label="Color"
                placeholder="e.g., Tricolor"
                wire:model.defer="color"
            />

            <x-ts-input
                label="Size"
                placeholder="e.g., Small / Medium / Large"
                wire:model.defer="size"
            />

            <x-ts-input
                label="Microchip"
                placeholder="e.g., 985112003421234"
                wire:model.defer="microchip"
            />
        </div>
    </div>

    {{-- ====== Photo Upload ====== --}}
    <div>
        <h3 class="text-base font-semibold text-gray-900">Photo</h3>
        <div class="mt-4 space-y-2">
            <label class="block text-sm font-medium text-gray-700">Main Photo</label>

            <input
                type="file"
                accept="image/*"
                wire:model="photo"
                class="block w-full text-sm file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-indigo-700 hover:file:bg-indigo-100
                       border rounded-md px-3 py-2"
            />

            @error('photo')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div
                x-data="{ isUploading: false, progress: 0 }"
                x-on:livewire-upload-start="isUploading = true"
                x-on:livewire-upload-finish="isUploading = false; progress = 0"
                x-on:livewire-upload-error="isUploading = false"
                x-on:livewire-upload-progress="progress = $event.detail.progress"
            >
                <div x-show="isUploading" class="w-full h-2 rounded bg-gray-200 overflow-hidden">
                    <div class="h-full bg-indigo-600 transition-all" :style="`width: ${progress}%;`"></div>
                </div>
            </div>

            @if ($photo)
                <div class="mt-3 flex items-start gap-3">
                    <img
                        src="{{ $photo->temporaryUrl() }}"
                        alt="Preview"
                        class="h-24 w-24 object-cover rounded-lg ring-1 ring-gray-200"
                    />
                    <div class="flex flex-col">
                        <span class="text-xs text-gray-600">Preview (not saved yet)</span>
                        <button
                            type="button"
                            wire:click="$set('photo', null)"
                            class="mt-2 inline-flex items-center rounded-md border px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Remove
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ====== Health ====== --}}
    <div>
        <h3 class="text-base font-semibold text-gray-900">Health</h3>
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <x-ts-input
                label="Heartworm"
                placeholder="e.g., Negative (tested 08/2024)"
                wire:model.defer="heartworm"
            />

            <x-ts-input
                label="FIV/L"
                placeholder="e.g., Negative"
                wire:model.defer="fiv_l"
            />

            <x-ts-input
                label="FLV"
                placeholder="e.g., Negative"
                wire:model.defer="flv"
            />
        </div>
    </div>

    {{-- ====== Home Compatibility ====== --}}
    <div>
        <h3 class="text-base font-semibold text-gray-900">Home Compatibility</h3>
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <x-ts-input
                label="Housetrained?"
                placeholder="e.g., Yes / Mostly / In progress"
                wire:model.defer="housetrained"
            />

            <x-ts-input
                label="Good with Dogs?"
                placeholder="e.g., Yes, with slow intros"
                wire:model.defer="good_with_dogs"
            />

            <x-ts-input
                label="Good with Cats?"
                placeholder="e.g., Unknown / No cats"
                wire:model.defer="good_with_cats"
            />

            <x-ts-input
                label="Good with Children?"
                placeholder="e.g., 10+ only"
                wire:model.defer="good_with_children"
            />
        </div>
    </div>

    {{-- ====== Description ====== --}}
    <div>
        <x-ts-textarea
            label="Description"
            placeholder="Notes about temperament, intake context, preferences…"
            rows="5"
            wire:model.defer="description"
        />
    </div>

    {{-- ===== Sticky save bar ===== --}}
    <div class="border-t pt-4 mt-6 flex items-center justify-end">
        <div class="flex items-center gap-3">
            <a href="{{ route('dogs.index') }}"
               class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold ring-1 transition"
               style="background:#FFFFFF; color:#03314C; ring-color:#E2E8F0;">
                Cancel
            </a>
            <x-ts-button type="submit" class="rounded-xl shadow"
                         style="background:#076BA8; border-color:#076BA8;">
                Save
            </x-ts-button>
        </div>
    </div>
</form>
