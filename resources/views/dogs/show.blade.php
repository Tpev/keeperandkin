@php($teamName = optional(Auth::user()->currentTeam)->name)
<x-app-layout>


   
        {{-- Livewire component --}}
        <livewire:dogs.show :dog="$dog" />

</x-app-layout>
