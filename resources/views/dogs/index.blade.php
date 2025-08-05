<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">Dogs</h2>

            {{-- NEW button --}}
            <x-ts-button href="{{ route('dogs.create') }}">
                + Add Dog
            </x-ts-button>
        </div>
    </x-slot>

    <div class="p-6">
        <livewire:dogs.table />
    </div>
</x-app-layout>
