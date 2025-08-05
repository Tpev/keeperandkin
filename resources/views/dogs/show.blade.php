@php($teamName = optional(Auth::user()->currentTeam)->name)
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('dogs.index') }}" class="text-sm text-blue-600 hover:underline">← Back</a>
            <h2 class="text-xl font-semibold">{{ $dog->name }}</h2>
            @if($teamName)
                <span class="ml-auto text-sm text-gray-500">{{ $teamName }}</span>
            @endif
        </div>
    </x-slot>

    <div class="p-6 max-w-3xl mx-auto">
        {{-- Livewire component --}}
        <livewire:dogs.show :dog="$dog" />
    </div>
</x-app-layout>
