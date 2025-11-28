@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Raleway', sans-serif; }
  h1, h2, h3, h4, h5, h6 { font-family: 'Playfair Display', serif; }
  .hard-corners * { border-radius: 0 !important; }
  [x-cloak]{ display:none !important; }
</style>
@endpush

@php
    // Brand palette
    $KK_NAVY     = '#03314C';   $KK_BLUE     = '#076BA8';   $KK_BLUE_ALT = '#DAEEFF';   $KK_BG_LIGHT = '#eaeaea';
    $KK_DIVIDER  = '#E2E8F0';   $KK_SUCCESS  = '#16A34A';   $KK_WARNING  = '#F59E0B';   $KK_DANGER   = '#DC2626';

    $badge = function($text, $bg, $fg, $border = null) {
        $border = $border ?: $bg;
        return "<span class=\"inline-block text-[11px] font-semibold px-2 py-0.5 border\" style=\"background:{$bg};color:{$fg};border-color:{$border};\">{$text}</span>";
    };

    $cards = [
        'sanctuary_rescue' => [
            'title' => 'Sanctuary / Rescue',
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"/></svg>',
            'desc'  => 'For brick-and-mortar rescues / sanctuaries managing dogs, staff and volunteers.',
            'you'   => 'rescue_admin',
            'can_invite' => ['rescue_staff','rescue_volunteer'],
        ],
        'foster_rescue' => [
            'title' => 'Foster-based Rescue',
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c1.657 0 3-1.567 3-3.5S13.657 1 12 1 9 2.567 9 4.5 10.343 8 12 8z"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 22v-2a7 7 0 0114 0v2"/></svg>',
            'desc'  => 'For organizations coordinating foster homes and transport with distributed teams.',
            'you'   => 'foster_admin',
            'can_invite' => ['foster_staff','foster_foster'],
        ],
        'adopter' => [
            'title' => 'Adopter',
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 14s1-1 4-1 4 1 4 1v2H4v-2z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 7a4 4 0 11-8 0 4 4 0 018 0zM16 14h4v6h-4v-6z"/></svg>',
            'desc'  => 'For individuals adopting a dog and tracking care details.',
            'you'   => 'adopter',
            'can_invite' => [],
        ],
    ];
@endphp

<div class="hard-corners min-h-screen py-12 px-4 sm:px-6 lg:px-8"
     style="color: {{ $KK_NAVY }}; background-image: linear-gradient(to bottom right, {{ $KK_BG_LIGHT }}, #ffffff 35%, {{ $KK_BLUE_ALT }} 100%);">

    <section class="max-w-3xl mx-auto border bg-white" style="border-color: {{ $KK_DIVIDER }};">
        {{-- Header --}}
        <div class="px-6 py-5 border-b" style="border-color: {{ $KK_DIVIDER }}; background: {{ $KK_BLUE_ALT }};">
            <h1 class="text-3xl font-extrabold">Quick Setup</h1>
            <p class="mt-1 text-sm" style="color: {{ $KK_NAVY }}B3">
                Choose your team type. We’ll set your initial role automatically. You can invite other roles after.
            </p>
        </div>

        {{-- Flash --}}
        @if (session('success'))
            <div class="mx-6 mt-4 rounded border border-green-200 bg-green-50 text-green-700 p-3">
                {{ session('success') }}
            </div>
        @endif

        {{-- Form --}}
        <form wire:submit.prevent="save" class="p-6 space-y-6">
            <div class="space-y-2">
                <label class="font-semibold">What best describes your account?</label>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach($cards as $value => $c)
                        @php $active = ($setup_type === $value); @endphp

                        <label
                            class="group relative cursor-pointer border p-4 flex flex-col gap-3"
                            style="border-color: {{ $active ? $KK_BLUE : $KK_DIVIDER }}; background: #fff; {{ $active ? 'box-shadow: 0 0 0 2px '.$KK_BLUE.'33 inset;' : '' }}"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-[{{ $active ? $KK_BLUE : $KK_NAVY }}]">{!! $c['icon'] !!}</span>
                                    <span class="font-bold">{{ $c['title'] }}</span>
                                </div>
                                <input type="radio" class="mt-1 size-4"
                                       wire:model.live="setup_type" value="{{ $value }}">
                            </div>

                            <p class="text-sm leading-5" style="color: {{ $KK_NAVY }}B3 }}">{{ $c['desc'] }}</p>

                            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs">
                                {!! $badge('You will be: '.$c['you'], $KK_BLUE_ALT, $KK_BLUE) !!}
                                @if(!empty($c['can_invite']))
                                    {!! $badge('Can invite', '#F8FAFC', '#374151', $KK_DIVIDER) !!}
                                    @foreach($c['can_invite'] as $role)
                                        {!! $badge($role, '#F8FAFC', '#374151', $KK_DIVIDER) !!}
                                    @endforeach
                                @else
                                    {!! $badge('No additional roles', '#F8FAFC', '#6B7280', $KK_DIVIDER) !!}
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>

                @error('setup_type')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>



            {{-- Actions (no Skip) --}}
            <div class="flex items-center justify-end gap-3">
                <button
                    class="px-4 py-2 font-semibold border disabled:opacity-60"
                    style="background: {{ $KK_BLUE }}; color:#fff; border-color: {{ $KK_BLUE }};"
                    @disabled(!$setup_type)
                    {{ $setup_type ? '' : 'disabled' }}
                >
                    Continue
                </button>
            </div>

            <p class="text-xs" style="color: {{ $KK_NAVY }}99">
                You can change this later in Team Settings if needed (existing member roles may need adjusting).
            </p>
        </form>
    </section>
</div>
