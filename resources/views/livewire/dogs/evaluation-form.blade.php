<form wire:submit.prevent="submit" class="space-y-10"
      x-data="{ s: @entangle('step') }"
      x-init="
        $watch('s', () => {
          window.scrollTo({ top: 0, behavior: 'smooth' });
          const main = document.querySelector('main'); if (main) main.scrollTo({ top: 0, behavior: 'smooth' });
          const app  = document.getElementById('app'); if (app)  app.scrollTo({ top: 0, behavior: 'smooth' });
          requestAnimationFrame(() => { const h = document.getElementById('step-heading'); if (h) h.focus({ preventScroll: true }); });
        });
      "
>
  @php
    $circ = 2 * M_PI * 44; // r=44
    $offset = $circ - ($this->progressPercent / 100) * $circ;
    $ring = $this->progressPercent < 50 ? '#DC2626' : ($this->progressPercent < 75 ? '#F59E0B' : '#076BA8');
  @endphp

  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
    <div class="flex items-center gap-5">
      <div class="relative">
        <svg class="w-24 h-24" aria-hidden="true">
          <circle cx="48" cy="48" r="44" fill="none" stroke="#E2E8F0" stroke-width="8"/>
          <circle cx="48" cy="48" r="44" fill="none"
                  stroke="{{ $ring }}" stroke-width="8"
                  stroke-dasharray="{{ $circ }}"
                  stroke-dashoffset="{{ $offset }}"
                  stroke-linecap="round" transform="rotate(-90 48 48)"/>
        </svg>
        <span class="absolute inset-0 flex items-center justify-center font-bold text-xl" style="color: {{ $ring }}">{{ $this->progressPercent }}%</span>
      </div>
      <div>
        <p class="text-sm opacity-80">Progress (answered {{ $this->answeredQuestions }} / {{ $this->totalQuestions }})</p>
        @if(!$this->useDbQuestions)
          <p class="text-lg font-semibold">
            Score: {{ $this->liveScore }} <span class="opacity-60">/ {{ $this->maxScore }}</span>
          </p>
        @endif
      </div>
    </div>

    {{-- Stepper --}}
    <div class="flex items-center gap-3">
      @foreach($this->categoryKeys as $idx => $catKey)
        @php $n = $idx + 1; @endphp
        <button type="button"
                wire:click="goToStep({{ $n }})"
                wire:key="stepper-{{ $n }}"
                class="px-3 py-2 rounded-lg border {{ $this->step === $n ? 'bg-[#DAEEFF] border-[#076BA8] text-[#076BA8]' : 'bg-white border-gray-200 text-gray-700' }}">
          <span class="font-semibold">{{ $n }}</span>
          <span class="ml-2">{{ $catKey }}</span>
        </button>
      @endforeach
    </div>
  </div>

  {{-- ===== Styles ===== --}}
  <style>
    .kk-sec{ background:#DAEEFF33; border:1px solid var(--kk-divider); border-radius:1rem; }
    .kk-sec h3{ color:#076BA8; font-weight:700; }
    .kk-opt{
      display:flex; gap:.6rem; align-items:flex-start; padding:.625rem .75rem;
      border:1px solid var(--kk-divider); border-radius:.75rem; background:#fff;
      transition:box-shadow .15s ease, transform .05s ease;
    }
    .kk-opt:hover{ box-shadow:0 6px 18px rgba(7,107,168,.10) }
    .kk-chip{
      display:inline-flex; align-items:center; justify-content:center;
      width:1.6rem; height:1.6rem; border-radius:.5rem;
      font-size:.75rem; font-weight:700; color:#076BA8; background:#DAEEFF;
      flex:0 0 auto;
    }
    .kk-na{ color:#374151; opacity:.8 }
    .kk-help{ font-size:.75rem; opacity:.7 }
    .kk-sticky{
      position:sticky; bottom:1rem; z-index:30;
      background:rgba(255,255,255,.96);
      border:1px solid #E2E8F0;
      border-radius:1rem; padding:.75rem 1rem;
      box-shadow:0 10px 30px rgba(3,49,76,.12);
    }
    .kk-followup{ border-left:2px solid #BFDBFE; padding-left:.75rem; margin-left:.125rem; }
    .kk-badge{ display:inline-flex; align-items:center; gap:.35rem; font-size:.7rem; padding:.15rem .4rem; border-radius:.375rem; border:1px solid #e5e7eb; background:#F9FAFB; color:#374151; }
    .kk-badge-blue{ border-color:#BFDBFE; background:#EFF6FF; color:#1D4ED8; }
    .kk-req{ color:#DC2626; }
  </style>

  {{-- ===== Current step/category only ===== --}}
  <section class="kk-sec p-5 sm:p-6" wire:key="section-{{ $this->currentCategoryKey }}-{{ $this->step }}">
    <h3 id="step-heading" class="text-lg mb-4" tabindex="-1">
      Step {{ $this->step }} of {{ $this->stepsCount }} — {{ $this->currentCategoryKey }}
    </h3>

    <div class="space-y-5">
      {{-- DB MODE --}}
      @if($this->useDbQuestions)
        {{-- IMPORTANT: $this->currentQuestions is already filtered for visibility by the component --}}
        @foreach($this->currentQuestions as $q)
          @php
            $qid     = (int) ($q['id'] ?? 0);
            $type    = $q['type'] ?? null;
            $opts    = $q['options'] ?? [];
            $help    = $q['help_text'] ?? null;
            $req     = !empty($q['required']) && (($q['visibility'] ?? 'always') === 'always');
            $isFu    = is_array($q['follow_up'] ?? null) || is_array($q['follow_up_rule'] ?? null);
            $booleanNoOpts = ($type === 'boolean') && (count($opts) === 0);
          @endphp

          <div wire:key="db-qrow-{{ $qid }}" class="{{ $isFu ? 'kk-followup' : '' }}">
            <p class="font-medium mb-1 flex items-center gap-2">
              <span>
                {{ $q['prompt'] ?? 'Question' }}
                @if($req) <span class="kk-req" title="Required">*</span> @endif
              </span>
              @if($isFu)
                <span class="kk-badge kk-badge-blue" title="This question only appears based on a previous answer">Follow-up</span>
              @endif
            </p>
            @if($help)
              <p class="kk-help mb-2">{{ $help }}</p>
            @endif

            @if(in_array($type, ['single_choice','boolean'], true) && !$booleanNoOpts)
              <div class="grid sm:grid-cols-1 gap-2">
                @php $i=1; @endphp
                @foreach($opts as $opt)
                  <label class="kk-opt cursor-pointer" wire:key="db-opt-{{ $qid }}-{{ $opt['id'] }}">
                    <input type="radio"
                           wire:model.live="answers.{{ $qid }}.answer_option_id"
                           value="{{ $opt['id'] }}"
                           class="ts-radio mt-1"
                           name="answers.{{ $qid }}.answer_option_id">
                    <span class="kk-chip">{{ $i }}</span>
                    <span class="text-sm">{{ $opt['label'] }}</span>
                  </label>
                  @php $i++; @endphp
                @endforeach
              </div>

            @elseif($booleanNoOpts)
              <div class="grid sm:grid-cols-1 gap-2">
                <label class="kk-opt cursor-pointer" wire:key="db-opt-{{ $qid }}--1">
                  <input type="radio"
                         wire:model.live="answers.{{ $qid }}.answer_option_id"
                         value="-1"
                         class="ts-radio mt-1"
                         name="answers.{{ $qid }}.answer_option_id">
                  <span class="kk-chip">Y</span>
                  <span class="text-sm">Yes</span>
                </label>
                <label class="kk-opt cursor-pointer" wire:key="db-opt-{{ $qid }}--2">
                  <input type="radio"
                         wire:model.live="answers.{{ $qid }}.answer_option_id"
                         value="-2"
                         class="ts-radio mt-1"
                         name="answers.{{ $qid }}.answer_option_id">
                  <span class="kk-chip">N</span>
                  <span class="text-sm">No</span>
                </label>
              </div>

            @elseif($type === 'multi_choice')
              <div class="grid sm:grid-cols-1 gap-2">
                @foreach($opts as $opt)
                  <label class="kk-opt cursor-pointer" wire:key="db-opt-{{ $qid }}-{{ $opt['id'] }}">
                    <input type="checkbox"
                           wire:model.live="answers.{{ $qid }}.answer_option_ids"
                           value="{{ $opt['id'] }}"
                           name="answers.{{ $qid }}.answer_option_ids[]"
                           class="ts-checkbox mt-1">
                    <span class="kk-chip">•</span>
                    <span class="text-sm">{{ $opt['label'] }}</span>
                  </label>
                @endforeach
              </div>
              <p class="kk-help mt-2">Select all that apply.</p>

            @elseif($type === 'scale')
              <div class="kk-opt">
                <input type="number" step="1"
                       wire:model.lazy="answers.{{ $qid }}.answer_value"
                       class="border rounded px-2 py-1 w-24">
                <span class="text-sm kk-help ml-2">
                  {{ ($q['meta']['min'] ?? 0) }}–{{ ($q['meta']['max'] ?? 100) }}
                  @if(($q['meta']['invert'] ?? false)===true) (inverted) @endif
                </span>
              </div>

            @elseif($type === 'text')
              <textarea wire:model.lazy="answers.{{ $qid }}.answer_text"
                        class="w-full border rounded px-3 py-2"
                        rows="3"></textarea>

            @else
              {{-- fallback custom --}}
              <textarea wire:model.lazy="answers.{{ $qid }}.answer_json"
                        class="w-full border rounded px-3 py-2"
                        rows="2"
                        placeholder="JSON"></textarea>
            @endif

            @error("answers.$qid".($type==='single_choice' || $type==='boolean' ? '.answer_option_id' : ($type==='multi_choice' ? '.answer_option_ids' : ($type==='scale' ? '.answer_value' : ($type==='text' ? '.answer_text' : '.answer_json')))))
              <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
          </div>
        @endforeach

      {{-- LEGACY MODE --}}
      @else
        @foreach($this->currentQuestions as $qKey => $q)
          <div wire:key="legacy-qrow-{{ $this->currentCategoryKey }}-{{ $qKey }}">
            <p class="font-medium mb-2">{{ $q['text'] }}</p>

            @if(($q['type'] ?? null) === 'radio')
              <div class="grid sm:grid-cols-1 gap-2">
                @php $i=1; @endphp
                @foreach(($q['options'] ?? []) as $optKey => $opt)
                  <label class="kk-opt cursor-pointer" wire:key="legacy-opt-{{ $qKey }}-{{ $optKey }}-{{ $this->step }}">
                    <input type="radio"
                          wire:model.live="answers.{{ $qKey }}"
                          value="{{ $optKey }}"
                          class="ts-radio mt-1"
                          name="answers.{{ $qKey }}">
                    @if(is_int($opt['score'] ?? null))
                      <span class="kk-chip">{{ $i }}</span>
                    @endif
                    <span class="text-sm @if($optKey==='na') kk-na @endif">{{ $opt['label'] }}</span>
                  </label>
                  @php $i++; @endphp
                @endforeach
              </div>

            @elseif(($q['type'] ?? null) === 'checkbox')
              <div class="grid sm:grid-cols-1 gap-2">
                @foreach(($q['options'] ?? []) as $opt)
                  <label class="kk-opt cursor-pointer" wire:key="legacy-opt-{{ $qKey }}-{{ $opt['label'] }}-{{ $this->step }}">
                    <input type="checkbox"
                          wire:model.live="answers.{{ $qKey }}"
                          value="{{ $opt['label'] }}"
                          name="answers.{{ $qKey }}[]"
                          class="ts-checkbox mt-1">
                    <span class="kk-chip">•</span>
                    <span class="text-sm">{{ $opt['label'] }}</span>
                  </label>
                @endforeach
              </div>
              <p class="kk-help mt-2">Select all that apply.</p>
            @endif

            @error("answers.$qKey")
              <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
          </div>
        @endforeach
      @endif
    </div>
  </section>

  {{-- ===== Sticky nav / actions ===== --}}
  <div class="kk-sticky mt-2">
    <div class="flex items-center justify-between gap-3">
      <div class="text-sm">
        <span class="font-semibold">Progress: {{ $this->progressPercent }}%</span>
        <span class="opacity-70"> • Answered {{ $this->answeredQuestions }}/{{ $this->totalQuestions }}</span>
      </div>

      <div class="flex items-center gap-2">
        <x-ts-button type="button" variant="secondary" wire:click="prevStep" :disabled="$this->step === 1">
          Back
        </x-ts-button>

        @if($this->step < $this->stepsCount)
          <x-ts-button type="button" wire:click="nextStep">
            Next
          </x-ts-button>
        @else
          <x-ts-button type="submit">
            Save Evaluation
          </x-ts-button>
        @endif
      </div>
    </div>
  </div>
</form>
