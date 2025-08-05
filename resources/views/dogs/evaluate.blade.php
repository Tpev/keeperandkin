<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Evaluate {{ $dog->name }}</h2>
    </x-slot>

    <div class="p-6 max-w-4xl mx-auto">
        <livewire:dogs.evaluation-form :dog="$dog" />
    </div>
</x-app-layout>
