<form wire:submit.prevent="update" class="space-y-4">
    <x-ts-input  label="Name"          wire:model.defer="name"  required />
    <x-ts-input  label="Breed"         wire:model.defer="breed" />
    <x-ts-input  label="Age (years)"   type="number" min="0" max="30" wire:model.defer="age" />

    <x-ts-select.native
        label="Sex"
        :options="[
            ['label' => 'Male',   'value' => 'male'],
            ['label' => 'Female', 'value' => 'female'],
        ]"
        placeholder="—"
        wire:model.defer="sex"
    />

    <x-ts-textarea label="Description" wire:model.defer="description" />

    <x-ts-button type="submit">Save changes</x-ts-button>
</form>
