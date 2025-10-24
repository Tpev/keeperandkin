@php
    use Illuminate\Support\Str;

    $KK_NAVY     = '#03314C';
    $KK_BLUE     = '#076BA8';
    $KK_BLUE_ALT = '#DAEEFF';
    $KK_DIVIDER  = '#E2E8F0';
@endphp

<section class="max-w-7xl mx-auto mt-12 border"
         style="background:#fff; border-color: {{ $KK_DIVIDER }}; color: {{ $KK_NAVY }};">

    {{-- Header --}}
    <div class="px-6 py-4" style="background: {{ $KK_BLUE_ALT }}; border-bottom:1px solid {{ $KK_DIVIDER }}">
        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <h2 class="text-xl font-bold">{{ $program->title }}</h2>
                @if (filled($program->description))
                    <p class="text-sm" style="color: {{ $KK_NAVY }}CC">{{ $program->description }}</p>
                @endif
            </div>

            <div class="flex items-center gap-3">
                @if ($program->difficulty)
                    <span class="text-xs px-2 py-0.5 rounded-full border"
                          style="border-color:{{ $KK_BLUE }}; color:{{ $KK_BLUE }};">
                        {{ ucfirst($program->difficulty) }}
                    </span>
                @endif

                <button wire:click="startOrResume"
                        class="px-3 py-2 text-sm font-semibold border"
                        style="background:#fff; border-color: {{ $KK_BLUE }}; color: {{ $KK_BLUE }};">
                    {{ $total > 0 && $done > 0 && $done < $total ? 'Resume' : ($done >= $total && $total > 0 ? 'Restart' : 'Start') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Body --}}
    <div class="p-6 space-y-8">

        {{-- Progress --}}
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

        {{-- Next step (flag) --}}
        <div class="border" style="border-color: {{ $KK_DIVIDER }}; background:#fff;">
            <div class="px-4 py-3" style="background: {{ $KK_BLUE_ALT }}; border-bottom:1px solid {{ $KK_DIVIDER }}">
                <h3 class="font-semibold">Next step</h3>
            </div>

            @if($nextFlag)
                <div class="p-6 space-y-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="space-y-1">
                            <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 border"
                                  style="color: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }}; background:#fff;">
                                Training
                            </span>
                            <h4 class="text-lg font-bold">{{ $nextFlag->name }}</h4>
                            @if(filled($nextFlag->description))
                                <p class="text-sm" style="color: {{ $KK_NAVY }}CC">{{ $nextFlag->description }}</p>
                            @endif
                            <p class="text-xs" style="color: {{ $KK_NAVY }}99">
                                Step {{ ($done + 1) }} of {{ $total }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <button wire:click="markStepComplete"
                                    class="px-4 py-2 text-sm font-semibold border text-white"
                                    style="background: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }};">
                                Mark as complete → Next
                            </button>
                            <button wire:click="resetProgress"
                                    class="px-3 py-2 text-sm font-semibold border"
                                    style="background:#fff; border-color: {{ $KK_DIVIDER }}; color: {{ $KK_NAVY }};">
                                Reset
                            </button>
                        </div>
                    </div>

                    {{-- Media block from the first session of this flag --}}
                    <div class="border" style="border-color: {{ $KK_DIVIDER }}; background:#fff;">
                        <div class="px-4 py-2 text-sm font-semibold" style="background: {{ $KK_BLUE_ALT }}; border-bottom:1px solid {{ $KK_DIVIDER }}">
                            Materials
                        </div>
                        <div class="p-4 space-y-4">
                            @php
                                $firstSession = $nextFlag->sessions->first();
                            @endphp

                            @if($firstSession)
                                @if ($embedUrl)
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
                                    <div class="text-xs" style="color: {{ $KK_NAVY }}99">
                                        If the video doesn't load, <a href="{{ $firstSession->video_url }}" target="_blank" style="color: {{ $KK_BLUE }}; text-decoration: underline;">open on YouTube</a>.
                                    </div>
                                @elseif ($pdfUrl)
                                    <div class="mb-3 flex items-center justify-between">
                                        <span class="text-sm font-semibold">
                                            PDF: {{ basename($firstSession->pdf_path) }}
                                        </span>
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
                                    <p class="text-sm" style="color: {{ $KK_NAVY }}99">No video or PDF found on the first session for this step.</p>
                                @endif
                            @else
                                <p class="text-sm" style="color: {{ $KK_NAVY }}99">No sessions attached to this step.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Optional: list all sessions attached to this flag --}}
                    @if($nextFlag->sessions->count() > 1)
                        <div class="border" style="border-color: {{ $KK_DIVIDER }};">
                            <div class="px-4 py-2 text-sm font-semibold" style="background: {{ $KK_BLUE_ALT }}; border-bottom:1px solid {{ $KK_DIVIDER }}">
                                All materials in this step
                            </div>
                            <ul class="p-4 space-y-2 text-sm">
                                @foreach($nextFlag->sessions as $s)
                                    <li class="flex items-center justify-between border rounded px-3 py-2" style="border-color: {{ $KK_DIVIDER }};">
                                        <div>
                                            <div class="font-medium">{{ $s->name }}</div>
                                            @if(filled($s->goal))
                                                <div class="text-xs" style="color: {{ $KK_NAVY }}99">{{ $s->goal }}</div>
                                            @endif
                                        </div>
                                        <div class="text-xs flex items-center gap-2">
                                            @if(filled($s->video_url))
                                                <a href="{{ $s->video_url }}" target="_blank" class="underline" style="color:{{ $KK_BLUE }};">Video</a>
                                            @endif
                                            @if(filled($s->pdf_path))
                                                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($s->pdf_path) }}" target="_blank" class="underline" style="color:{{ $KK_BLUE }};">PDF</a>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @else
                <div class="p-6">
                    @if(($total ?? 0) === 0)
                        <h4 class="text-lg font-bold">No steps in this program</h4>
                        <p class="text-sm mt-1" style="color: {{ $KK_NAVY }}CC">
                            An admin can attach training flags to this certification program.
                        </p>
                    @else
                        <h4 class="text-lg font-bold">All steps completed 🎉</h4>
                        <p class="text-sm mt-1" style="color: {{ $KK_NAVY }}CC">
                            You’ve finished this certification program.
                        </p>
                        <div class="mt-4">
                            <button wire:click="resetProgress"
                                    class="px-4 py-2 text-sm font-semibold border"
                                    style="background:#fff; border-color: {{ $KK_DIVIDER }}; color: {{ $KK_NAVY }};">
                                Start over
                            </button>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Upcoming glance (next 2 flags) --}}
        @if(!empty($upcoming))
            <div class="border" style="border-color: {{ $KK_DIVIDER }}; background:#fff;">
                <div class="px-4 py-3" style="background: {{ $KK_BLUE_ALT }}; border-bottom:1px solid {{ $KK_DIVIDER }}">
                    <h3 class="font-semibold">Upcoming</h3>
                </div>
                <div class="p-6">
                    <ul class="space-y-3 text-sm">
                        @foreach($upcoming as $u)
                            <li class="flex items-start gap-3">
                                <span class="inline-flex items-center text-[10px] font-semibold px-2 py-0.5 border"
                                      style="color: {{ $KK_BLUE }}; border-color: {{ $KK_BLUE }}; background:#fff;">
                                    Training
                                </span>
                                <div>
                                    <p class="font-semibold">{{ $u->name }}</p>
                                    @if(filled($u->description))
                                        <p class="text-xs" style="color: {{ $KK_NAVY }}99">{{ \Illuminate\Support\Str::limit($u->description, 120) }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Full outline (optional) --}}
        <div class="border" style="border-color: {{ $KK_DIVIDER }}; background:#fff;">
            <div class="px-4 py-3" style="background: {{ $KK_BLUE_ALT }}; border-bottom:1px solid {{ $KK_DIVIDER }}">
                <h3 class="font-semibold">Program outline</h3>
            </div>
            <ul class="p-6 space-y-2 text-sm">
                @foreach($program->flags as $f)
                    @php
                        // Mark completed with a check if in done subset
                        $completed = \Illuminate\Support\Facades\DB::table('flag_user')
                            ->where('user_id', \Illuminate\Support\Facades\Auth::id())
                            ->where('training_flag_id', $f->id)
                            ->where('status','completed')
                            ->exists();
                    @endphp
                    <li class="flex items-center justify-between border rounded px-3 py-2"
                        style="border-color: {{ $KK_DIVIDER }};">
                        <div class="font-medium">{{ $f->name }}</div>
                        <div class="text-xs">
                            @if($completed)
                                <span class="px-2 py-0.5 rounded-full border" style="border-color:{{ $KK_BLUE }}; color:{{ $KK_BLUE }};">Completed</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full border" style="border-color:#CBD5E1; color:#64748B;">Pending</span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

    </div>
</section>
