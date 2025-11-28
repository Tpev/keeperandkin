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
        border:1px solid var(--kk-divider); background:#fff; color:var(--kk-navy);
        padding:.5rem .6rem; font-size:.875rem;
      }

      /* ---- TABLE + CELLS ---- */
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

      /* Allow dropdown to overflow properly */
      .kk-table td { overflow: visible !important; }

      /* ---- FIX TEAM SELECT ARROW OVERLAP ---- */
      .kk-table select {
          padding-right: 2rem !important;  /* Space for native arrow */
          min-width: 160px !important;      /* Ensure text doesn't push arrow */
          background-position: right .5rem center;
          position: relative;
          z-index: 5;
      }

      /* ---- DOG DISPLAY ---- */
      .kk-avatar{ width: 48px; height: 48px; object-fit: cover; border: 2px solid var(--kk-blue-alt); }
      .kk-name{ font-weight: 800; letter-spacing:.2px; }
      .kk-breed{ opacity:.8; font-size:.85rem; }

      /* ---- SCORE BADGES ---- */
      .kk-score{
        display:inline-flex; align-items:center; justify-content:center;
        font-size:.8rem; font-weight:700; padding:.25rem .5rem;
        border:1px solid var(--kk-divider); background:#fff;
        color: var(--kk-scale-ok);
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

      /* ---- BUTTONS ---- */
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

        {{-- Toolbar – FRONT-END ONLY --}}
        <div class="kk-toolbar p-3 mb-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <div class="lg:col-span-2">
                    <label class="block mb-1">Search</label>
                    <input id="dog-search" type="text" placeholder="Name, breed, serial…" />
                </div>

                <div>
                    <label class="block mb-1">Sex</label>
                    <select id="dog-sex">
                        <option value="">Any</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>

                <div>
                    <label class="block mb-1">Flags</label>
                    <select id="dog-flags">
                        <option value="">Any</option>
                        <option value="with">With red flags</option>
                        <option value="none">No red flags</option>
                    </select>
                </div>

                <div></div>

                <div class="flex items-end">
                    <button type="button" id="dog-reset-filters" class="kk-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4v6h6M20 20v-6h-6M5.636 18.364A9 9 0 1118.364 5.636"/>
                        </svg>
                        Reset
                    </button>
                </div>
            </div>
        </div>

        {{-- TABLE --}}
        <x-ts-table
            :$headers
            :$rows
            link="{{ url('/dogs/{id}') }}"
            hover
            paginate
            id="dogs"
        >
            @interact('column_name', $row)
                @php
                    $dogName  = $row->name ?? 'Unnamed';
                    $breed    = $row->breed ?? 'Mixed';
                    $photo    = $row->photo ?? $row->photo_path ?? null;
                    $photoUrl = $photo ? asset('storage/'.$photo) : 'https://placehold.co/96x96?text=Dog';
                @endphp
                <div class="flex items-center gap-3">
                    <img src="{{ $photoUrl }}" class="kk-avatar" loading="lazy">
                    <div>
                        <div class="kk-name">{{ $dogName }}</div>
                        <div class="kk-breed">{{ $breed }}</div>
                    </div>
                </div>
            @endinteract

            @interact('column_age', $row)
                {{ $row->age ?? '—' }}
            @endinteract

            @interact('column_sex', $row)
                {{ ucfirst($row->sex ?? '—') }}
            @endinteract

            @interact('column_score', $row)
                @php
                    $ev = $row->latestEvaluation;

                    $scores = $ev?->category_scores ?? [];

                    $c = $scores['Comfort & Confidence'] ?? $scores['Confidence'] ?? null;
                    $s = $scores['Sociability'] ?? null;
                    $t = $scores['Trainability'] ?? null;

                    $norm = fn($v)=> is_numeric($v)?max(0,min(100,$v)):null;

                    $c=$norm($c); $s=$norm($s); $t=$norm($t);

                    $class = fn($v)=>$v===null?'':(
                        $v<=25?'kk-score--red':($v<=50?'kk-score--orange':($v<=75?'kk-score--yellow':'kk-score--green'))
                    );
                @endphp
                <div class="kk-scores-3">
                    <span class="kk-score-label">C</span>
                    <span class="kk-score {{ $class($c) }}">{{ $c ?? '—' }}</span>
                    <span class="kk-score-label">S</span>
                    <span class="kk-score {{ $class($s) }}">{{ $s ?? '—' }}</span>
                    <span class="kk-score-label">T</span>
                    <span class="kk-score {{ $class($t) }}">{{ $t ?? '—' }}</span>
                </div>
            @endinteract

            @interact('column_team', $row)
                @php
                    $isAdminLocal = auth()->user()->is_admin ?? false;
                    $current = $row->team_id;
                    $teams = $isAdminLocal
                        ? \App\Models\Team::select('id','name')->orderBy('name')->get()
                        : collect();
                @endphp

                @if($isAdminLocal)
                    <div x-data @click.stop>
                        <select
                            class="border px-2 py-1 w-full"
                            wire:change="updateTeam({{ $row->id }}, $event.target.value)"
                            @click.stop
                        >
                            @foreach($teams as $t)
                                <option value="{{ $t->id }}" @selected($t->id == $current)>
                                    {{ $t->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    {{ $row->team->name ?? '—' }}
                @endif
            @endinteract

            @interact('column_flag', $row)
                @php
                    $flags = $row->latestEvaluation?->red_flags ?? [];
                    $flags = is_object($flags) ? get_object_vars($flags) : $flags;
                @endphp

                @if(count($flags))
                    <span class="kk-flag">
                        {{ \Illuminate\Support\Str::headline($flags[0]) }}
                        @if(count($flags)>1)
                            +{{ count($flags)-1 }}
                        @endif
                    </span>
                @else
                    <span class="kk-flag kk-flag-none">No red flags</span>
                @endif
            @endinteract

            @interact('column_action', $row)
                <div class="flex items-center gap-2">
                    <a class="kk-btn" href="{{ route('dogs.show', $row) }}">View</a>
                    <a class="kk-btn kk-btn--primary" href="{{ route('dogs.edit', $row) }}">Edit</a>
                </div>
            @endinteract
        </x-ts-table>
    </div>

    {{-- FE FILTER SCRIPT --}}
    <script>
        (function () {
            function applyFilters() {
                const search = document.getElementById('dog-search').value.toLowerCase();
                const sex = document.getElementById('dog-sex').value;
                const flags = document.getElementById('dog-flags').value;

                const rows = document.querySelectorAll('.kk-table table tbody tr');

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    const sexText = row.children[2]?.textContent.trim().toLowerCase() || '';
                    const flagText = row.children[5]?.textContent.trim().toLowerCase() || '';

                    let ok = true;

                    if (search && !text.includes(search)) ok = false;

                    if (sex === 'male' && sexText !== 'male') ok = false;
                    if (sex === 'female' && sexText !== 'female') ok = false;

                    if (flags === 'with' && flagText.includes('no red flags')) ok = false;
                    if (flags === 'none' && !flagText.includes('no red flags')) ok = false;

                    row.style.display = ok ? '' : 'none';
                });
            }

            function init() {
                document.getElementById('dog-search').addEventListener('input', applyFilters);
                document.getElementById('dog-sex').addEventListener('change', applyFilters);
                document.getElementById('dog-flags').addEventListener('change', applyFilters);

                document.getElementById('dog-reset-filters').addEventListener('click', () => {
                    document.getElementById('dog-search').value = '';
                    document.getElementById('dog-sex').value = '';
                    document.getElementById('dog-flags').value = '';
                    applyFilters();
                });

                applyFilters();
            }

            document.addEventListener('DOMContentLoaded', init);
            if (window.Livewire) Livewire.hook('message.processed', init);
        })();
    </script>
</div>
