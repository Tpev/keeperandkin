@php
    $KK_NAVY     = '#03314C';
    $KK_BLUE     = '#076BA8';
    $KK_BLUE_ALT = '#DAEEFF';
    $KK_DIVIDER  = '#E2E8F0';
@endphp

<div class="max-w-7xl mx-auto mt-12 p-6 space-y-6" style="color:{{ $KK_NAVY }};">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Learn</h1>
    </div>

    @if ($programs->isEmpty())
        <div class="border rounded p-6" style="border-color: {{ $KK_DIVIDER }};">
            <p class="text-sm" style="color:{{ $KK_NAVY }}CC">
                No certification programs are currently available to you.
            </p>
        </div>
    @else
        <div class="grid md:grid-cols-2 gap-5">
            @foreach ($programs as $p)
                @php
                    $en = $enrollments[$p->id] ?? null;
                    $status = $en->status ?? null; // enrolled|in_progress|completed|null
                @endphp
                <a href="{{ route('learn.show', $p->slug) }}"
                   class="block border rounded p-4 hover:shadow"
                   style="border-color: {{ $KK_DIVIDER }}; background:#fff;">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">{{ $p->title }}</h2>
                        @if ($p->difficulty)
                            <span class="text-xs px-2 py-0.5 rounded-full border" style="border-color:{{ $KK_BLUE }}; color:{{ $KK_BLUE }};">
                                {{ ucfirst($p->difficulty) }}
                            </span>
                        @endif
                    </div>
                    @if ($p->description)
                        <p class="text-sm mt-1" style="color:{{ $KK_NAVY }}B3">{{ \Illuminate\Support\Str::limit($p->description, 140) }}</p>
                    @endif

                    <div class="mt-3 text-xs">
                        <span class="inline-block px-2 py-0.5 rounded border" style="border-color:{{ $KK_DIVIDER }};">
                            {{ $p->flags_count }} step{{ $p->flags_count === 1 ? '' : 's' }}
                        </span>

                        @if ($status)
                            <span class="ml-2 inline-block px-2 py-0.5 rounded border"
                                  style="border-color:{{ $KK_BLUE }}; color:{{ $KK_BLUE }};">
                                {{ str_replace('_',' ', $status) }}
                            </span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
