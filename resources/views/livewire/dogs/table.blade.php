{{-- resources/views/livewire/dogs/table.blade.php --}}
<div>
    @push('styles')
    <style>
      .kk-table {
        --kk-navy:#03314C;
        --kk-blue:#076BA8;
        --kk-blue-alt:#DAEEFF;
        --kk-divider:#E2E8F0;

        --kk-scale-ok:#94A3B8;
        --kk-scale-red:#DC2626;
        --kk-scale-orange:#F97316;
        --kk-scale-yellow:#FFCC00;
        --kk-scale-green:#16A34A;
      }
      .kk-table *, .kk-table *::before, .kk-table *::after { border-radius: 0 !important; }

      .kk-toolbar { border:1px solid var(--kk-divider); background:#fff; }
      .kk-toolbar label { font-size:.75rem; color: color-mix(in oklab, var(--kk-navy) 70%, #000 0%); }
      .kk-toolbar input, .kk-toolbar select {
        border:1px solid var(--kk-divider); background:#fff; color:var(--kk-navy); padding:.5rem .6rem; font-size:.875rem;
      }

      .kk-table .ts-table{ border:1px solid var(--kk-divider); overflow: clip; }
      .kk-table thead th{
        background: var(--kk-blue-alt) !important;
        color: var(--kk-navy) !important;
        font-weight: 700 !important;
        border-bottom: 1px solid var(--kk-divider) !important;
      }
      .kk-table tbody tr{ border-bottom: 1px solid var(--kk-divider); transition: background .15s ease; }
      .kk-table tbody tr:hover{ background: rgba(218,238,255,.45); }
      .kk-table tbody td{ color: var(--kk-navy); }

      .kk-avatar{ width: 48px; height: 48px; object-fit: cover; border: 2px solid var(--kk-blue-alt); }
      .kk-name{ font-weight: 800; letter-spacing:.2px; }
      .kk-breed{ opacity:.8; font-size:.85rem; }

      .kk-score{
        display:inline-flex; align-items:center; justify-content:center;
        font-size:.8rem; font-weight:700; padding:.25rem .5rem;
        border:1px solid var(--kk-divider); background:#fff; color: var(--kk-scale-ok);
        min-width: 2.25rem; text-align:center;
      }
      .kk-score--red    { color: var(--kk-scale-red);    border-color: var(--kk-scale-red); }
      .kk-score--orange { color: var(--kk-scale-orange); border-color: var(--kk-scale-orange); }
      .kk-score--yellow { color: var(--kk-scale-yellow); border-color: var(--kk-scale-yellow); }
      .kk-score--green  { color: var(--kk-scale-green);  border-color: var(--kk-scale-green); }

      .kk-scores-3 { display:flex; align-items:center; gap:.35rem; }
      .kk-scores-3 .kk-score { min-width:2.25rem; }
      .kk-score-label { font-size:.675rem; font-weight:800; letter-spacing:.2px; margin-right:.15rem; color:var(--kk-scale-ok); }

      .kk-flag{
        display:inline-flex; align-items:center; font-size:.75rem; font-weight:700;
        padding:.25rem .5rem; border:1px solid var(--kk-scale-red); background: #fff; color: var(--kk-scale-red);
      }
      .kk-flag-none{ border-color: var(--kk-divider); color:#374151; }

      .kk-btn {
        display:inline-flex; align-items:center; gap:.35rem;
        padding:.4rem .6rem; font-size:.8125rem; font-weight:700;
        border:1px solid var(--kk-divider); background:#fff; color:#076BA8;
      }
      .kk-btn--primary { background: #076BA8; color:#fff; border-color: #076BA8; }
      .kk-btn svg { width: 14px; height: 14px; }

      .kk-table .ts-table-footer{ border-top:1px solid var(--kk-divider); background:#fff; }
    </style>
    @endpush

    <div class="kk-table">

        {{-- Toolbar --}}
        <div class="kk-toolbar p-3 mb-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <div class="lg:col-span-2">
                    <label class="block mb-1">Search</label>
                    <input type="text" placeholder="Name, breed, serial…"
                           wire:model.debounce.400ms="q" />
                </div>

                <div>
                    <label class="block mb-1">Sex</label>
                    <select wire:model="sex">
                        <option value="">Any</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>

                <div>
                    <label class="block mb-1">Flags</label>
                    <select wire:model="flags">
                        <option value="">Any</option>
                        <option value="with">With red flags</option>
                        <option value="none">No red flags</option>
                    </select>
                </div>

                <div>
                    <label class="block mb-1">Min. Score</label>
                    <input type="number" min="0" max="100" step="1" wire:model.lazy="scoreMin" placeholder="e.g., 70" />
                </div>

                <div class="flex items-end">
                    <button type="button" wire:click="resetFilters"
                            class="kk-btn" title="Reset filters">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4v6h6M20 20v-6h-6M5.636 18.364A9 9 0 1118.364 5.636"/>
                        </svg>
                        Reset
                    </button>
                </div>
            </div>
        </div>

        <x-ts-table
            :$headers
            :$rows
            link="{{ url('/dogs/{id}') }}"  {{-- row click-through --}}
            hover
            paginate
            id="dogs"
        >
            {{-- DOG --}}
            @interact('column_name', $row)
                @php
                    $dogName  = $row->name ?? ($row['name'] ?? 'Unnamed');
                    $breed    = $row->breed ?? ($row['breed'] ?? 'Mixed');
                    $photo    = $row->photo ?? ($row->photo_path ?? null);
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

            {{-- AGE --}}
            @interact('column_age', $row)
                {{ $row->age ?? '—' }}
            @endinteract

            {{-- SEX --}}
            @interact('column_sex', $row)
                {{ isset($row->sex) ? \Illuminate\Support\Str::ucfirst($row->sex) : '—' }}
            @endinteract

            {{-- SCORES (C/S/T) --}}
            @interact('column_score', $row)
                @php
                    $ev = $row->latestEvaluation ?? ($row['latestEvaluation'] ?? null);

                    $toArray = function ($maybe) {
                        if (is_array($maybe)) return $maybe;
                        if (is_object($maybe)) return get_object_vars($maybe);
                        return [];
                    };

                    $raw   = $ev->category_scores ?? ($ev['category_scores'] ?? null);
                    $byCat = $toArray($raw);

                    $pick = function(array $a, array $keys, $fallback = null) {
                        foreach ($keys as $k) {
                            if (array_key_exists($k, $a) && $a[$k] !== null && $a[$k] !== '') {
                                return (int) $a[$k];
                            }
                        }
                        return $fallback;
                    };

                    $c = $pick($byCat, ['Comfort & Confidence','Confidence','comfort_confidence'], null);
                    $s = $pick($byCat, ['Sociability','Social','sociability'], null);
                    $t = $pick($byCat, ['Trainability','trainability'], null);

                    $norm = function ($v) { if (!is_numeric($v)) return null; $v=(int)$v; return max(0,min(100,$v)); };
                    $c = $norm($c); $s = $norm($s); $t = $norm($t);

                    $classFor = function ($v) {
                        if ($v === null) return '';
                        if ($v <= 25)  return 'kk-score--red';
                        if ($v <= 50)  return 'kk-score--orange';
                        if ($v <= 75)  return 'kk-score--yellow';
                        return 'kk-score--green';
                    };
                @endphp

                <div class="kk-scores-3" title="Comfort / Sociability / Trainability">
                    <span class="kk-score-label">C</span>
                    <span class="kk-score {{ $classFor($c) }}" title="Comfort & Confidence">{{ $c === null ? '—' : $c }}</span>

                    <span class="kk-score-label">S</span>
                    <span class="kk-score {{ $classFor($s) }}" title="Sociability">{{ $s === null ? '—' : $s }}</span>

                    <span class="kk-score-label">T</span>
                    <span class="kk-score {{ $classFor($t) }}" title="Trainability">{{ $t === null ? '—' : $t }}</span>
                </div>
            @endinteract

            {{-- TEAM (Admins only) --}}
            @interact('column_team', $row)
                @php
                    $isAdminLocal = (auth()->user()->is_admin ?? false) ? true : false;
                    $currentId    = (int) ($row->team_id ?? 0);
                    $teamsList    = $isAdminLocal
                        ? \App\Models\Team::query()->select('id','name')->orderBy('name')->get()
                        : collect();
                    $currentName  = $row->team->name ?? ($teamsList->firstWhere('id', $currentId)->name ?? '—');
                @endphp

                @if($isAdminLocal)
                    <div
                        x-data
                        @click.stop
                        @mousedown.stop
                        @mouseup.stop
                        @keydown.stop
                        @focus.stop
                        @change.stop
                        onclick="event.stopPropagation()"
                        onmousedown="event.stopPropagation()"
                        onmouseup="event.stopPropagation()"
                        onkeydown="event.stopPropagation()"
                        onfocus="event.stopPropagation()"
                        onchange="event.stopPropagation()"
                    >
                        <label class="sr-only" for="team-{{ $row->id }}">Team</label>
                        <select id="team-{{ $row->id }}"
                                class="border px-2 py-1"
                                wire:change="updateTeam({{ $row->id }}, $event.target.value)"
                                @click.stop
                                @mousedown.stop
                                @mouseup.stop
                                @keydown.stop
                                @focus.stop
                                @change.stop
                                onclick="event.stopPropagation()"
                                onmousedown="event.stopPropagation()"
                                onmouseup="event.stopPropagation()"
                                onkeydown="event.stopPropagation()"
                                onfocus="event.stopPropagation()"
                                onchange="event.stopPropagation()"
                        >
                            @foreach($teamsList as $t)
                                <option value="{{ $t->id }}" @selected($t->id === $currentId)>{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    {{ $currentName }}
                @endif
            @endinteract

            {{-- FLAG --}}
            @interact('column_flag', $row)
                @php
                    $ev    = $row->latestEvaluation ?? null;
                    $flags = $ev?->red_flags ?? [];
                    if (is_object($flags)) $flags = get_object_vars($flags);
                    if (!is_array($flags)) $flags = [];
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
                <div class="flex items-center gap-2">
                    <a class="kk-btn" href="{{ route('dogs.show', $row) }}" title="View">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z"/>
                        </svg>
                        View
                    </a>
                    <a class="kk-btn kk-btn--primary" href="{{ route('dogs.edit', $row) }}" title="Edit">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15.232 5.232l3.536 3.536M4 20h4l10.5-10.5a2.5 2.5 0 10-3.536-3.536L4 16v4z"/>
                        </svg>
                        Edit
                    </a>
                </div>
            @endinteract

        </x-ts-table>
    </div>
</div>
