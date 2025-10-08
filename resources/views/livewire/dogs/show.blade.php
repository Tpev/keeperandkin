{{-- resources/views/dogs/show-upgraded.blade.php --}}
{{-- Keeper & Kin–aligned dog profile (Comfort & Confidence / Sociability / Trainability) --}}

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    body { font-family: 'Raleway', sans-serif; }
    h1, h2, h3, h4, h5, h6 { font-family: 'Playfair Display', serif; }

    /* Hard corners everywhere inside .hard-corners */
    .hard-corners * { border-radius: 0 !important; }
</style>
@endpush

@php
    use Illuminate\Support\Str;
    use Carbon\Carbon;

    // --- Brand palette (final) ---
    $KK_NAVY     = '#03314C';   // deep navy
    $KK_BLUE     = '#076BA8';   // primary blue
    $KK_BLUE_ALT = '#DAEEFF';   // alt blue (light)
    $KK_BG_LIGHT = '#eaeaea';   // airy off-white / app bg

    // neutrals/utility
    $KK_DIVIDER  = '#E2E8F0';
    $KK_SUCCESS  = '#16A34A';
    $KK_WARNING  = '#F59E0B';
    $KK_DANGER   = '#DC2626';
    $LAVENDER    = '#6366F1';

    // Color scale (OK, Red, Orange, Yellow, Green) — 25% each
    $SCALE_OK     = '#94A3B8';  // slate-400 (unknown/missing)
    $SCALE_RED    = '#DC2626';  // 0–25
    $SCALE_ORANGE = '#F97316';  // 26–50
    $SCALE_YELLOW = '#FFCC00';  // 51–75
    $SCALE_GREEN  = '#16A34A';  // 76–100

    // Pull what we have from $dog, fallback to placeholders
    $name       = $dog->name ?? 'Unnamed';
    $uid        = 'DOG-'.str_pad((string)($dog->id ?? 0), 4, '0', STR_PAD_LEFT);
    $breed      = $dog->breed ?? 'Mixed';
    $sex        = $dog->sex ? Str::ucfirst($dog->sex) : '—';
    $age        = $dog->age ? $dog->age.' yrs' : '—';
    $status     = $dog->status ?? 'Available';
    $intakeDate = $dog->created_at ? $dog->created_at->toDateString() : now()->toDateString();

    // Photo column name can be 'photo' (our create flow) or legacy 'photo_path'
    $photoCol   = $dog->photo ?? $dog->photo_path ?? null;
    $photo      = $photoCol ? asset('storage/'.$photoCol) : 'https://placehold.co/448x448?text=Dog';

    $latestEval = $dog->latestEvaluation ?? null;

    // Do we truly have an evaluation?
$catsRaw = is_array($latestEval?->category_scores ?? null) ? $latestEval->category_scores : [];

$val = function(array $a, array $keys, $default = null) {
    foreach ($keys as $k) {
        if (array_key_exists($k, $a) && $a[$k] !== null && $a[$k] !== '') return (int) $a[$k];
    }
    return $default;
};

    // Color helper: OK (unknown) → Red (0–25) → Orange (26–50) → Yellow (51–75) → Green (76–100)
    $colorFor = function($v) use ($SCALE_OK,$SCALE_RED,$SCALE_ORANGE,$SCALE_YELLOW,$SCALE_GREEN) {
        if (!is_numeric($v)) return $SCALE_OK;
        $v = (int)$v;
        if ($v <= 25) return $SCALE_RED;
        if ($v <= 50) return $SCALE_ORANGE;
        if ($v <= 75) return $SCALE_YELLOW;
        return $SCALE_GREEN;
    };

    // Public-facing category labels
    $LABEL_CC = 'Comfort & Confidence';
    $LABEL_SO = 'Sociability';
    $LABEL_TR = 'Trainability';

    // Category values mapped from stored keys
$cc = $val($catsRaw, ['Comfort & Confidence','Confidence','comfort_confidence']);
$so = $val($catsRaw, ['Sociability','Social','sociability']);
$tr = $val($catsRaw, ['Trainability','trainability']);

$hasScores = $latestEval && ($cc !== null || $so !== null || $tr !== null);

