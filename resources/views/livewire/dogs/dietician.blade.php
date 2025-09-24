{{-- resources/views/livewire/dogs/dietician.blade.php --}}
@php
    use Illuminate\Support\Carbon;
    $KK_NAVY     = '#03314C';
    $KK_BLUE     = '#076BA8';
    $KK_BLUE_ALT = '#DAEEFF';
    $KK_DIVIDER  = '#E2E8F0';
@endphp

<section class="max-w-7xl mx-auto mt-12 border" style="background: #F7FEE7; border-color: {{ $KK_DIVIDER }};">
    {{-- Section header --}}
    <div class="px-6 py-4" style="background:#fff; border-bottom:1px solid {{ $KK_DIVIDER }}">
        <h2 class="text-xl font-bold" style="color: {{ $KK_NAVY }}">Dietetician</h2>
    </div>

    {{-- Summary chips (from profile if present) --}}
    @php
        $p = $dog->dietProfile;
        $brand = $p?->food_brand; $name = $p?->food_name; $type = $p?->food_type;
        $kcal = $p?->daily_calories; $meals = $p?->meals_per_day; $portion = $p?->portion_grams_per_meal;
    @endphp

    <div class="px-6 pt-6">
        <div class="flex flex-wrap gap-2 mb-8">
            <span class="inline-flex items-center gap-2 text-xs font-semibold text-white px-3 py-1 border"
                  style="background: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }};">
                {{ $brand || $name ? trim(($brand ?? '').' '.($name ?? '')) : 'Diet not set' }}
            </span>
            @if($type)
                <span class="inline-flex items-center gap-2 text-xs font-semibold text-white px-3 py-1 border"
                      style="background:#22C55E; border-color:#22C55E;">
                    {{ ucfirst($type) }}
                </span>
            @endif
            @if($kcal)
                <span class="inline-flex items-center gap-2 text-xs font-semibold text-white px-3 py-1 border"
                      style="background:#0EA5E9; border-color:#0EA5E9;">
                    {{ $kcal }} kcal/day
                </span>
            @endif
            @if($meals)
                <span class="inline-flex items-center gap-2 text-xs font-semibold text-white px-3 py-1 border"
                      style="background:#A855F7; border-color:#A855F7;">
                    {{ $meals }} meals/day
                </span>
            @endif
            @if($portion)
                <span class="inline-flex items-center gap-2 text-xs font-semibold text-white px-3 py-1 border"
                      style="background:#F97316; border-color:#F97316;">
                    {{ number_format($portion,1) }} g/meal
                </span>
            @endif
        </div>
    </div>

    {{-- Profile form --}}
    <div class="px-6">
        <div class="border mb-8" style="background:#fff; border-color: {{ $KK_DIVIDER }};">
            <div class="px-4 py-3" style="background: {{ $KK_BLUE_ALT }}; border-bottom:1px solid {{ $KK_DIVIDER }}">
                <h3 class="font-semibold" style="color: {{ $KK_NAVY }}">Diet profile</h3>
            </div>

            <div class="p-6">
                <form wire:submit.prevent="saveProfile" class="grid grid-cols-1 sm:grid-cols-6 gap-5">
                    <x-ts-input label="Food brand" wire:model.defer="food_brand" class="sm:col-span-2" placeholder="e.g., Acme" />
                    <x-ts-input label="Food name" wire:model.defer="food_name" class="sm:col-span-2" placeholder="e.g., Adult Salmon" />
                    <x-ts-select.native
                        label="Food type"
                        class="sm:col-span-2"
                        :options="[
                            ['label'=>'Kibble','value'=>'kibble'],
                            ['label'=>'Wet','value'=>'wet'],
                            ['label'=>'Raw','value'=>'raw'],
                            ['label'=>'Home-cooked','value'=>'home-cooked'],
                        ]"
                        placeholder="—"
                        wire:model.defer="food_type"
                    />

                    <x-ts-input label="Daily calories (kcal)" type="number" min="0" max="5000" wire:model.defer="daily_calories" class="sm:col-span-2" />
                    <x-ts-input label="Meals per day" type="number" min="1" max="6" wire:model.defer="meals_per_day" class="sm:col-span-2" />
                    <x-ts-input label="Portion per meal (g)" type="number" step="0.1" min="0" wire:model.defer="portion_grams_per_meal" class="sm:col-span-2" />

                    <x-ts-input label="Allergies (comma-separated)" wire:model.defer="allergies_csv" class="sm:col-span-3" placeholder="chicken, beef" />
                    <x-ts-input label="Supplements (comma-separated)" wire:model.defer="supplements_csv" class="sm:col-span-3" placeholder="omega-3, probiotic" />

                    <x-ts-textarea label="Notes" rows="3" wire:model.defer="notes" class="sm:col-span-6" />
                    <x-ts-input label="Last reviewed on" type="date" wire:model.defer="last_reviewed_at" class="sm:col-span-3" />

                    <div class="sm:col-span-6 flex justify-end">
                        <button type="submit"
                                class="px-4 py-2 text-sm font-semibold border text-white"
                                style="background: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }};">
                            Save profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Toggle Feeding Log --}}
    <div class="px-6 text-center">
        <button x-data @click="$dispatch('toggle-diet-entries')"
                class="px-3 py-1 text-sm font-semibold border"
                style="color: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }}; background:#fff;">
            {{ $showEntries ? 'Hide feeding log' : 'Show feeding log' }}
        </button>
    </div>

    <div
        x-data="{ open: @entangle('showEntries') }"
        x-on:toggle-diet-entries.window="open = !open"
        x-show="open" x-cloak
        class="px-6 mt-6">

        {{-- Add entry --}}
        <div class="border mb-6" style="background:#fff; border-color: {{ $KK_DIVIDER }};">
            <div class="px-4 py-3" style="background: {{ $KK_BLUE_ALT }}; border-bottom:1px solid {{ $KK_DIVIDER }}">
                <h3 class="font-semibold" style="color: {{ $KK_NAVY }}">Add feeding entry</h3>
            </div>

            <div class="p-6">
                <form wire:submit.prevent="addEntry" class="grid grid-cols-1 sm:grid-cols-6 gap-5">
                    <x-ts-input label="When" type="datetime-local" wire:model.defer="fed_at" class="sm:col-span-2" />
                    <x-ts-input label="Meal" wire:model.defer="meal" class="sm:col-span-1" placeholder="breakfast" />
                    <x-ts-input label="Food (optional)" wire:model.defer="entry_food" class="sm:col-span-3" placeholder="if different from profile" />
                    <x-ts-input label="Grams" type="number" step="0.1" min="0" wire:model.defer="grams" class="sm:col-span-2" />
                    <x-ts-input label="Calories (kcal)" type="number" min="0" wire:model.defer="calories" class="sm:col-span-2" />
                    <x-ts-select.native
                        label="Appetite"
                        class="sm:col-span-2"
                        :options="[
                            ['label'=>'1 - Poor', 'value'=>1],
                            ['label'=>'2', 'value'=>2],
                            ['label'=>'3 - Normal', 'value'=>3],
                            ['label'=>'4', 'value'=>4],
                            ['label'=>'5 - Great', 'value'=>5],
                        ]"
                        placeholder="—"
                        wire:model.defer="appetite"
                    />
                    <x-ts-textarea label="Comment" rows="2" wire:model.defer="comment" class="sm:col-span-6" />

                    <div class="sm:col-span-6 flex justify-end">
                        <button type="submit"
                                class="px-4 py-2 text-sm font-semibold border text-white"
                                style="background: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }};">
                            Add entry
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Entries table --}}
        <div class="border" style="background:#fff; border-color: {{ $KK_DIVIDER }};">
            <div class="px-4 py-3" style="background: {{ $KK_BLUE_ALT }}; border-bottom:1px solid {{ $KK_DIVIDER }}">
                <h3 class="font-semibold" style="color: {{ $KK_NAVY }}">Feeding log</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm" style="border-collapse: collapse;">
                    <thead>
                        <tr style="background: {{ $KK_BLUE_ALT }}; border-bottom:1px solid {{ $KK_DIVIDER }};">
                            <th class="py-2 px-3 text-left">When</th>
                            <th class="py-2 px-3 text-left">Meal</th>
                            <th class="py-2 px-3 text-left">Food</th>
                            <th class="py-2 px-3 text-left">Grams</th>
                            <th class="py-2 px-3 text-left">kcal</th>
                            <th class="py-2 px-3 text-left">Appetite</th>
                            <th class="py-2 px-3 text-right"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dog->dietEntries as $e)
                            <tr style="border-top:1px solid {{ $KK_DIVIDER }};">
                                <td class="py-2 px-3 font-medium">{{ $e->fed_at->format('M d, Y H:i') }}</td>
                                <td class="py-2 px-3">{{ $e->meal ?? '—' }}</td>
                                <td class="py-2 px-3">{{ $e->food ?? '—' }}</td>
                                <td class="py-2 px-3">{{ $e->grams ? number_format($e->grams,1) : '—' }}</td>
                                <td class="py-2 px-3">{{ $e->calories ?? '—' }}</td>
                                <td class="py-2 px-3">{{ $e->appetite ?? '—' }}</td>
                                <td class="py-2 px-3 text-right">
                                    <button wire:click="deleteEntry({{ $e->id }})"
                                            class="px-3 py-1 text-sm font-semibold border"
                                            style="background:#fff; border-color:#DC2626; color:#DC2626;">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr style="border-top:1px solid {{ $KK_DIVIDER }};">
                                <td class="py-3 px-3 text-sm" colspan="7" style="color:#6B7280;">No feeding entries yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>

@push('styles')
<style>[x-cloak]{ display:none !important; }</style>
@endpush
