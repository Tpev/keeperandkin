{{-- resources/views/dogs/show-upgraded.blade.php --}}
{{-- Keeper & Kin – Dog Profile (4 Tabs: Overview, Training, Gallery, Health) --}}

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Raleway', sans-serif; }
  h1, h2, h3, h4, h5, h6 { font-family: 'Playfair Display', serif; }
  .hard-corners * { border-radius: 0 !important; }
  @keyframes shimmer{0%{background-position:0% 50%}100%{background-position:200% 50%}}
  .text-shadow-white{ text-shadow:0 0 2px #fff }
  [x-cloak]{ display:none !important; }
</style>
@endpush

@php
    use Illuminate\Support\Str;

    // --- Brand palette ---
    $KK_NAVY     = '#03314C';   $KK_BLUE     = '#076BA8';   $KK_BLUE_ALT = '#DAEEFF';   $KK_BG_LIGHT = '#eaeaea';
    $KK_DIVIDER  = '#E2E8F0';   $KK_SUCCESS  = '#16A34A';   $KK_WARNING  = '#F59E0B';   $KK_DANGER   = '#DC2626';

    // Score scale
    $SCALE_OK     = '#94A3B8'; $SCALE_RED    = '#DC2626'; $SCALE_ORANGE = '#F97316'; $SCALE_YELLOW = '#FFCC00'; $SCALE_GREEN  = '#16A34A';

    // Dog basics
    $name       = $dog->name ?? 'Unnamed';
    $uid        = 'DOG-'.str_pad((string)($dog->id ?? 0), 4, '0', STR_PAD_LEFT);
    $breed      = $dog->breed ?? 'Mixed';
    $sex        = $dog->sex ? Str::ucfirst($dog->sex) : '—';
    $age        = $dog->age ? $dog->age.' yrs' : '—';
    $status     = $dog->status ?? 'Available';
    $intakeDate = $dog->created_at ? $dog->created_at->toDateString() : now()->toDateString();
    $photoCol   = $dog->photo ?? $dog->photo_path ?? null;
    $photo      = $photoCol ? asset('storage/'.$photoCol) : 'https://placehold.co/448x448?text=Dog';
    $photoUrl   = $dog->photo_url ?? $photo;

    $latestEval = $dog->latestEvaluation ?? null;

    // Active tab via ?tab=overview|training|gallery|health
    $activeTabKey = request('tab', 'overview');
    $tabMap = [
        'overview' => 'Overview',
        'training' => 'Training',
        'gallery'  => 'Gallery',
        'health'   => 'Health',
    ];
    $activeTabLabel = $tabMap[$activeTabKey] ?? 'Overview';

    // --- Evaluation helpers
    $catsRaw = is_array($latestEval?->category_scores ?? null) ? $latestEval->category_scores : [];

    $val = function(array $a, array $keys, $default = null) {
        foreach ($keys as $k) if (array_key_exists($k, $a) && $a[$k] !== null && $a[$k] !== '') return (int) $a[$k];
        return $default;
    };

    $colorFor = function($v) use ($SCALE_OK,$SCALE_RED,$SCALE_ORANGE,$SCALE_YELLOW,$SCALE_GREEN) {
        if (!is_numeric($v)) return $SCALE_OK;
        $v = (int)$v;
        if ($v <= 25) return $SCALE_RED;
        if ($v <= 50) return $SCALE_ORANGE;
        if ($v <= 75) return $SCALE_YELLOW;
        return $SCALE_GREEN;
    };

    $LABEL_CC = 'Comfort & Confidence'; $LABEL_SO = 'Sociability'; $LABEL_TR = 'Trainability';

    $cc = $val($catsRaw, ['Comfort & Confidence','Confidence','comfort_confidence']);
    $so = $val($catsRaw, ['Sociability','Social','sociability']);
    $tr = $val($catsRaw, ['Trainability','trainability']);

    $hasScores = $latestEval && ($cc !== null || $so !== null || $tr !== null);

    $categories = [
        $LABEL_CC => $hasScores ? $cc : null,
        $LABEL_SO => $hasScores ? $so : null,
        $LABEL_TR => $hasScores ? $tr : null,
    ];

    $barWidth  = fn($v) => is_numeric($v) ? max(0, min(100, (int)$v)) : 0;
    $barColor  = fn($v) => $colorFor($v);

    $tileIcons = [
        'comfort-confidence'=>'<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6l7 4v3a9 9 0 11-14 0V10l7-4z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>',
        'sociability'=>'<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M9 20H4v-2a3 3 0 015.356-1.857M15 11a3 3 0 110-6 3 3 0 010 6z"/><path stroke-linecap="round" stroke-linejoin="round" d="M7 11a3 3 0 110-6 3 3 0 010 6z"/></svg>',
        'trainability'=>'<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422A12.083 12.083 0 0119 13.714L12 21l-7-7.286a12.083 12.083 0 01-.84-3.136L12 14z"/></svg>',
    ];

    $approxDobUs = $dog->approx_dob ? \Illuminate\Support\Carbon::parse($dog->approx_dob)->format('m/d/Y') : '—';
    $fixedHuman  = is_null($dog->fixed) ? 'Unknown' : ($dog->fixed ? 'Yes' : 'No');

    // History
    $evals = $dog->evaluations()->orderBy('created_at')->get();
    $history = $evals->map(function ($e) {
        $cs = (array) ($e->category_scores ?? []);
        $pick = function(array $a, array $keys) { foreach ($keys as $k) if (array_key_exists($k, $a) && $a[$k] !== null) return (int) $a[$k]; return 0; };
        return [
            'id'   => $e->id,
            'date' => $e->created_at,
            'cc'   => $pick($cs, ['Comfort & Confidence','Confidence','comfort_confidence']),
            'so'   => $pick($cs, ['Sociability','Social','sociability']),
            'tr'   => $pick($cs, ['Trainability','trainability']),
        ];
    });

    $original = $history->first();
    $latest   = $history->last();

    // Training badges (placeholder)
    $trainingTotal = 0; $trainingDone = 0;
@endphp

<div class="hard-corners min-h-screen font-sans py-12 px-4 sm:px-6 lg:px-8"
     style="color: {{ $KK_NAVY }}; background-image: linear-gradient(to bottom right, {{ $KK_BG_LIGHT }}, #ffffff 35%, {{ $KK_BLUE_ALT }} 100%);">

    @php
        $pendingTransfer = \App\Models\DogTransfer::where('dog_id', $dog->id)->where('status','pending')->first();
    @endphp

    @if($pendingTransfer)
        <div class="mb-4 p-3 rounded border border-amber-300 bg-amber-50">
            <div class="flex justify-between items-center">
                <div>
                    <strong>In Transfer:</strong> Invite sent to {{ $pendingTransfer->to_email }}.
                    Expires {{ $pendingTransfer->expires_at->diffForHumans() }}.
                </div>
                <form method="POST" action="{{ route('transfers.cancel', $pendingTransfer) }}">
                    @csrf @method('DELETE')
                    <button class="px-3 py-1 rounded bg-red-600 text-white">Cancel</button>
                </form>
            </div>
        </div>
    @endif

    {{-- Action bar --}}
    <section class="max-w-7xl mx-auto mb-4 border" style="background:#fff; border-color: {{ $KK_DIVIDER }};">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4">
            <div class="flex items-center gap-3 text-sm">
                <a href="{{ route('dogs.index') }}" class="inline-flex items-center px-2 py-1 border font-medium"
                   style="color: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }};"> ← All Dogs </a>
                <div class="hidden md:block h-5 w-px" style="background: {{ $KK_DIVIDER }};"></div>
                <div class="font-semibold" style="color: {{ $KK_NAVY }};">
                    {{ $dog->name ?? 'Dog' }}
                    <span class="ml-2 text-xs font-mono px-1.5 py-0.5 border"
                          style="color: {{ $KK_BLUE }}; border-color: {{ $KK_DIVIDER }};">{{ $uid }}</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dogs.evaluate', $dog) }}" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold border"
                   style="background: {{ $KK_BLUE }}; color: #fff; border-color: {{ $KK_BLUE }};">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:#fff">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    New Evaluation
                </a>

                <a href="{{ route('dogs.edit', $dog) }}" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold border"
                   style="background:#fff; color: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }};">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: {{ $KK_BLUE }}">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M4 20h4l10.5-10.5a2.5 2.5 0 10-3.536-3.536L4 16v4z"/>
                    </svg>
                    Edit Dog
                </a>
                <a href="{{ route('dogs.pdf.overview', $dog) }}"
                   class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold border"
                   style="background:#fff; color: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }};"
                   target="_blank" rel="noopener">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: {{ $KK_BLUE }}">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V4h12v5M6 14H5a2 2 0 01-2-2V9a2 2 0 012-2h14a2 2 0 012 2v3a2 2 0 01-2 2h-1M6 18h12v2H6zM8 14h8v4H8z"/>
                    </svg>
                    Print PDF
                </a>

                @livewire('dogs.transfer-initiator', ['dog' => $dog], key('transfer-'.$dog->id))

                <div class="inline-flex">
                    <livewire:dogs.delete-button :dog="$dog"
                        :button-classes="'inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold border'"
                        :button-style="'background:#FFFFFF; color:#DC2626; border-color:#DC2626;'" />
                </div>
            </div>
        </div>
    </section>

    {{-- Sticky Tab Bar + Panels (TallStackUI) --}}
    <div class="sticky top-0 z-30 border-b bg-white/90 backdrop-blur" style="border-color: {{ $KK_DIVIDER }};">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">

            {{-- 4 tabs now --}}
            <script>
                window.__kkTabMap = {
                    Overview: 'overview',
                    Training: 'training',
                    Gallery: 'gallery',
                    Health: 'health',
                };
            </script>

            <x-ts-tab :selected="$activeTabLabel" x-on:navigate="
                (e) => {
                  const map = window.__kkTabMap || {};
                  const key = map[e.detail.select] || 'overview';
                  const url = new URL(window.location);
                  url.searchParams.set('tab', key);
                  window.history.replaceState({}, '', url);
                  window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            " scroll-on-mobile>

                {{-- OVERVIEW: Profile + Scorecard + Notes + Care Team Notes + History --}}
                <x-ts-tab.items tab="Overview">
                    {{-- Profile section --}}
                    <section class="max-w-7xl mx-auto p-8 md:p-12 border mt-4"
                             style="background: rgba(255,255,255,0.9); border-color: {{ $KK_DIVIDER }};">

                        {{-- HERO: 2 columns -> left = photo, right = identity/info --}}
                        <div class="flex flex-col md:flex-row gap-10 items-start">
                            {{-- Left: Photo --}}
                            <div class="md:w-1/2 flex justify-center md:justify-start">
                                <img src="{{ $photoUrl }}" alt="{{ $name }}"
                                     class="w-48 h-48 md:w-72 md:h-72 object-cover border"
                                     style="border-color: {{ $KK_BLUE_ALT }};" loading="lazy">
                            </div>

                            {{-- Right: Name + meta + red flags --}}
                            <div class="md:w-1/2 space-y-3">
                                <div class="space-y-2">
                                    <h1 class="text-4xl font-extrabold flex flex-wrap items-baseline gap-3">
                                        <span>{{ $name }}</span>
                                        <span class="text-xs font-mono px-2 py-0.5 border"
                                              style="background: {{ $KK_BLUE_ALT }}; color: {{ $KK_BLUE }}; border-color: {{ $KK_DIVIDER }};">
                                            {{ $uid }}
                                        </span>
                                    </h1>

                                    @if ($dog->serial_number)
                                        <p class="text-sm font-mono flex items-center gap-2">
                                            <span class="font-semibold text-gray-700">Serial No:</span>
                                            <span class="px-2 py-0.5 border bg-white text-gray-800" style="border-color: {{ $KK_DIVIDER }};">
                                                {{ $dog->serial_number }}
                                            </span>
                                        </p>
                                    @endif

                                    <p class="text-sm uppercase tracking-wide font-semibold flex items-center gap-2"
                                       style="color: {{ $KK_NAVY }}CC">
                                        {!! $tileIcons['trainability'] !!} {{ $breed }} · {{ $sex }} · {{ $age }}
                                    </p>

                                    <p class="text-sm leading-5">
                                        <span class="font-medium">Intake:</span>
                                        {{ \Carbon\Carbon::parse($intakeDate)->format('M d, Y') }} ·
                                        <span class="font-medium">Status:</span>
                                        <span class="inline-block px-2 py-0.5 border"
                                              style="background:#DEF7EC; color:#03543F; border-color:#C7F3E3;">
                                            {{ $status }}
                                        </span>
                                    </p>
                                </div>

                                @php
                                    $latestEval = $latestEval ?? ($dog->latestEvaluation ?? null);
                                    $redFlags   = is_array($latestEval?->red_flags ?? null) ? $latestEval->red_flags : [];
                                @endphp

                                <div class="flex flex-wrap gap-2 mt-1">
                                    @forelse ($redFlags as $flag)
                                        <span class="inline-block text-xs font-semibold px-3 py-1 border"
                                              style="background: {{ $KK_DANGER }}; color: white; border-color: {{ $KK_DANGER }};">
                                            {{ \Illuminate\Support\Str::headline($flag) }}
                                        </span>
                                    @empty
                                        <span class="inline-block text-xs px-3 py-1 border"
                                              style="background:#F8FAFC; color:#374151; border-color: {{ $KK_DIVIDER }};">
                                            No red flags
                                        </span>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        {{-- Everything else under hero --}}
                        <div class="mt-8 space-y-6">
                            {{-- Profile details --}}
                            <div class="border" style="border-color: {{ $KK_DIVIDER }};">
                                <div class="px-4 py-3" style="background: {{ $KK_BLUE_ALT }};">
                                    <h2 class="text-lg font-bold">Profile</h2>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                                    @php
                                        $row = function($label, $value) use ($KK_DIVIDER, $KK_NAVY) {
                                            $val = filled($value) ? e($value) : '—';
                                            return <<<HTML
                                                <div class="flex flex-col border-t" style="border-color: {$KK_DIVIDER};">
                                                    <div class="px-4 pt-3 text-xs uppercase tracking-wide font-semibold" style="color: {$KK_NAVY}B3">{$label}</div>
                                                    <div class="px-4 pb-3 text-sm">{$val}</div>
                                                </div>
                                            HTML;
                                        };
                                    @endphp

                                    {!! $row('Location', $dog->location) !!}
                                    {!! $row('Approx. Date of Birth', $approxDobUs) !!}
                                    {!! $row('Altered', $fixedHuman) !!}
                                    {!! $row('Color', $dog->color) !!}
                                    {!! $row('Size', $dog->size) !!}
                                    {!! $row('Microchip', $dog->microchip) !!}
                                    {!! $row('Heartworm', $dog->heartworm) !!}
                                    {!! $row('FIV/L', $dog->fiv_l) !!}
                                    {!! $row('FLV', $dog->flv) !!}
                                    {!! $row('Housetrained?', $dog->housetrained) !!}
                                    {!! $row('Good with Dogs?', $dog->good_with_dogs) !!}
                                    {!! $row('Good with Cats?', $dog->good_with_cats) !!}
                                    {!! $row('Good with Children?', $dog->good_with_children) !!}
                                </div>
                            </div>

                            {{-- Scorecard --}}
                            <div class="border" style="border-color: {{ $KK_DIVIDER }};">
                                <div class="px-4 py-3" style="background: {{ $KK_BLUE_ALT }};">
                                    <div class="flex items-center justify-between">
                                        <h2 class="text-lg font-bold">Scorecard</h2>
                                        <div class="hidden sm:flex items-center gap-3 text-xs">
                                            <span class="inline-flex items-center gap-1">
                                                <span class="inline-block w-3 h-3 border" style="background: {{ $SCALE_RED }}; border-color: {{ $SCALE_RED }}"></span>
                                                Red (0–25)
                                            </span>
                                            <span class="inline-flex items-center gap-1">
                                                <span class="inline-block w-3 h-3 border" style="background: {{ $SCALE_ORANGE }}; border-color: {{ $SCALE_ORANGE }}"></span>
                                                Orange (26–50)
                                            </span>
                                            <span class="inline-flex items-center gap-1">
                                                <span class="inline-block w-3 h-3 border" style="background: {{ $SCALE_YELLOW }}; border-color: {{ $SCALE_YELLOW }}"></span>
                                                Yellow (51–75)
                                            </span>
                                            <span class="inline-flex items-center gap-1">
                                                <span class="inline-block w-3 h-3 border" style="background: {{ $SCALE_GREEN }}; border-color: {{ $SCALE_GREEN }}"></span>
                                                Green (76–100)
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid gap-0 sm:grid-cols-3">
                                    @foreach($categories as $lbl=>$valx)
                                        @php
                                            $key    = Str::slug($lbl);
                                            $icon   = $tileIcons[$key] ?? '';
                                            $barClr = $barColor($valx);
                                            $width  = $barWidth($valx);
                                            $text   = is_numeric($valx) ? ($valx.' / 100') : '—';
                                        @endphp
                                        <div class="p-4 border-t sm:border-l" style="border-color: {{ $KK_DIVIDER }}; background: #fff;">
                                            <p class="text-sm font-semibold flex items-center gap-2" style="color: {{ $KK_NAVY }}">
                                                {!! $icon !!} {{ $lbl }}
                                            </p>
                                            <div class="h-3 w-full overflow-hidden mt-2 border" style="background: {{ $KK_DIVIDER }}; border-color: {{ $KK_DIVIDER }};">
                                                <div class="h-full" style="background: {{ $barClr }}; width: {{ $width }}%"></div>
                                            </div>
                                            <p class="text-right mt-1 text-sm font-medium" style="color:{{ $barClr }}">{{ $text }}</p>
                                        </div>
                                    @endforeach
                                </div>

                                
                            </div>
<livewire:dogs.adoption-checklist :dog="$dog" :key="'adoption-checklist-'.$dog->id" />

                            {{-- Simple Notes (dog description) --}}
                            @if($dog->description)
                                <div class="border p-4" style="border-color: {{ $KK_DIVIDER }}; background: #fff;">
                                    <h3 class="text-base font-semibold mb-2">Notes</h3>
                                    <p class="text-sm leading-6" style="color: {{ $KK_NAVY }}CC">{{ $dog->description }}</p>
                                </div>
                            @endif

                            {{-- Care Team Notes (Livewire component) --}}
                            <div class="border" style="border-color: {{ $KK_DIVIDER }}; background:#fff;">
                                <div class="px-4 py-3 border-b" style="background: {{ $KK_BLUE_ALT }}; border-color: {{ $KK_DIVIDER }}">
                                    <h2 class="text-lg font-bold">Care Team Notes</h2>
                                </div>
                                <div class="p-4">
                                    <livewire:dogs.care-notes :dog="$dog" :key="'notes-'.$dog->id" />
                                </div>
                            </div>

                            {{-- Evaluation History (aligned with other cards) --}}
                            <div class="border" style="border-color: {{ $KK_DIVIDER }}; background:#fff;">
                                <div class="px-4 py-3 border-b" style="background: {{ $KK_BLUE_ALT }}; border-color: {{ $KK_DIVIDER }}">
                                    <h2 class="text-lg font-bold">Evaluation History</h2>
                                </div>

                                <div class="p-4">
                                    @if ($history->isEmpty())
                                        <p class="text-sm" style="color: {{ $KK_NAVY }}B3">
                                            No evaluations yet.
                                        </p>
                                    @else
                                        <div class="overflow-x-auto border" style="border-color: {{ $KK_DIVIDER }};">
                                            <table class="min-w-full text-sm divide-y" style="background: #fff; border-color: {{ $KK_DIVIDER }};">
                                                <thead style="background: {{ $KK_BLUE_ALT }};">
                                                    <tr>
                                                        <th class="py-2 px-3 text-left">Date</th>
                                                        <th class="py-2 px-3 text-left">{{ $LABEL_CC }}</th>
                                                        <th class="py-2 px-3 text-left">{{ $LABEL_SO }}</th>
                                                        <th class="py-2 px-3 text-left">{{ $LABEL_TR }}</th>
                                                        <th class="py-2 px-3 text-left">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y" style="border-color: {{ $KK_DIVIDER }};">
                                                    @foreach($history as $row)
                                                        @php
                                                            $date = \Carbon\Carbon::parse($row['date'] ?? now());
                                                            $ccH  = (int) ($row['cc'] ?? 0);
                                                            $soH  = (int) ($row['so'] ?? 0);
                                                            $trH  = (int) ($row['tr'] ?? 0);
                                                            $eid  = (int) ($row['id'] ?? 0);
                                                        @endphp
                                                        <tr>
                                                            <td class="py-2 px-3 font-medium">{{ $date->format('M d, Y') }}</td>
                                                            <td class="py-2 px-3">{{ $ccH }}</td>
                                                            <td class="py-2 px-3">{{ $soH }}</td>
                                                            <td class="py-2 px-3">{{ $trH }}</td>
                                                            <td class="py-2 px-3">
                                                                <a  class="inline-flex items-center gap-1 px-2 py-1 border text-sm"
                                                                    style="color:#076BA8; border-color:#076BA8;"
                                                                    href="{{ route('dogs.evaluations.show', ['dog' => $dog, 'evaluation' => $eid]) }}"
                                                                    target="_blank" rel="noopener">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                                    </svg>
                                                                    Open
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </section>
                </x-ts-tab.items>

                {{-- TRAINING --}}
                <x-ts-tab.items tab="Training">
                    <div class="max-w-7xl mx-auto mt-6">
                        <x-ts-card>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <x-ts-icon name="academic-cap" class="h-5 w-5"/>
                                    <div class="font-semibold">Training Program</div>
                                </div>
                                @if($trainingTotal > 0)
                                    <x-ts-badge color="blue">{{ $trainingDone }}/{{ $trainingTotal }}</x-ts-badge>
                                @endif
                            </div>
                        </x-ts-card>
                    </div>
                    <div class="mt-4">
                        <livewire:dogs.training-roadmap :dog="$dog" :key="'training-'.$dog->id" />
                    </div>
                </x-ts-tab.items>

                {{-- GALLERY --}}
                <x-ts-tab.items tab="Gallery">
                    <div class="max-w-7xl mx-auto mt-6 space-y-6">

                        @php
                            // Build image list for the TallStackUI v2 carousel (images only)
                            $carouselImages = $dog->media()
                                ->where('media_type', 'image')
                                ->orderBy('sort_order')
                                ->get()
                                ->map(function ($media) use ($dog) {
                                    return [
                                        'src'         => asset('storage/' . $media->file_path),
                                        'title'       => $media->caption ?: $dog->name,
                                        'description' => $media->caption ?: null,
                                    ];
                                })
                                ->values()
                                ->all();
                        @endphp

                        {{-- Carousel preview (TallStackUI v2) --}}
                        @if (count($carouselImages))
                            <x-ts-card>
                                <div class="mb-3 flex items-center justify-between">
                                    <h2 class="text-base font-semibold">Gallery Preview</h2>
                                    <p class="text-xs text-gray-500">
                                        Swipe or click to browse photos.
                                    </p>
                                </div>

                                {{-- Smaller carousel: limit width + keep aspect ratio --}}
                                <div class="max-w-xl mx-auto">
                                    <x-ts-carousel
                                        :images="$carouselImages"
                                        wrapper="aspect-[4/3]"
                                    />
                                </div>
                            </x-ts-card>
                        @else
                            <x-ts-card>
                                <h2 class="text-base font-semibold mb-1">Gallery Preview</h2>
                                <p class="text-sm text-gray-500">
                                    No photos yet. Add some below to see them here.
                                </p>
                            </x-ts-card>
                        @endif

                        {{-- Management (grid + add/remove via Livewire) --}}
                        @livewire('dogs.dog-gallery', ['dog' => $dog], key('gallery-'.$dog->id))

                    </div>
                </x-ts-tab.items>

                {{-- HEALTH (Vet + Diet combined) --}}
                <x-ts-tab.items tab="Health">
                    <div class="max-w-7xl mx-auto mt-6 grid grid-cols-1 gap-6">
                        {{-- Vet --}}
                        <x-ts-card>
                            <div class="px-2 py-2 border-b" style="border-color: {{ $KK_DIVIDER }};">
                                <h3 class="font-semibold">Vet</h3>
                            </div>
                            <div class="pt-4">
                                <livewire:dogs.vet-corner :dog="$dog" :key="'vet-'.$dog->id" />
                            </div>
                        </x-ts-card>

                        {{-- Diet / Nutrition --}}
                        <x-ts-card>
                            <div class="px-2 py-2 border-b" style="border-color: {{ $KK_DIVIDER }};">
                                <h3 class="font-semibold">Diet & Nutrition</h3>
                            </div>
                            <div class="pt-4">
                                <livewire:dogs.dietician :dog="$dog" :key="'diet-'.$dog->id" />
                            </div>
                        </x-ts-card>
                    </div>
                </x-ts-tab.items>

            </x-ts-tab>
        </div>
    </div>

    {{-- Keep the viewer available for History "Open" links --}}
    <livewire:dogs.evaluation-viewer :key="'eval-viewer-'.$dog->id" />
</div>
