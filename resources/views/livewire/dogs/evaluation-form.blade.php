{{-- resources/views/livewire/dogs/evaluation-form.blade.php --}}
<form wire:submit.prevent="submit" class="space-y-8">

    {{-- Live progress ------------------------------------------------------------------}}
    <x-ts-progress :value="$this->scorePercent" class="mb-4" />
    <p class="text-sm text-gray-500 mb-6">
        Current score:
        <span class="font-semibold">{{ $this->liveScore }}</span>
        /
        {{ $this->maxScore }}
    </p>

    {{-- Categories & Questions -------------------------------------------------------- --}}
    @foreach($this->catalog as $cat => $questions)
        <h3 class="text-lg font-semibold mb-2">{{ $cat }}</h3>

        <x-ts-card class="mb-8">
            <div class="space-y-4">
                @foreach($questions as $qKey => $q)
                    <div>
                        <p class="font-medium mb-2">{{ $q['text'] }}</p>

                        <div class="flex flex-wrap items-center gap-4">
                            @foreach($q['options'] as $optKey => $weight)
                                <label class="flex items-center gap-1">
                                    <input type="radio"
                                           wire:model="answers.{{ $qKey }}"
                                           value="{{ $optKey }}"
                                           class="ts-radio">
                                    <span class="text-sm capitalize">{{ $optKey }}</span>
                                </label>
                            @endforeach
                        </div>

                        @error("answers.$qKey")
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>
        </x-ts-card>
    @endforeach

    {{-- Submit ------------------------------------------------------------------------ --}}
    <x-ts-button type="submit">Save Evaluation</x-ts-button>
</form>
