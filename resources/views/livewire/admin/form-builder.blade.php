<div class="max-w-6xl mx-auto p-6 space-y-8">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">
            Form Builder — {{ $form->name }}
            <span class="text-sm font-normal text-gray-500">(v{{ $form->version }})</span>
        </h1>
        <a href="{{ route('admin.forms.index') }}" class="px-3 py-2 rounded border">Back to forms</a>
    </div>

    @if (session('success'))
        <div class="p-3 rounded bg-green-50 text-green-700">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="p-3 rounded bg-red-50 text-red-700">{{ session('error') }}</div>
    @endif

    {{-- Form meta --}}
    <div class="bg-white border rounded p-4 space-y-3">
        <h2 class="font-semibold">Form Details</h2>
        <div class="flex items-end gap-3">
            <div class="flex-1">
                <label class="text-sm">Name</label>
                <input type="text" wire:model.defer="formName" class="border rounded w-full px-3 py-2">
                @error('formName') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <button wire:click="saveFormMeta" class="px-3 py-2 rounded bg-blue-600 text-white">Save</button>
        </div>
        <p class="text-xs text-gray-500">
            Slug: <span class="font-mono">{{ $form->slug }}</span> •
            Scope: {{ $form->team_id ? 'Team '.$form->team_id : 'Global' }}
        </p>
        <p class="text-xs text-gray-500">
            Status:
            {!! $form->is_active
                ? '<span class="text-green-600 font-semibold">Published</span>'
                : '<span class="text-gray-700">Draft</span>' !!}
        </p>
    </div>

    {{-- Sections --}}
    <div class="bg-white border rounded p-4 space-y-3">
        <h2 class="font-semibold">Sections</h2>

        <div class="flex flex-wrap gap-2">
            <div class="w-80">
                <label class="text-sm">New section title</label>
                <input type="text" wire:model="newSectionTitle" class="border rounded px-3 py-2 w-full" placeholder="e.g., Comfort & Confidence">
                @error('newSectionTitle') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <div class="w-64">
                <label class="text-sm">Slug (optional)</label>
                <input type="text" wire:model="newSectionSlug" class="border rounded px-3 py-2 w-full" placeholder="comfort-confidence">
            </div>
            <div class="self-end">
                <button wire:click="addSection" class="px-3 py-2 rounded bg-blue-600 text-white">Add Section</button>
            </div>
        </div>

        <div class="space-y-4 mt-4">
            @foreach($form->sections as $sec)
                @php
                    $catKey = \App\Livewire\Admin\FormBuilder::inferCategoryStatic($sec->slug ?? \Illuminate\Support\Str::slug($sec->title));
                    $catLabel = [
                        'comfort_confidence' => 'Comfort & Confidence',
                        'sociability'        => 'Sociability',
                        'trainability'       => 'Trainability',
                        'general'            => 'General',
                    ][$catKey] ?? 'General';
                @endphp

                <div class="border rounded p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-gray-100">{{ $sec->position }}</span>

                            @if($editingSectionId === $sec->id)
                                <input type="text" wire:model.defer="editingSectionTitle" class="border rounded px-2 py-1">
                                <button wire:click="saveSection" class="px-2 py-1 rounded bg-blue-600 text-white">Save</button>
                            @else
                                <h3 class="font-semibold">{{ $sec->title }}</h3>
                                <span class="text-xs px-2 py-0.5 border rounded bg-gray-50 text-gray-700">
                                    Category: {{ $catLabel }}
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <button wire:click="sectionUp({{ $sec->id }})" class="px-2 py-1 rounded border">Up</button>
                            <button wire:click="sectionDown({{ $sec->id }})" class="px-2 py-1 rounded border">Down</button>
                            <button wire:click="editSection({{ $sec->id }})" class="px-2 py-1 rounded border">Rename</button>
                            <button wire:click="deleteSection({{ $sec->id }})" class="px-2 py-1 rounded border text-red-600">Delete</button>
                        </div>
                    </div>

                    {{-- Attach or Create --}}
                    <div class="mt-4 flex flex-wrap items-end gap-3">
                        <div>
                            <label class="text-sm">Attach existing question</label>
                            <select wire:model="existingQuestionId" class="border rounded px-2 py-1 w-96">
                                <option value="">— choose from bank —</option>
                                @foreach($bank as $q)
                                    <option value="{{ $q->id }}">{{ $q->slug }} — {{ \Illuminate\Support\Str::limit($q->prompt, 60) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button
                            class="px-3 py-2 rounded border"
                            wire:click="attachExistingQuestion({{ $sec->id }})"
                            wire:loading.attr="disabled"
                            @disabled(!$existingQuestionId)
                        >
                            Attach to “{{ $sec->title }}”
                        </button>

                        <button class="px-3 py-2 rounded bg-gray-900 text-white"
                                wire:click="openCreateQuestion({{ $sec->id }})">
                            Create new question
                        </button>
                    </div>

                    {{-- Questions table --}}
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="text-left p-2">#</th>
                                    <th class="text-left p-2">Question</th>
                                    <th class="text-left p-2">Type</th>
                                    <th class="text-left p-2">Category</th>
                                    <th class="text-left p-2">Required</th>
                                    <th class="text-left p-2">Visibility</th>
                                    <th class="text-left p-2">Options / Scoring</th>
                                    <th class="text-left p-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($form->formQuestions->where('section_id',$sec->id) as $fq)
                                    @php
                                        $q = $fq->question;
                                        $cat = $q->category;
                                        $catLabel = [
                                            'comfort_confidence' => 'Comfort & Confidence',
                                            'sociability'        => 'Sociability',
                                            'trainability'       => 'Trainability',
                                            'general'            => 'General',
                                        ][$cat] ?? 'General';
                                    @endphp
                                    <tr>
                                        <td class="p-2">{{ $fq->position }}</td>
                                        <td class="p-2">
                                            <div class="font-medium">{{ $q->prompt }}</div>
                                            <div class="text-xs text-gray-500">slug: {{ $q->slug }}</div>
                                            @if($q->type==='scale')
                                                <div class="text-xs text-gray-500 mt-1">
                                                    Scale: min {{ $q->meta['min'] ?? 0 }},
                                                    max {{ $q->meta['max'] ?? 10 }},
                                                    invert: {{ ($q->meta['invert'] ?? false) ? 'Yes' : 'No' }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="p-2">{{ $q->type }}</td>
                                        <td class="p-2">{{ $catLabel }}</td>
                                        <td class="p-2">
                                            <button wire:click="toggleRequired({{ $fq->id }})" class="px-2 py-1 rounded border">
                                                {{ $fq->required ? 'Yes' : 'No' }}
                                            </button>
                                        </td>
                                        <td class="p-2">
                                            <label class="sr-only">Visibility</label>
                                            <select class="border rounded px-2 py-1"
                                                    wire:change="setVisibility({{ $fq->id }}, $event.target.value)">
                                                <option value="always" {{ $fq->visibility==='always'?'selected':'' }}>Always</option>
                                                <option value="staff_only" {{ $fq->visibility==='staff_only'?'selected':'' }}>Staff only</option>
                                                <option value="public_summary" {{ $fq->visibility==='public_summary'?'selected':'' }}>Public summary</option>
                                            </select>
                                        </td>
                                        <td class="p-2">
                                            @if(in_array($q->type, ['single_choice','multi_choice','boolean']))
                                                <div class="space-y-2">
                                                    @foreach($q->answerOptions as $opt)
                                                        <div class="border rounded p-2 space-y-2">
                                                            <div class="grid grid-cols-12 gap-2">
                                                                <div class="col-span-5">
                                                                    <label class="text-xs text-gray-600">Label</label>
                                                                    <input type="text"
                                                                           value="{{ $opt->label }}"
                                                                           class="border rounded px-2 py-1 w-full"
                                                                           wire:change="updateOptionField({{ $opt->id }}, 'label', $event.target.value)">
                                                                </div>
                                                                <div class="col-span-3">
                                                                    <label class="text-xs text-gray-600">Internal value (optional)</label>
                                                                    <input type="text"
                                                                           value="{{ $opt->value }}"
                                                                           class="border rounded px-2 py-1 w-full"
                                                                           wire:change="updateOptionField({{ $opt->id }}, 'value', $event.target.value)">
                                                                </div>

                                                                {{-- Red Flags (JSON, legacy for badges) --}}
                                                                <div class="col-span-4">
                                                                    <label class="text-xs text-gray-600 block">Flags (hold Ctrl/Cmd to multi-select)</label>
                                                                    <select multiple
                                                                            class="border rounded px-2 py-1 w-full"
                                                                            wire:change="replaceOptionFlags({{ $opt->id }}, Array.from($event.target.selectedOptions).map(o=>o.value))">
                                                                        @php
                                                                            $optFlags = $opt->flags ?? [];
                                                                        @endphp
                                                                        @foreach($availableFlags as $flag)
                                                                            <option value="{{ $flag }}"
                                                                                {{ in_array($flag, $optFlags ?? []) ? 'selected' : '' }}>
                                                                                {{ $flag }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            {{-- Training Flags (DB-based) --}}
                                                            <div class="grid grid-cols-12 gap-2 mt-2">
                                                                <div class="col-span-12 md:col-span-6">
                                                                    <label class="text-xs text-gray-600 block">Training Flags (hold Ctrl/Cmd to multi-select)</label>
                                                                    <select multiple
                                                                            class="border rounded px-2 py-1 w-full"
                                                                            wire:change="replaceOptionTrainingFlags({{ $opt->id }}, Array.from($event.target.selectedOptions).map(o=>parseInt(o.value)))">
                                                                        @foreach($trainingFlags as $tf)
                                                                            <option value="{{ $tf->id }}"
                                                                                {{ $opt->trainingFlags->contains('id', $tf->id) ? 'selected' : '' }}>
                                                                                {{ $tf->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            {{-- Single score (derived from question category) --}}
                                                            @php
                                                                $scoreMap = $opt->score_map ?? [];
                                                                $current = (int) ($scoreMap[$cat] ?? 0);
                                                            @endphp
                                                            @if($cat !== 'general')
                                                                <div class="grid grid-cols-12 gap-2 mt-2">
                                                                    <div class="col-span-4">
                                                                        <label class="text-xs text-gray-600">Score ({{ $catLabel }})</label>
                                                                        <input type="number" step="1"
                                                                               value="{{ $current }}"
                                                                               class="border rounded px-2 py-1 w-full"
                                                                               wire:change="updateOptionScore({{ $opt->id }}, '{{ $cat }}', $event.target.value)">
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <span class="text-xs text-gray-500">Not scored in summary (General category).</span>
                                                            @endif

                                                            <div class="flex items-center justify-between">
                                                                <button wire:click="deleteOption({{ $opt->id }})"
                                                                        class="px-2 py-1 rounded border text-red-600">
                                                                    Delete option
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endforeach

                                                    <button wire:click="addOption({{ $q->id }})"
                                                            class="px-2 py-1 rounded border">Add option</button>
                                                </div>
                                            @elseif($q->type==='scale')
                                                <span class="text-xs text-gray-500">Scale question — configured in question settings.</span>
                                            @else
                                                <span class="text-xs text-gray-500">—</span>
                                            @endif
                                        </td>
                                        <td class="p-2">
                                            <div class="flex gap-2">
                                                <button wire:click="qUp({{ $fq->id }})" class="px-2 py-1 rounded border">Up</button>
                                                <button wire:click="qDown({{ $fq->id }})" class="px-2 py-1 rounded border">Down</button>
                                                <button wire:click="detachQuestion({{ $fq->id }})" class="px-2 py-1 rounded border text-red-600">Detach</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                @if($form->formQuestions->where('section_id',$sec->id)->isEmpty())
                                    <tr>
                                        <td colspan="8" class="p-3 text-sm text-gray-500">No questions yet.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            @if($form->sections->isEmpty())
                <div class="text-sm text-gray-500">No sections. Add one above.</div>
            @endif
        </div>
    </div>

    {{-- Create Question Modal --}}
    @if($createQuestionModal)
        <div class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-[960px] shadow-xl">
                <h2 class="text-lg font-semibold mb-3">Create Question</h2>

                {{-- Category is derived from section; show a hint only --}}
                <div class="mb-3 text-sm">
                    <span class="px-2 py-1 border rounded bg-gray-50">
                        Category: <strong>{{ $derivedCategoryLabel }}</strong> (auto from section)
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm">Type</label>
                        <select wire:model="qType" class="border rounded w-full px-2 py-1">
                            <option value="single_choice">Single choice (radio)</option>
                            <option value="multi_choice">Multiple choice (checkboxes)</option>
                            <option value="scale">Scale (numeric)</option>
                            <option value="boolean">Yes / No</option>
                            <option value="text">Free text</option>
                        </select>
                    </div>

                    <div class="col-span-2">
                        <label class="text-sm">Prompt</label>
                        <textarea wire:model="qPrompt" rows="2" class="border rounded w-full px-2 py-1"></textarea>
                        @error('qPrompt') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm">Help (optional)</label>
                        <input type="text" wire:model="qHelp" class="border rounded w-full px-2 py-1">
                    </div>
                </div>

                {{-- Scale configuration --}}
                @if($qType==='scale')
                    <div class="mt-4 grid grid-cols-3 gap-3">
                        <div>
                            <label class="text-sm">Minimum value</label>
                            <input type="number" wire:model="qScaleMin" class="border rounded w-full px-2 py-1">
                        </div>
                        <div>
                            <label class="text-sm">Maximum value</label>
                            <input type="number" wire:model="qScaleMax" class="border rounded w-full px-2 py-1">
                        </div>
                        <div class="flex items-end gap-2">
                            <input id="invert" type="checkbox" wire:model="qScaleInvert" class="border rounded">
                            <label for="invert" class="text-sm">Invert score (higher = worse)</label>
                        </div>
                    </div>
                @endif

                {{-- Options (discrete types) --}}
                @if(in_array($qType,['single_choice','multi_choice','boolean']))
                    <div class="mt-4">
                        <h3 class="font-semibold mb-2">Options, training & scoring</h3>

                        <div class="space-y-3">
                            @foreach($optionRows as $idx => $row)
                                <div class="border rounded p-3">
                                    <div class="grid grid-cols-12 gap-2">
                                        <div class="col-span-5">
                                            <label class="text-xs text-gray-600">Label</label>
                                            <input type="text" class="border rounded px-2 py-1 w-full"
                                                   wire:model="optionRows.{{ $idx }}.label" placeholder="e.g., Calm">
                                        </div>
                                        <div class="col-span-3">
                                            <label class="text-xs text-gray-600">Internal value (optional)</label>
                                            <input type="text" class="border rounded px-2 py-1 w-full"
                                                   wire:model="optionRows.{{ $idx }}.value" placeholder="e.g., calm">
                                        </div>

                                        {{-- Red Flags (legacy) --}}
                                        <div class="col-span-4">
                                            <label class="text-xs text-gray-600">Flags</label>
                                            <select multiple class="border rounded px-2 py-1 w-full"
                                                    wire:model="optionRows.{{ $idx }}.flags">
                                                @foreach($availableFlags as $flag)
                                                    <option value="{{ $flag }}">{{ $flag }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-12 gap-2 mt-2">
                                        {{-- Training Flags --}}
                                        <div class="col-span-12 md:col-span-6">
                                            <label class="text-xs text-gray-600">Training Flags</label>
                                            <select multiple class="border rounded px-2 py-1 w-full"
                                                    wire:model="optionRows.{{ $idx }}.training_flag_ids">
                                                @foreach($trainingFlags as $tf)
                                                    <option value="{{ $tf->id }}">{{ $tf->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Score for derived category --}}
                                        <div class="col-span-12 md:col-span-6">
                                            <label class="text-xs text-gray-600">Score ({{ $derivedCategoryLabel }})</label>
                                            <input type="number" step="1" class="border rounded px-2 py-1 w-full"
                                                   wire:model.lazy="optionRows.{{ $idx }}.scores.{{ $derivedCategoryKey }}">
                                        </div>
                                    </div>

                                    <div class="mt-2">
                                        <button class="px-2 py-1 rounded border text-red-600"
                                                wire:click="removeOptionRow({{ $idx }})">
                                            Remove option
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button class="mt-2 px-2 py-1 rounded border" wire:click="addOptionRow">Add option</button>
                    </div>
                @endif

                <div class="mt-6 flex justify-end gap-2">
                    <button class="px-3 py-2 rounded border" wire:click="$set('createQuestionModal', false)">Cancel</button>
                    <button class="px-3 py-2 rounded bg-blue-600 text-white" wire:click="saveNewQuestion">Create</button>
                </div>
            </div>
        </div>
    @endif
</div>
