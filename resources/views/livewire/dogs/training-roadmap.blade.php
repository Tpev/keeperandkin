@php
    use Illuminate\Support\Facades\Storage;

    $KK_NAVY     = '#03314C';
    $KK_BLUE     = '#076BA8';
    $KK_BLUE_ALT = '#DAEEFF';
    $KK_DIVIDER  = '#E2E8F0';
    $ACC_CC      = '#16A34A'; // green
    $ACC_SO      = '#F59E0B'; // yellow
    $ACC_TR      = '#6366F1'; // lavender

    $catLabel = function (?string $key) {
        return match($key) {
            'comfort_confidence' => 'Comfort & Confidence',
            'sociability'        => 'Sociability',
            'trainability'       => 'Trainability',
            default              => 'General',
        };
    };

    $catColor = function (?string $key) use ($ACC_CC,$ACC_SO,$ACC_TR) {
        return match($key) {
            'comfort_confidence' => $ACC_CC,
            'sociability'        => $ACC_SO,
            'trainability'       => $ACC_TR,
            default              => '#64748B',
        };
    };

    $hasNext      = !is_null($nextAssignment ?? null);
    $nextSession  = $hasNext ? $nextAssignment->session : null;
    $nextCategory = $nextSession?->category;

    $hasVideo = $nextSession && filled($nextSession->video_url);
    $hasPdf   = $nextSession && filled($nextSession->pdf_path);
    $pdfUrl   = $hasPdf ? Storage::disk('public')->url($nextSession->pdf_path) : null;
@endphp

<section class="max-w-7xl mx-auto mt-12 border"
         style="background:#fff; border-color: {{ $KK_DIVIDER }}; color: {{ $KK_NAVY }};">

    {{-- Section header --}}
    <div class="px-6 py-4" style="background: {{ $KK_BLUE_ALT }}; border-bottom:1px solid {{ $KK_DIVIDER }}">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold">Training Program</h2>

            @if(($total ?? 0) === 0)
                <button wire:click="generateProgram"
                        class="px-3 py-2 text-sm font-semibold border"
                        style="background:#fff; border-color: {{ $KK_BLUE }}; color: {{ $KK_BLUE }};">
                    Generate program for this dog
                </button>
            @endif
        </div>
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

            @if($hasNext && $nextSession)
                @php
                    $accent = $catColor($nextCategory);
                    $label  = $catLabel($nextCategory);
                @endphp

                <div class="p-6 space-y-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="space-y-1">
                            <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 border"
                                  style="color: {{ $accent }}; border-color: {{ $accent }}; background:#fff;">
                                {{ $label }}
                            </span>
                            <h4 class="text-lg font-bold">{{ $nextSession->name }}</h4>
                            @if(filled($nextSession->goal ?? null))
                                <p class="text-sm" style="color: {{ $KK_NAVY }}CC">{{ $nextSession->goal }}</p>
                            @endif
                            <p class="text-xs" style="color: {{ $KK_NAVY }}99">
                                Step {{ ($done + 1) }} of {{ $total }}
                            </p>
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
                                {{-- Responsive 16:9 YouTube embed --}}
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
                                    If the video doesn't load, <a href="{{ $nextSession->video_url }}" target="_blank" style="color: {{ $KK_BLUE }}; text-decoration: underline;">open on YouTube</a>.
                                </div>
                            @elseif ($hasPdf && $pdfUrl)
                                {{-- Embedded PDF viewer with download fallback --}}
                                <div class="mb-3 flex items-center justify-between">
                                    <span class="text-sm font-semibold">PDF: {{ basename($nextSession->pdf_path) }}</span>
                                    <a href="{{ $pdfUrl }}" target="_blank"
                                       class="px-3 py-1 text-sm font-semibold border"
                                       style="background:#fff; border-color: {{ $KK_BLUE }}; color: {{ $KK_BLUE }};">
                                        View / Download
                                    </a>
                                </div>
                                <div style="height: 640px; border:1px solid {{ $KK_DIVIDER }};">
                                    <iframe src="{{ $pdfUrl }}" title="Module PDF" style="width:100%; height:100%; border:0;"></iframe>
                                </div>
                            @elseif ($hasVideo)
                                {{-- Fallback: video present but couldn't embed --}}
                                <a href="{{ $nextSession->video_url }}" target="_blank"
                                   class="inline-flex items-center px-3 py-2 text-sm font-semibold border"
                                   style="background:#fff; border-color: {{ $KK_BLUE }}; color: {{ $KK_BLUE }};">
                                    Open video
                                </a>
                            @else
                                <p class="text-sm" style="color: {{ $KK_NAVY }}99">No media attached for this module.</p>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="p-6">
                    @if(($total ?? 0) === 0)
                        <h4 class="text-lg font-bold">No training program yet</h4>
                        <p class="text-sm mt-1" style="color: {{ $KK_NAVY }}CC">
                            Click “Generate program for this dog” above to build a plan from your training sessions.
                        </p>
                    @else
                        <h4 class="text-lg font-bold">All modules completed 🎉</h4>
                        <p class="text-sm mt-1" style="color: {{ $KK_NAVY }}CC">
                            This dog has finished the current training sequence.
                        </p>
                        <div class="mt-4">
                            <button wire:click="resetPlan"
                                    class="px-4 py-2 text-sm font-semibold border"
                                    style="background:#fff; border-color: {{ $KK_DIVIDER }}; color: {{ $KK_NAVY }};">
                                Start over
                            </button>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Upcoming glance (next 2) --}}
        @if(!empty($upcoming))
            <div class="border" style="border-color: {{ $KK_DIVIDER }}; background:#fff;">
                <div class="px-4 py-3" style="background: {{ $KK_BLUE_ALT }}; border-bottom:1px solid {{ $KK_DIVIDER }}">
                    <h3 class="font-semibold">Upcoming</h3>
                </div>
                <div class="p-6">
                    <ul class="space-y-3 text-sm">
                        @foreach($upcoming as $u)
                            @php
                                $cat  = $u->session?->category;
                                $acc  = $catColor($cat);
                                $lab  = $catLabel($cat);
                                $goal = $u->session?->goal;
                            @endphp
                            <li class="flex items-start gap-3">
                                <span class="inline-flex items-center text-[10px] font-semibold px-2 py-0.5 border"
                                      style="color: {{ $acc }}; border-color: {{ $acc }}; background:#fff;">
                                    {{ $lab }}
                                </span>
                                <div>
                                    <p class="font-semibold">{{ $u->session?->name }}</p>
                                    @if(filled($goal))
                                        <p class="text-xs" style="color: {{ $KK_NAVY }}99">{{ $goal }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>
</section>
