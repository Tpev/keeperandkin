<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('dogs.index') }}" class="text-sm text-blue-600 hover:underline">← Back</a>
            <h2 class="text-xl font-semibold">Edit Dog: {{ $dog->name }}</h2>
        </div>
    </x-slot>

    <div class="p-6 max-w-xl">
        <livewire:dogs.edit :dog="$dog" />
    </div>
</x-app-layout>
