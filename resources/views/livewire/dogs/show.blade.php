{{-- resources/views/dogs/show-upgraded.blade.php --}}
{{-- Keeper & Kin–aligned dog profile (Confidence / Social / Trainability) --}}

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    body { font-family: 'Raleway', sans-serif; }
    h1, h2, h3, h4, h5, h6 { font-family: 'Playfair Display', serif; }
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

    // Pull what we have from $dog, fallback to placeholders
    $name       = $dog->name ?? 'Unnamed';
    $uid        = 'DOG-'.str_pad((string)($dog->id ?? 0), 4, '0', STR_PAD_LEFT);
    $breed      = $dog->breed ?? 'Mixed';
    $sex        = $dog->sex ? Str::ucfirst($dog->sex) : '—';
    $age        = $dog->age ? $dog->age.' yrs' : '—';
    $status     = $dog->status ?? 'Available';
    $intakeDate = $dog->created_at ? $dog->created_at->toDateString() : now()->subMonths(2)->toDateString();
    $photoPath  = $dog->photo_path ?? null;
    $photo      = $photoPath ? asset('storage/'.$photoPath) : 'https://placehold.co/448x448?text=Dog';

    $latestEval = $dog->latestEvaluation ?? null;

    // Do we truly have an evaluation?
    $catsRaw   = is_array($latestEval?->category_scores ?? null) ? $latestEval->category_scores : [];
    $hasScores = $latestEval && (
        is_numeric($latestEval->score)
        || isset($catsRaw['Confidence']) || isset($catsRaw['Sociability']) || isset($catsRaw['Social']) || isset($catsRaw['Trainability'])
    );

    // If none, keep everything "unknown" instead of fake defaults
    $overallScore = $hasScores && is_numeric($latestEval->score) ? (int) $latestEval->score : null;

    // Color helper (defined BEFORE $barColor uses it)
    $colorFor = fn(int $v)=> $v < 50 ? $KK_DANGER : ($v < 75 ? $KK_WARNING : $KK_BLUE);

    $categories = [
        'Confidence'   => $hasScores && isset($catsRaw['Confidence'])   ? (int) $catsRaw['Confidence']   : null,
        // UI says "Social" but DB stores "Sociability" sometimes
        'Social'       => $hasScores && (isset($catsRaw['Sociability']) || isset($catsRaw['Social']))
                          ? (int) ($catsRaw['Sociability'] ?? $catsRaw['Social'])
                          : null,
        'Trainability' => $hasScores && isset($catsRaw['Trainability']) ? (int) $catsRaw['Trainability'] : null,
    ];

    // Helpers for rendering unknowns
    $neutralBar = '#CBD5E1'; // slate-300-ish
    $valOrDash  = fn($v) => is_numeric($v) ? $v : '—';
    $barWidth   = fn($v) => is_numeric($v) ? max(0, min(100, (int)$v)) : 0;
    $barColor   = fn($v) => is_numeric($v) ? $colorFor((int)$v) : $neutralBar;

    // Demo data (can be swapped with real relations)
    $sessions = [
        (object)['category'=>'Confidence','title'=>'Novel surfaces','goal'=>'Cross metal grate calmly','progress'=>0.55],
        (object)['category'=>'Confidence','title'=>'Startle recovery','goal'=>'Bounce-back < 5s','progress'=>0.4],
        (object)['category'=>'Social','title'=>'Polite greetings','goal'=>'No jump, loose leash','progress'=>0.6],
        (object)['category'=>'Social','title'=>'Handler focus','goal'=>'Hold eye contact 5s','progress'=>0.45],
        (object)['category'=>'Trainability','title'=>'Place','goal'=>'Go-to-mat from 3m','progress'=>0.8],
        (object)['category'=>'Trainability','title'=>'Loose-leash','goal'=>'Walk 20m no pull','progress'=>0.5],
    ];

    $catHex = ['confidence'=>$KK_BLUE,'social'=>$KK_WARNING,'trainability'=>$LAVENDER];

    $tileIcons = [
        'confidence'=>'<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6l7 4v3a9 9 0 11-14 0V10l7-4z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>',
        'social'=>'<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M9 20H4v-2a3 3 0 015.356-1.857M15 11a3 3 0 110-6 3 3 0 010 6z"/><path stroke-linecap="round" stroke-linejoin="round" d="M7 11a3 3 0 110-6 3 3 0 010 6z"/></svg>',
        'trainability'=>'<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422A12.083 12.083 0 0119 13.714L12 21l-7-7.286a12.083 12.083 0 01.84-3.136L12 14z"/></svg>',
    ];