$categories = [
    $LABEL_CC => $hasScores ? $cc : null,
    $LABEL_SO => $hasScores ? $so : null,
    $LABEL_TR => $hasScores ? $tr : null,
];

    // Helpers for rendering
    $valOrDash  = fn($v) => is_numeric($v) ? $v : '—';
    $barWidth   = fn($v) => is_numeric($v) ? max(0, min(100, (int)$v)) : 0;
    $barColor   = fn($v) => $colorFor($v);

    // Visual accents (by slug)
    $catHex = [
        'comfort-confidence' => $SCALE_GREEN,
        'sociability'        => $SCALE_YELLOW,
        'trainability'       => $LAVENDER,
    ];

    // Icons keyed by slug
    $tileIcons = [
        'comfort-confidence'=>'<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6l7 4v3a9 9 0 11-14 0V10l7-4z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>',
        'sociability'=>'<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M9 20H4v-2a3 3 0 015.356-1.857M15 11a3 3 0 110-6 3 3 0 010 6z"/><path stroke-linecap="round" stroke-linejoin="round" d="M7 11a3 3 0 110-6 3 3 0 010 6z"/></svg>',
        'trainability'=>'<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422A12.083 12.083 0 0119 13.714L12 21l-7-7.286a12.083 12.083 0 01.84-3.136L12 14z"/></svg>',
    ];

    // Derived/Formatted fields
    $approxDobUs = $dog->approx_dob ? \Illuminate\Support\Carbon::parse($dog->approx_dob)->format('m/d/Y') : '—';
    $fixedHuman  = is_null($dog->fixed) ? 'Unknown' : ($dog->fixed ? 'Yes' : 'No');
@endphp

<div class="hard-corners min-h-screen font-sans py-12 px-4 sm:px-6 lg:px-8"
     style="color: {{ $KK_NAVY }}; background-image: linear-gradient(to bottom right, {{ $KK_BG_LIGHT }}, #ffffff 35%, {{ $KK_BLUE_ALT }} 100%);"
     x-data="{ showHistory:false }">
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

