@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;

    $KK_NAVY     = '#03314C';
    $KK_BLUE     = '#076BA8';
    $KK_BLUE_ALT = '#DAEEFF';
    $KK_DIVIDER  = '#E2E8F0';
    $ACC_CC      = '#16A34A'; // green
    $ACC_SO      = '#F59E0B'; // yellow
    $ACC_TR      = '#6366F1'; // lavender

    $catColor = function (string $c) use ($ACC_CC,$ACC_SO,$ACC_TR) {
        return match(Str::slug($c)) {
            'comfort-confidence' => $ACC_CC,
            'sociability'        => $ACC_SO,
            'trainability'       => $ACC_TR,
            default              => '#64748B',
        };
    };

    // Determine media block for current step: YouTube (via $embedUrl) or PDF
    $hasPdf = $next && !empty($next['pdf']);
    $pdfUrl = null;
    if ($hasPdf) {
        // Assume file is on 'public' disk for demo; this yields /storage/… URL
        $pdfUrl = Storage::disk('public')->url($next['pdf']);
    }
@endphp

<section class="max-w-7xl mx-auto mt-12 border"
         style="background:#fff; border-color: {{ $KK_DIVIDER }}; color: {{ $KK_NAVY }};">

    {{-- Section header --}}
    <div class="px-6 py-4" style="background: {{ $KK_BLUE_ALT }}; border-bottom:1px solid {{ $KK_DIVIDER }}">
        <h2 class="text-xl font-bold">Training Program</h2>
    </div>

    {{-- Body --}}
    <div class="p-6 space-y-8">

        {{-- Progress bar --}}
        <div>
            <div class="flex items-center justify-between text-sm mb-2">
                <span style="color: {{ $KK_NAVY }}B3">{{ $done }} of {{ $total }} completed</span>
                <span class="font-semibold" style="color: {{ $KK_NAVY }}B3">
                    {{ $total ? intval(($done / max(1,$total)) * 100) : 0 }}%
                </span>
            </div>
            <div class="h-2 w-full border overflow-hidden" style="border-color: {{ $KK_DIVIDER }}; background:#F1F5F9;">
                <div class="h-full" style="width: {{ $total ? ($done/$total)*100 : 0 }}%; background: {{ $KK_BLUE }};"></div>
            </div>
        </div>

        {{-- Next module ONLY --}}
        <div class="border" style="border-color: {{ $KK_DIVIDER }}; background:#fff;">
            <div class="px-4 py-3" style="background: {{ $KK_BLUE_ALT }}; border-bottom:1px solid {{ $KK_DIVIDER }}">
                <h3 class="font-semibold">Next module</h3>
            </div>

            @if($next)
                @php
                    $cat    = $next['category'];
                    $slug   = Str::slug($cat);
                    $accent = $catColor($cat);
                @endphp
                <div class="p-6 space-y-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="space-y-1">
                            <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 border"
                                  style="color: {{ $accent }}; border-color: {{ $accent }}; background:#fff;">
                                {{ $cat }}
                            </span>
                            <h4 class="text-lg font-bold">{{ $next['title'] }}</h4>
                            <p class="text-sm" style="color: {{ $KK_NAVY }}CC">{{ $next['goal'] }}</p>
                            <p class="text-xs" style="color: {{ $KK_NAVY }}99">Step {{ $done + 1 }} of {{ $total }}</p>
                        </div>

                        <div class="flex items-center gap-2">
                            <button wire:click="markComplete"
                                    class="px-4 py-2 text-sm font-semibold border text-white"
                                    style="background: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }};">
                                Mark as complete → Next
                            </button>
                            <button wire:click="resetPlan"
                                    class="px-3 py-2 text-sm font-semibold border"
                                    style="background:#fff; border-color: {{ $KK_DIVIDER }}; color: {{ $KK_NAVY }};">
                                Reset
                            </button>
                        </div>
                    </div>

                    {{-- Media block (YouTube or PDF) --}}
                    <div class="border" style="border-color: {{ $KK_DIVIDER }}; background:#fff;">
                        <div class="px-4 py-2 text-sm font-semibold" style="background: {{ $KK_BLUE_ALT }}; border-bottom:1px solid {{ $KK_DIVIDER }}">
                            Module material
                        </div>
                        <div class="p-4">
                            @if ($embedUrl)
                                {{-- Responsive 16:9 YouTube embed (hard corners) --}}
                                <div style="position:relative; width:100%; padding-bottom:56.25%; background:#000; border:1px solid {{ $KK_DIVIDER }};">
                                    <iframe
                                        src="{{ $embedUrl }}"
                                        title="Training video"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                        allowfullscreen
                                        style="position:absolute; top:0; left:0; width:100%; height:100%; border:0;"
                                    ></iframe>
                                </div>
                                <div class="mt-3 text-xs" style="color: {{ $KK_NAVY }}99">
                                    If the video doesn't load, <a href="{{ $next['youtube'] }}" target="_blank" style="color: {{ $KK_BLUE }}; text-decoration: underline;">open on YouTube</a>.
                                </div>
                            @elseif ($hasPdf && $pdfUrl)
                                {{-- Embedded PDF viewer with download fallback --}}
                                <div class="mb-3 flex items-center justify-between">
                                    <span class="text-sm font-semibold">PDF: {{ basename($next['pdf']) }}</span>
                                    <a href="{{ $pdfUrl }}" target="_blank"
                                       class="px-3 py-1 text-sm font-semibold border"
                                       style="background:#fff; border-color: {{ $KK_BLUE }}; color: {{ $KK_BLUE }};">
                                        View / Download
                                    </a>
                                </div>
                                <div style="height: 640px; border:1px solid {{ $KK_DIVIDER }};">
                                    <iframe src="{{ $pdfUrl }}" title="Module PDF" style="width:100%; height:100%; border:0;"></iframe>
                                </div>
                            @else
                                <p class="text-sm" style="color: {{ $KK_NAVY }}99">No media attached for this module.</p>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                {{-- All done! --}}
                <div class="p-6">
                    <h4 class="text-lg font-bold">All modules completed 🎉</h4>
                    <p class="text-sm mt-1" style="color: {{ $KK_NAVY }}CC">
                        This dog has finished the demo training sequence.
                    </p>
                    <div class="mt-4">
                        <button wire:click="resetPlan"
                                class="px-4 py-2 text-sm font-semibold border"
                                style="background:#fff; border-color: {{ $KK_DIVIDER }}; color: {{ $KK_NAVY }};">
                            Start over
                        </button>
                    </div>
                </div>
            @endif
        </div>

        {{-- Optional: upcoming glance (next 2) --}}
        @if(!empty($upcoming))
            <div class="border" style="border-color: {{ $KK_DIVIDER }}; background:#fff;">
                <div class="px-4 py-3" style="background: {{ $KK_BLUE_ALT }}; border-bottom:1px solid {{ $KK_DIVIDER }}">
                    <h3 class="font-semibold">Upcoming</h3>
                </div>
                <div class="p-6">
                    <ul class="space-y-3 text-sm">
                        @foreach($upcoming as $u)
                            @php $acc = $catColor($u['category']); @endphp
                            <li class="flex items-start gap-3">
                                <span class="inline-flex items-center text-[10px] font-semibold px-2 py-0.5 border"
                                      style="color: {{ $acc }}; border-color: {{ $acc }}; background:#fff;">
                                    {{ $u['category'] }}
                                </span>
                                <div>
                                    <p class="font-semibold">{{ $u['title'] }}</p>
                                    <p class="text-xs" style="color: {{ $KK_NAVY }}99">{{ $u['goal'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>
</section>