@endphp

<div class="min-h-screen font-sans py-12 px-4 sm:px-6 lg:px-8"
     style="color: {{ $KK_NAVY }}; background-image: linear-gradient(to bottom right, {{ $KK_BG_LIGHT }}, #ffffff 35%, {{ $KK_BLUE_ALT }} 100%);"
     x-data="{ showHistory:false }">

    {{-- Action bar --}}
    <div class="max-w-7xl mx-auto mb-4 flex gap-3">
        <a href="{{ route('dogs.evaluate', $dog) }}" class="inline-flex items-center px-4 py-2 rounded-xl text-white transition shadow" style="background: {{ $KK_SUCCESS }};">New Evaluation</a>
        <a href="{{ route('dogs.edit', $dog) }}" class="inline-flex items-center px-4 py-2 rounded-xl text-white transition shadow" style="background: {{ $KK_BLUE }};">Edit Dog</a>
        <livewire:dogs.delete-button :dog="$dog" />
    </div>

{{-- 1 ▸ Header card --}}
<section class="max-w-7xl mx-auto backdrop-blur rounded-3xl ring-1 ring-black/5 shadow-lg p-8 md:p-12 flex flex-col md:flex-row gap-10 transition hover:-translate-y-1 hover:shadow-2xl" style="background: rgba(255,255,255,0.85);">
    {{-- Dog photo --}}
    <img src="{{ $photo }}" alt="{{ $name }}"
         class="w-40 h-40 md:w-56 md:h-56 object-cover rounded-full shadow-md"
         style="border: 4px solid {{ $KK_BLUE_ALT }};" loading="lazy">

    <div class="flex-1 space-y-2">
        <h1 class="text-4xl font-extrabold flex items-center gap-3">
            {{ $name }}
            <span class="text-xs font-mono px-2 py-0.5 rounded"
                  style="background: {{ $KK_BLUE_ALT }}; color: {{ $KK_BLUE }};">
                {{ $uid }}
            </span>
        </h1>

        {{-- Serial number --}}
        @if ($dog->serial_number)
            <p class="text-sm font-mono flex items-center gap-2">
                <span class="font-semibold text-gray-700">Serial No:</span>
                <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-800">
                    {{ $dog->serial_number }}
                </span>
            </p>
        @endif

        <p class="text-sm uppercase tracking-wide font-semibold flex items-center gap-2" style="color: {{ $KK_NAVY }}CC">
            {!! $tileIcons['trainability'] !!} {{ $breed }} · {{ $sex }} · {{ $age }}
        </p>

        <p class="text-sm leading-5">
            <span class="font-medium">Intake:</span> {{ Carbon::parse($intakeDate)->format('M d, Y') }} ·
            <span class="font-medium">Status:</span>
            <span class="inline-block px-2 py-0.5 rounded-full" style="background:#DEF7EC; color:#03543F;">
                {{ $status }}
            </span>
        </p>

        @php
            // Use the latest evaluation if available (from passed variable or relation)
            $latestEval = $latestEval ?? ($dog->latestEvaluation ?? null);
            $redFlags   = is_array($latestEval?->red_flags ?? null) ? $latestEval->red_flags : [];
        @endphp

        <div class="flex flex-wrap gap-2 mt-3">
            @forelse ($redFlags as $flag)
                <span class="inline-block text-xs font-semibold px-3 py-1 rounded-full"
                      style="background: {{ $KK_DANGER }}; color: white;">
                    {{ \Illuminate\Support\Str::headline($flag) }}
                </span>
            @empty
                <span class="inline-block text-xs px-3 py-1 rounded-full"
                      style="background:#E2E8F0; color:#374151;">
                    No red flags
                </span>
            @endforelse
        </div>

        @php
            $stroke = is_numeric($overallScore) ? $colorFor($overallScore) : $KK_DIVIDER;
            $dashOffset = is_numeric($overallScore) ? 276 - ($overallScore/100)*276 : 276; // full empty ring
        @endphp

        <div class="flex items-center gap-4 mt-6">
            <div class="relative" aria-label="Overall score">
                <svg class="w-24 h-24" role="img" aria-hidden="true" focusable="false">
                    <circle cx="48" cy="48" r="44" fill="none" stroke="{{ $KK_DIVIDER }}" stroke-width="8"/>
                    <circle cx="48" cy="48" r="44" fill="none" stroke="{{ $stroke }}" stroke-width="8"
                            stroke-dasharray="276" stroke-dashoffset="{{ $dashOffset }}"
                            stroke-linecap="round" transform="rotate(-90 48 48)"/>
                </svg>
                <span class="absolute inset-0 flex items-center justify-center font-bold text-xl text-shadow-white"
                      style="color:{{ $stroke }}">{{ is_numeric($overallScore) ? $overallScore : '—' }}</span>
            </div>
            <span class="text-sm" style="color: {{ $KK_NAVY }}B3">
                {{ is_numeric($overallScore) ? 'Overall score' : 'No evaluations yet' }}
            </span>
        </div>
    </div>