{{-- Action bar (brand-aligned, hard corners) --}}
<section class="max-w-7xl mx-auto mb-4 border"
         style="background: #FFFFFF; border-color: {{ $KK_DIVIDER }};">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4">
        {{-- Left: Breadcrumb-ish context --}}
        <div class="flex items-center gap-3 text-sm">
            <a href="{{ route('dogs.index') }}" class="inline-flex items-center px-2 py-1 border font-medium"
               style="color: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }};">
                ← All Dogs
            </a>
            <div class="hidden md:block h-5 w-px" style="background: {{ $KK_DIVIDER }};"></div>
            <div class="font-semibold" style="color: {{ $KK_NAVY }};">
                {{ $dog->name ?? 'Dog' }}
                <span class="ml-2 text-xs font-mono px-1.5 py-0.5 border"
                      style="color: {{ $KK_BLUE }}; border-color: {{ $KK_DIVIDER }};">
                    {{ $uid }}
                </span>
            </div>
        </div>

        {{-- Right: Actions (primary / secondary / danger) --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('dogs.evaluate', $dog) }}"
               class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold border"
               style="background: {{ $KK_BLUE }}; color: #fff; border-color: {{ $KK_BLUE }};">
                {{-- lightning icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:#fff">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                New Evaluation
            </a>

            <a href="{{ route('dogs.edit', $dog) }}"
               class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold border"
               style="background: #FFFFFF; color: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }};">
                {{-- pencil icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: {{ $KK_BLUE }}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M4 20h4l10.5-10.5a2.5 2.5 0 10-3.536-3.536L4 16v4z"/>
                </svg>
                Edit Dog
            </a>
@livewire('dogs.transfer-initiator', ['dog' => $dog], key('transfer-'.$dog->id))

            {{-- Livewire delete button wrapper to inherit styles (hard corners) --}}
            <div class="inline-flex">
                <livewire:dogs.delete-button :dog="$dog"
                    :button-classes="'inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold border'"
                    :button-style="'background:#FFFFFF; color:#DC2626; border-color:#DC2626;'" />
            </div>
        </div>
    </div>
</section>

@php
    $photoUrl = $dog->photo_url ?? 'https://placehold.co/448x448?text=Dog';
@endphp
    {{-- Header / Profile section (NOW includes the new profile fields BEFORE scorecard) --}}
    <section class="max-w-7xl mx-auto p-8 md:p-12 border"
             style="background: rgba(255,255,255,0.9); border-color: {{ $KK_DIVIDER }};">

        <div class="flex flex-col md:flex-row gap-10">
            {{-- Dog photo --}}
<img src="{{ $photoUrl }}" alt="{{ $name }}"
     class="w-40 h-40 md:w-56 md:h-56 object-cover border"
     style="border-color: {{ $KK_BLUE_ALT }};" loading="lazy">

            <div class="flex-1 space-y-4">
                {{-- Headline --}}
                <div class="space-y-2">
                    <h1 class="text-4xl font-extrabold flex items-center gap-3">
                        {{ $name }}
                        <span class="text-xs font-mono px-2 py-0.5 border"
                              style="background: {{ $KK_BLUE_ALT }}; color: {{ $KK_BLUE }}; border-color: {{ $KK_DIVIDER }};">
                            {{ $uid }}
                        </span>
                    </h1>

                    {{-- Serial number --}}
                    @if ($dog->serial_number)
                        <p class="text-sm font-mono flex items-center gap-2">
                            <span class="font-semibold text-gray-700">Serial No:</span>
                            <span class="px-2 py-0.5 border bg-white text-gray-800" style="border-color: {{ $KK_DIVIDER }};">
                                {{ $dog->serial_number }}
                            </span>
                        </p>
                    @endif

                    {{-- Inline basics --}}
                    <p class="text-sm uppercase tracking-wide font-semibold flex items-center gap-2" style="color: {{ $KK_NAVY }}CC">
                        {!! $tileIcons['trainability'] !!} {{ $breed }} · {{ $sex }} · {{ $age }}
                    </p>

                    {{-- Intake & Status --}}
                    <p class="text-sm leading-5">
                        <span class="font-medium">Intake:</span> {{ \Carbon\Carbon::parse($intakeDate)->format('M d, Y') }} ·
                        <span class="font-medium">Status:</span>
                        <span class="inline-block px-2 py-0.5 border"
                              style="background:#DEF7EC; color:#03543F; border-color:#C7F3E3;">
                            {{ $status }}
                        </span>
                    </p>

                    @php
                        // Latest evaluation & red flags
                        $latestEval = $latestEval ?? ($dog->latestEvaluation ?? null);
                        $redFlags   = is_array($latestEval?->red_flags ?? null) ? $latestEval->red_flags : [];
                    @endphp

                    {{-- Red Flags --}}
                    <div class="flex flex-wrap gap-2 mt-3">
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

                {{-- NEW: Profile details grid (before scorecard) --}}
                <div class="mt-6 border" style="border-color: {{ $KK_DIVIDER }};">
                    <div class="px-4 py-3" style="background: {{ $KK_BLUE_ALT }};">
                        <h2 class="text-lg font-bold">Profile</h2>
                    </div>

                    {{-- Use a definition table look with hard corners --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                        {{-- Row helper --}}
                        @php
                            $row = function($label, $value) use ($KK_DIVIDER, $KK_NAVY) {
                                $val = filled($value) ? $value : '—';
                                return <<<HTML
                                    <div class="flex flex-col border-t" style="border-color: {$KK_DIVIDER};">
                                        <div class="px-4 pt-3 text-xs uppercase tracking-wide font-semibold" style="color: {$KK_NAVY}B3">{$label}</div>
                                        <div class="px-4 pb-3 text-sm">{$val}</div>
                                    </div>
                                HTML;
                            };
                        @endphp

                        {!! $row('Location', e($dog->location)) !!}
                        {!! $row('Approx. Date of Birth ', e($approxDobUs)) !!}
                        {!! $row('Altered', e($fixedHuman)) !!}
                        {!! $row('Color', e($dog->color)) !!}
                        {!! $row('Size', e($dog->size)) !!}
                        {!! $row('Microchip', e($dog->microchip)) !!}
                        {!! $row('Heartworm', e($dog->heartworm)) !!}
                        {!! $row('FIV/L', e($dog->fiv_l)) !!}
                        {!! $row('FLV', e($dog->flv)) !!}
                        {!! $row('Housetrained?', e($dog->housetrained)) !!}
                        {!! $row('Good with Dogs?', e($dog->good_with_dogs)) !!}
                        {!! $row('Good with Cats?', e($dog->good_with_cats)) !!}
                        {!! $row('Good with Children?', e($dog->good_with_children)) !!}
                    </div>
                </div>

                {{-- SCORECARD (still in profile section, after profile details) --}}
                <div class="mt-6 border" style="border-color: {{ $KK_DIVIDER }};">
                    <div class="px-4 py-3" style="background: {{ $KK_BLUE_ALT }};">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-bold">Scorecard</h2>
                            <div class="hidden sm:flex items-center gap-3 text-xs">
                                <span class="inline-flex items-center gap-1">
                                    <span class="inline-block w-3 h-3 border" style="background: {{ $SCALE_RED }}; border-color: {{ $SCALE_RED }}"></span> Red (0–25)
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <span class="inline-block w-3 h-3 border" style="background: {{ $SCALE_ORANGE }}; border-color: {{ $SCALE_ORANGE }}"></span> Orange (26–50)
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <span class="inline-block w-3 h-3 border" style="background: {{ $SCALE_YELLOW }}; border-color: {{ $SCALE_YELLOW }}"></span> Yellow (51–75)
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <span class="inline-block w-3 h-3 border" style="background: {{ $SCALE_GREEN }}; border-color: {{ $SCALE_GREEN }}"></span> Green (76–100)
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-0 sm:grid-cols-3">
                        @foreach($categories as $lbl=>$val)
                            @php
                                $key    = Str::slug($lbl); // 'comfort-confidence' | 'sociability' | 'trainability'
                                $icon   = $tileIcons[$key] ?? '';
                                $barClr = $barColor($val);
                                $width  = $barWidth($val);
                                $text   = is_numeric($val) ? ($val.' / 100') : '—';
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

                {{-- Description --}}
                @if($dog->description)
                    <div class="mt-6 border p-4" style="border-color: {{ $KK_DIVIDER }}; background: #fff;">
                        <h3 class="text-base font-semibold mb-2">Notes</h3>
                        <p class="text-sm leading-6" style="color: {{ $KK_NAVY }}CC">{{ $dog->description }}</p>
                    </div>
                @endif
            </div>
        </div>
    </section>

{{-- Scorecard evolution --}}
@php
    // ADD THIS LINE:
    $evals = $dog->evaluations()->orderBy('created_at')->get();

    $history = $evals->map(function ($e) {
        $cs = (array) ($e->category_scores ?? []);
        $pick = function(array $a, array $keys) {
            foreach ($keys as $k) if (array_key_exists($k, $a)) return (int) $a[$k];
            return 0;
        };
        return [
            'date' => $e->created_at,
            'cc'   => $pick($cs, ['Comfort & Confidence','Confidence','comfort_confidence']),
            'so'   => $pick($cs, ['Sociability','Social','sociability']),
            'tr'   => $pick($cs, ['Trainability','trainability']),
        ];
    });

    $original = $history->first();
    $latest   = $history->last();
@endphp


    <section class="max-w-7xl mx-auto mt-12 border p-8 md:p-10" style="background: {{ $KK_BLUE_ALT }}; border-color: {{ $KK_DIVIDER }};">
        <h2 class="text-xl font-bold mb-6">Scorecard Evolution</h2>

        @if ($history->isEmpty())
            <div class="p-6 border" style="background: #fff; border-color: {{ $KK_DIVIDER }};">
                <p class="text-sm" style="color: {{ $KK_NAVY }}B3">No evaluations yet.</p>
            </div>
        @else
            @php
                $originalDate = $original['date'];
                $origCC = $original['cc']; $origSO = $original['so']; $origTR = $original['tr'];

                $latestDate = $latest['date'];
                $newCC = $latest['cc']; $newSO = $latest['so']; $newTR = $latest['tr'];
            @endphp

            <div class="grid gap-6 sm:grid-cols-2">
                {{-- Original --}}
                <div class="p-6 space-y-4 border" style="background: #fff; border-color: {{ $KK_DIVIDER }};">
                    <h3 class="font-semibold">Original ({{ \Carbon\Carbon::parse($originalDate)->format('M d, Y') }})</h3>
                    @foreach([[$LABEL_CC,$origCC],[$LABEL_SO,$origSO],[$LABEL_TR,$origTR]] as [$l,$v])
                        @php $b = $colorFor((int) $v); @endphp
                        <p class="text-xs mb-0.5" style="color: {{ $KK_NAVY }}B3">{{ $l }}</p>
                        <div class="h-2 w-full overflow-hidden border" style="background: {{ $KK_DIVIDER }}; border-color: {{ $KK_DIVIDER }};">
                            <div class="h-full" style="background:{{ $b }};width:{{ (int) $v }}%"></div>
                        </div>
                    @endforeach
                </div>

                {{-- Latest --}}
                <div class="p-6 space-y-4 border" style="background: #fff; border-color: {{ $KK_DIVIDER }};">
                    <h3 class="font-semibold">Latest ({{ \Carbon\Carbon::parse($latestDate)->format('M d, Y') }})</h3>
                    @foreach([[$LABEL_CC,$newCC,$origCC],[$LABEL_SO,$newSO,$origSO],[$LABEL_TR,$newTR,$origTR]] as [$l,$n,$o])
                        @php $n=(int)$n; $o=(int)$o; $b=$colorFor($n); $d=$n-$o; @endphp
                        <div>
                            <div class="flex justify-between items-center">
                                <p class="text-xs mb-0.5" style="color: {{ $KK_NAVY }}B3">{{ $l }}</p>
                                <span class="text-xs font-semibold" style="color: {{ $d>=0 ? '#059669' : '#DC2626' }}">{{ $d>0?'+':'' }}{{ $d }}</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden border" style="background: {{ $KK_DIVIDER }}; border-color: {{ $KK_DIVIDER }};">
                                <div class="h-full" style="background:{{ $b }};width:{{ $n }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Full history --}}
            <div class="mt-8 text-center">
                <button x-show="!showHistory" @click="showHistory=true" class="font-semibold border px-3 py-1"
                        style="color: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }};">See full history</button>
                <div x-show="showHistory" x-cloak>
                    <div class="overflow-x-auto mt-6 border" style="border-color: {{ $KK_DIVIDER }};">
                        <table class="min-w-full text-sm divide-y" style="background: #fff; border-color: {{ $KK_DIVIDER }};">
                            <thead style="background: {{ $KK_BLUE_ALT }};">
                                <tr>
                                    <th class="py-2 px-3 text-left">Date</th>
                                    <th class="py-2 px-3 text-left">{{ $LABEL_CC }}</th>
                                    <th class="py-2 px-3 text-left">{{ $LABEL_SO }}</th>
                                    <th class="py-2 px-3 text-left">{{ $LABEL_TR }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y" style="border-color: {{ $KK_DIVIDER }};">
                                @forelse($history as $row)
                                    @php
                                        $date = \Carbon\Carbon::parse($row['date'] ?? now());
                                        $cc   = (int) ($row['cc'] ?? 0);
                                        $so   = (int) ($row['so'] ?? 0);
                                        $tr   = (int) ($row['tr'] ?? 0);
                                    @endphp
                                    <tr>
                                        <td class="py-2 px-3 font-medium">{{ $date->format('M d, Y') }}</td>
                                        <td class="py-2 px-3">{{ $cc }}</td>
                                        <td class="py-2 px-3">{{ $so }}</td>
                                        <td class="py-2 px-3">{{ $tr }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="py-3 px-3 text-sm text-gray-500" colspan="4">No evaluations yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <button @click="showHistory=false" class="mt-4 font-semibold border px-3 py-1"
                            style="color: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }};">Hide history</button>
                </div>
            </div>
        @endif
    </section>

{{-- Adoption Requirements Checklist (Livewire) --}}
<livewire:dogs.adoption-checklist :dog="$dog" />


{{-- Training Program (Livewire) --}}


<livewire:dogs.training-roadmap :dog="$dog" />


    {{-- Dynamic modules (kept) --}}
    <livewire:dogs.care-notes :dog="$dog" />
    <livewire:dogs.vet-corner :dog="$dog" />
    <livewire:dogs.dietician :dog="$dog" />
</div>

@push('styles')
<style>
@keyframes shimmer{0%{background-position:0% 50%}100%{background-position:200% 50%}}
.text-shadow-white{ text-shadow:0 0 2px #fff }
[x-cloak]{ display:none !important; }
</style>
@endpush
