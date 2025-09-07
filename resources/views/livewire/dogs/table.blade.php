<div>
    @push('styles')
    <style>
      .kk-table {
        --kk-navy:#03314C;
        --kk-blue:#076BA8;
        --kk-blue-alt:#DAEEFF;
        --kk-divider:#E2E8F0;
        --kk-danger:#DC2626;
      }
      .kk-table .ts-table{ border-radius: 1.25rem; overflow: hidden; }
      .kk-table thead th{
        background: var(--kk-blue-alt) !important;
        color: var(--kk-navy) !important;
        font-weight: 700 !important;
        border-bottom: 1px solid var(--kk-divider) !important;
      }
      .kk-table tbody tr{ border-bottom: 1px solid var(--kk-divider); transition: background .15s ease; }
      .kk-table tbody tr:hover{ background: rgba(218,238,255,.55); }
      .kk-table tbody td{ color: var(--kk-navy); }

      .kk-avatar{ width: 42px; height: 42px; border-radius: 999px; object-fit: cover; border: 3px solid var(--kk-blue-alt); box-shadow: 0 2px 8px rgba(7,107,168,.12); }
      .kk-name{ font-weight: 800; letter-spacing:.2px; }
      .kk-breed{ opacity:.8; font-size:.85rem; }

      .kk-score{
        display:inline-flex; align-items:center; justify-content:center;
        font-size:.8rem; font-weight:700;
        background: var(--kk-blue-alt); color: var(--kk-blue);
        padding:.25rem .65rem; border-radius:.75rem;
      }
      .kk-flag{
        display:inline-flex; align-items:center; font-size:.75rem;
        font-weight:600; background: var(--kk-danger);
        color:white; padding:.25rem .6rem; border-radius:999px;
      }
		.kk-flag-none{ background:#E2E8F0; color:#374151; }

      /* Pagination footer */
      .kk-table .ts-table-footer{ border-top:1px solid var(--kk-divider); background:#fff; }
    </style>
    @endpush

    <div class="kk-table">
        <x-ts-table
            :$headers
            :$rows
            link="{{ url('/dogs/{id}') }}"  {{-- row click-through --}}
            striped
            hover
            paginate
            id="dogs"
        >
            {{-- DOG (avatar + name + breed) --}}
            @interact('column_name', $row)
                @php
                    $dogName  = $row->name ?? ($row['name'] ?? 'Unnamed');
                    $breed    = $row->breed ?? ($row['breed'] ?? 'Mixed');
                    $photo    = $row->photo_path ?? ($row['photo_path'] ?? null);
                    $photoUrl = $photo ? asset('storage/'.$photo) : 'https://placehold.co/96x96?text=Dog';
                @endphp
                <div class="flex items-center gap-3">
                    <img src="{{ $photoUrl }}" alt="Photo of {{ $dogName }}" class="kk-avatar" loading="lazy">
                    <div>
                        <div class="kk-name">{{ $dogName }}</div>
                        <div class="kk-breed">{{ $breed }}</div>
                    </div>
                </div>
            @endinteract

            {{-- AGE (optional: customize formatting) --}}
            @interact('column_age', $row)
                {{ $row->age ?? '—' }}
            @endinteract

            {{-- SEX (optional: capitalize) --}}
            @interact('column_sex', $row)
                {{ isset($row->sex) ? \Illuminate\Support\Str::ucfirst($row->sex) : '—' }}
            @endinteract

{{-- SCORE (latest evaluation) --}}
@interact('column_score', $row)
    @php
        $score = $row->latestEvaluation->score ?? null;
        // color by threshold: <50 red, <75 amber, else blue
        $scoreInt = is_numeric($score) ? (int)$score : null;
        $color = $scoreInt === null
            ? '#64748B' // slate for missing
            : ($scoreInt < 50 ? 'var(--kk-danger)' : ($scoreInt < 75 ? '#F59E0B' : 'var(--kk-blue)'));
        $bg = $scoreInt === null ? '#F1F5F9' : 'var(--kk-blue-alt)';
    @endphp

    <span class="kk-score" style="background: {{ $bg }}; color: {{ $color }}">
        {{ $scoreInt ?? '—' }}
    </span>
@endinteract


{{-- FLAG (latest evaluation red_flags) --}}
@interact('column_flag', $row)
    @php
        $flags = (array)($row->latestEvaluation->red_flags ?? []);
        $count = count($flags);
    @endphp

    @if($count > 0)
        @php
            $pretty = \Illuminate\Support\Str::headline($flags[0]);
            $title  = implode(', ', array_map(fn($f) => \Illuminate\Support\Str::headline($f), $flags));
        @endphp
        <span class="kk-flag" title="{{ $title }}">
            {{ $pretty }}@if($count > 1) <span class="ml-1 opacity-90">+{{ $count - 1 }}</span>@endif
        </span>
    @else
        <span class="kk-flag kk-flag-none" title="No red flags">No red flags</span>
    @endif
@endinteract


            {{-- ACTIONS --}}
            @interact('column_action', $row)
                <div class="flex items-center gap-1.5">
                    <x-ts-button.circle
                        color="gray"
                        icon="eye"
                        href="{{ route('dogs.show', $row) }}"
                        title="View" />
                    <x-ts-button.circle
                        color="blue"
                        icon="pencil"
                        href="{{ route('dogs.edit', $row) }}"
                        title="Edit" />
                </div>
            @endinteract

        </x-ts-table>
    </div>
</div>