</section> {{-- ✅ close header section --}}


    {{-- 2 ▸ Category tiles --}}
    <section class="max-w-7xl mx-auto mt-12 grid gap-6 grid-cols-1 sm:grid-cols-3">
        @foreach($categories as $lbl=>$val)
            @php
                $key    = Str::lower($lbl);
                $accent = $catHex[$key] ?? $KK_BLUE;
                $icon   = $tileIcons[$key] ?? '';
                $barClr = $barColor($val);
                $width  = $barWidth($val);
                $text   = is_numeric($val) ? ($val.' / 100') : '—';
            @endphp
            <div class="rounded-3xl p-6 shadow-lg transition hover:-translate-y-1 hover:shadow-2xl" style="background: {{ $KK_BLUE_ALT }};">
                <p class="text-sm font-semibold flex items-center gap-2" style="color: {{ $accent }}">{!! $icon !!} {{ ucfirst($lbl) }}</p>
                <div class="h-3 w-full rounded overflow-hidden mt-2" style="background: rgba(255,255,255,.6);">
                    <div class="h-full {{ is_numeric($val) ? 'animate-[shimmer_3s_infinite]' : '' }}"
                         style="background: {{ $barClr }}; width: {{ $width }}%"></div>
                </div>
                <p class="text-right mt-1 text-sm font-medium" style="color:{{ $barClr }}">{{ $text }}</p>
            </div>
        @endforeach
    </section>

    {{-- 3 ▸ Scorecard evolution (real data) --}}
    @php
        // Build history from real evaluations (oldest → newest)
        $evals = $dog->evaluations()->orderBy('created_at')->get();

        $history = $evals->map(function ($e) {
            $cs = (array) ($e->category_scores ?? []);
            return [
                'date' => $e->created_at,
                'g'    => (int) ($e->score ?? 0),
                'c'    => (int) ($cs['Confidence'] ?? 0),
                // UI label is "Social" but stored key might be "Sociability"
                's'    => (int) ($cs['Social'] ?? ($cs['Sociability'] ?? 0)),
                't'    => (int) ($cs['Trainability'] ?? 0),
            ];
        });

        $original = $history->first();
        $latest   = $history->last();
    @endphp

    <section class="max-w-7xl mx-auto mt-16 rounded-3xl ring-1 ring-black/5 shadow-lg p-8 md:p-10" style="background: {{ $KK_BLUE_ALT }};">
        <h2 class="text-xl font-bold mb-6">Scorecard evolution</h2>

        @if ($history->isEmpty())
            <div class="rounded-2xl p-6 ring-1 ring-black/5" style="background: rgba(255,255,255,.9);">
                <p class="text-sm" style="color: {{ $KK_NAVY }}B3">No evaluations yet.</p>
            </div>
        @else
            @php
                $originalDate = $original['date'];
                $origG = $original['g']; $origC = $original['c']; $origS = $original['s']; $origT = $original['t'];

                $latestDate = $latest['date'];
                $newG = $latest['g']; $newC = $latest['c']; $newS = $latest['s']; $newT = $latest['t'];
            @endphp

            <div class="grid gap-6 sm:grid-cols-2">
                {{-- Original --}}
                <div class="rounded-2xl p-6 space-y-4 ring-1 ring-black/5" style="background: rgba(255,255,255,.9);">
                    <h3 class="font-semibold">Original ({{ \Carbon\Carbon::parse($originalDate)->format('M d, Y') }})</h3>
                    @foreach([['Global',$origG],['Confidence',$origC],['Social',$origS],['Trainability',$origT]] as [$l,$v])
                        @php $b = $colorFor((int) $v); @endphp
                        <p class="text-xs mb-0.5" style="color: {{ $KK_NAVY }}B3">{{ $l }}</p>
                        <div class="h-2 w-full rounded overflow-hidden" style="background: {{ $KK_DIVIDER }};">
                            <div class="h-full" style="background:{{ $b }};width:{{ (int) $v }}%"></div>
                        </div>
                    @endforeach
                </div>

                {{-- Latest --}}
                <div class="rounded-2xl p-6 space-y-4 ring-1 ring-black/5" style="background: rgba(255,255,255,.9);">
                    <h3 class="font-semibold">Latest ({{ \Carbon\Carbon::parse($latestDate)->format('M d, Y') }})</h3>
                    @foreach([['Global',$newG,$origG],['Confidence',$newC,$origC],['Social',$newS,$origS],['Trainability',$newT,$origT]] as [$l,$n,$o])
                        @php $n=(int)$n; $o=(int)$o; $b=$colorFor($n); $d=$n-$o; @endphp
                        <div>
                            <div class="flex justify-between items-center">
                                <p class="text-xs mb-0.5" style="color: {{ $KK_NAVY }}B3">{{ $l }}</p>
                                <span class="text-xs font-semibold" style="color: {{ $d>=0 ? '#059669' : '#DC2626' }}">{{ $d>0?'+':'' }}{{ $d }}</span>
                            </div>
                            <div class="h-2 w-full rounded overflow-hidden" style="background: {{ $KK_DIVIDER }};">
                                <div class="h-full" style="background:{{ $b }};width:{{ $n }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Full history --}}
            <div class="mt-8 text-center">
                <button x-show="!showHistory" @click="showHistory=true" class="font-semibold" style="color: {{ $KK_BLUE }};">See full history</button>
                <div x-show="showHistory" x-cloak>
                    <div class="overflow-x-auto mt-6 rounded-lg shadow">
                        <table class="min-w-full text-sm divide-y" style="background: rgba(255,255,255,.9); border-color: {{ $KK_DIVIDER }};">
                            <thead style="background: {{ $KK_BLUE_ALT }};">
                                <tr>
                                    <th class="py-2 px-3">Date</th>
                                    <th class="py-2 px-3">Global</th>
                                    <th class="py-2 px-3">Confidence</th>
                                    <th class="py-2 px-3">Social</th>
                                    <th class="py-2 px-3">Trainability</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y" style="border-color: {{ $KK_DIVIDER }};">
                                @forelse($history as $row)
                                    @php
                                        $date = \Carbon\Carbon::parse($row['date'] ?? now());
                                        $g    = (int) ($row['g'] ?? 0);
                                        $c    = (int) ($row['c'] ?? 0); // Confidence only
                                        $s    = (int) ($row['s'] ?? 0); // Social/Sociability already normalized above
                                        $t    = (int) ($row['t'] ?? 0);
                                    @endphp
                                    <tr>
                                        <td class="py-2 px-3 font-medium">{{ $date->format('M d, Y') }}</td>
                                        <td class="py-2 px-3">{{ $g }}</td>
                                        <td class="py-2 px-3">{{ $c }}</td>
                                        <td class="py-2 px-3">{{ $s }}</td>
                                        <td class="py-2 px-3">{{ $t }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="py-3 px-3 text-sm text-gray-500" colspan="5">No evaluations yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <button @click="showHistory=false" class="mt-4 font-semibold" style="color: {{ $KK_BLUE }};">Hide history</button>
                </div>
            </div>
        @endif
    </section>

    {{-- ✅ New Section: Checklist --}}
    <section class="max-w-7xl mx-auto mt-12 rounded-3xl ring-1 ring-black/5 shadow-lg p-8 md:p-10"
             style="background: {{ $KK_BLUE_ALT }};">
        <h2 class="text-xl font-bold mb-6">Readiness Checklist</h2>
        <ul class="text-sm">
            @php
                $checklist = [
                    'Accept a harness without resistance',
                    'Remain calm when a leash is clipped on',
                    'Functionally tolerate handling, including being picked up and touched, without posing a danger to themselves or others',
                    'Participate in short car rides around the property without distress',
                    'Transition through doorways and thresholds voluntarily',
                ];
            @endphp
            @foreach ($checklist as $idx => $item)
                <li class="flex items-center gap-3 py-3"
                    style="border-bottom: 1px solid {{ $KK_DIVIDER }}; {{ $idx === array_key_last($checklist) ? 'border-bottom: none;' : '' }}">
                    <span class="flex items-center justify-center w-6 h-6 rounded-full shadow-sm"
                          style="background: {{ $KK_SUCCESS }}; color: white;">
                        ✓
                    </span>
                    <span class="leading-snug" style="color: {{ $KK_NAVY }}">{{ $item }}</span>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- 4 ▸ Training road-map --}}
    <section class="max-w-7xl mx-auto mt-16 backdrop-blur rounded-3xl ring-1 ring-black/5 p-8 md:p-10 shadow-lg" style="background: rgba(255,255,255,.9);">
        <h2 class="text-xl font-bold mb-6">Training Road-map</h2>
        @php $all=collect($sessions)->map(fn($s)=>(array)$s); @endphp
        @foreach(['confidence','social','trainability'] as $cat)
            @php
                $rows=$all->filter(fn($r)=>Str::lower($r['category'])===$cat);
                if($rows->isEmpty()) continue;
                $acc=$catHex[$cat] ?? $KK_BLUE;
            @endphp
            <h3 class="text-lg font-semibold mb-3 flex items-center gap-2" style="color: {{ $KK_BLUE }};">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color: {{ $KK_BLUE }};"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                {{ ucfirst($cat) }}
            </h3>
            <div class="overflow-x-auto rounded-lg shadow">
                <table class="min-w-full text-sm divide-y" style="background: rgba(255,255,255,.95); border-color: {{ $KK_DIVIDER }};">
                    <thead style="background: {{ $KK_BLUE_ALT }};">
                        <tr><th class="py-2 px-3 text-left">Session</th><th class="py-2 px-3 text-left">Goal</th><th class="py-2 px-3 text-left w-40">Progress</th></tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: {{ $KK_DIVIDER }};">
                        @foreach($rows as $s)
                            <tr>
                                <td class="py-2 px-3 font-medium">{{ $s['title'] }}</td>
                                <td class="py-2 px-3" style="color: {{ $KK_NAVY }}CC">{{ $s['goal'] }}</td>
                                <td class="py-2 px-3">
                                    <div class="h-2 w-full rounded overflow-hidden" role="progressbar" aria-valuenow="{{ intval($s['progress']*100) }}" style="background: {{ $KK_DIVIDER }};">
                                        <div class="h-full" style="background:{{ $acc }};width:{{ $s['progress']*100 }}%"></div>
                                    </div>
                                    <p class="mt-0.5 text-xs text-right font-semibold" style="color: {{ $KK_BLUE }};">{{ intval($s['progress']*100) }}%</p>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    </section>

    {{-- 5 ▸ Vet Corner (dynamic) --}}
    <livewire:dogs.vet-corner :dog="$dog" />

    {{-- 6 ▸ Care-Team Notes (dynamic) --}}
    <livewire:dogs.care-notes :dog="$dog" />

    {{-- 7 ▸ Dietetician (dynamic) --}}
    <livewire:dogs.dietician :dog="$dog" />
</div>

@push('styles')
<style>
@keyframes shimmer{0%{background-position:0% 50%}100%{background-position:200% 50%}}
.text-shadow-white{ text-shadow:0 0 2px #fff }
[x-cloak]{ display:none !important; }
</style>
@endpush
