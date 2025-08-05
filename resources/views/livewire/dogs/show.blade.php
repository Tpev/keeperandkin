<div>
    <x-ts-card class="space-y-4">

        <x-slot name="title">
            Basic Information
        </x-slot>
<x-ts-button href="{{ route('dogs.evaluate', $dog) }}" color="green">
    New Evaluation
</x-ts-button>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <p class="text-sm font-medium text-gray-500">Breed</p>
                <p class="text-lg">{{ $dog->breed ?? '—' }}</p>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-500">Age</p>
                <p class="text-lg">{{ $dog->age ? $dog->age.' yrs' : '—' }}</p>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-500">Sex</p>
                <p class="text-lg capitalize">{{ $dog->sex ?? '—' }}</p>
            </div>
        </div>

        <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Description</p>
            <p class="prose max-w-none">{{ $dog->description ?: 'No description yet.' }}</p>
        </div>

    </x-ts-card>

    <div class="mt-6">
        <x-ts-button href="{{ route('dogs.edit', $dog) }}" color="blue">
            Edit Dog
        </x-ts-button>
    </div>
</div>
