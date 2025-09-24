<div class="max-w-6xl mx-auto p-6 space-y-8">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Form Builder — {{ $form->name }} <span class="text-sm font-normal text-gray-500">(v{{ $form->version }})</span></h1>
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
        <p class="text-xs text-gray-500">Slug: <span class="font-mono">{{ $form->slug }}</span> • Scope: {{ $form->team_id ? 'Team '.$form->team_id : 'Global' }}</p>
        <p class="text-xs text-gray-500">Status: {!! $form->is_active ? '<span class="text-green-600 font-semibold">Published</span>' : '<span class="text-gray-700">Draft</span>' !!}</p>
    </div>

    {{-- Add section --}}
    <div class="bg-white border rounded p-4 space-y-3">
        <h2 class="font-semibold">Sections</h2>

        <div class="flex gap-2">
            <input type="text" wire:model="newSectionTitle" placeholder="New section title" class="border rounded px-3 py-2 w-80">
            <input type="text" wire:model="newSectionSlug" placeholder="slug (optional)" class="border rounded px-3 py-2 w-64">
            <button wire:click="addSection" class="px-3 py-2 rounded bg-blue-600 text-white">Add Section</button>
        </div>
        @error('newSectionTitle') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror

        <div class="space-y-4 mt-4">
            @foreach($form->sections as $sec)
                <div class="border rounded p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-gray-100">{{ $sec->position }}</span>
                            @if($editingSectionId === $sec->id)
                                <input type="text" wire:model.defer="editingSectionTitle" class="border rounded px-2 py-1">
                                <button wire:click="saveSection" class="px-2 py-1 rounded bg-blue-600 text-white">Save</button>
                            @else
                                <h3 class="font-semibold">{{ $sec->title }}</h3>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <button wire:click="sectionUp({{ $sec->id }})" class="px-2 py-1 rounded border">Up</button>
                            <button wire:click="sectionDown({{ $sec->id }})" class="px-2 py-1 rounded border">Down</button>
                            <button wire:click="editSection({{ $sec->id }})" class="px-2 py-1 rounded border">Rename</button>
                            <button wire:click="deleteSection({{ $sec->id }})" class="px-2 py-1 rounded border text-red-600">Delete</button>
                        </div>
                    </div>

                    {{-- Attach existing question --}}
                    <div class="mt-4 flex flex-wrap items-end gap-2">
                        <div>
                            <label class="text-sm">Attach existing question</label>
                            <select wire:model="existingQuestionId" class="border rounded px-2 py-1 w-80">
                                <option value="">— choose from bank —</option>
                                @foreach($bank as $q)
                                    <option value="{{ $q->id }}">{{ $q->slug }} — {{ \Illuminate\Support\Str::limit($q->prompt, 60) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="px-3 py-2 rounded border" wire:click="attachExistingQuestion" wire:loading.attr="disabled" @disabled(!$existingQuestionId) wire:key="attach-{{ $sec->id }}">Attach</button>

                        <button class="px-3 py-2 rounded bg-gray-900 text-white" wire:click="openCreateQuestion({{ $sec->id }})">Create new question</button>
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
                                    <th class="text-left p-2">Options</th>
                                    <th class="text-left p-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($form->formQuestions->where('section_id',$sec->id) as $fq)
                                    <tr>
                                        <td class="p-2">{{ $fq->position }}</td>
                                        <td class="p-2">
                                            <div class="font-medium">{{ $fq->question->prompt }}</div>
                                            <div class="text-xs text-gray-500">slug: {{ $fq->question->slug }}</div>
                                        </td>
                                        <td class="p-2">{{ $fq->question->type }}</td>
                                        <td class="p-2">{{ $fq->question->category }}</td>
                                        <td class="p-2">
                                            <button wire:click="toggleRequired({{ $fq->id }})" class="px-2 py-1 rounded border">
                                                {{ $fq->required ? 'Yes' : 'No' }}
                                            </button>
                                        </td>
                                        <td class="p-2">
                                            <select class="border rounded px-2 py-1" wire:change="setVisibility({{ $fq->id }}, $event.target.value)">
                                                <option value="always" {{ $fq->visibility==='always'?'selected':'' }}>always</option>
                                                <option value="staff_only" {{ $fq->visibility==='staff_only'?'selected':'' }}>staff_only</option>
                                                <option value="public_summary" {{ $fq->visibility==='public_summary'?'selected':'' }}>public_summary</option>
                                            </select>
                                        </td>
                                        <td class="p-2">
                                            @if(in_array($fq->question->type, ['single_choice','multi_choice','boolean']))
                                                <div class="space-y-2">
                                                    @foreach($fq->question->answerOptions as $opt)
                                                        <div class="border rounded p-2">
                                                            <div class="flex items-center gap-2">
                                                                <input type="text" value="{{ $opt->label }}" class="border rounded px-2 py-1"
                                                                    wire:change="saveOption({{ $opt->id }}, 'label', $event.target.value)">
                                                                <input type="text" value="{{ $opt->value }}" placeholder="value" class="border rounded px-2 py-1 w-36"
                                                                    wire:change="saveOption({{ $opt->id }}, 'value', $event.target.value)">
                                                                <input type="text" value='@json($opt->score_map)' class="border rounded px-2 py-1 w-64 font-mono text-xs"
                                                                    wire:change="saveOption({{ $opt->id }}, 'score_map', $event.target.value)">
                                                                <input type="text" value='@json($opt->flags)' class="border rounded px-2 py-1 w-64 font-mono text-xs"
                                                                    wire:change="saveOption({{ $opt->id }}, 'flags', $event.target.value)">
                                                                <button wire:click="deleteOption({{ $opt->id }})" class="px-2 py-1 rounded border text-red-600">Delete</button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                    <button wire:click="addOption({{ $fq->question->id }})" class="px-2 py-1 rounded border">Add option</button>
                                                </div>
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
                                    <tr><td colspan="8" class="p-3 text-sm text-gray-500">No questions yet.</td></tr>
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
            <div class="bg-white rounded-xl p-6 w-[900px] shadow-xl">
                <h2 class="text-lg font-semibold mb-3">Create Question</h2>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm">Slug</label>
                        <input type="text" wire:model="qSlug" class="border rounded w-full px-2 py-1">
                        @error('qSlug') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm">Type</label>
                        <select wire:model="qType" class="border rounded w-full px-2 py-1">
                            <option value="single_choice">single_choice</option>
                            <option value="multi_choice">multi_choice</option>
                            <option value="scale">scale</option>
                            <option value="boolean">boolean</option>
                            <option value="text">text</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="text-sm">Prompt</label>
                        <textarea wire:model="qPrompt" rows="2" class="border rounded w-full px-2 py-1"></textarea>
                        @error('qPrompt') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm">Category</label>
                        <select wire:model="qCategory" class="border rounded w-full px-2 py-1">
                            <option value="comfort_confidence">comfort_confidence</option>
                            <option value="sociability">sociability</option>
                            <option value="trainability">trainability</option>
                            <option value="general">general</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm">Help (optional)</label>
                        <input type="text" wire:model="qHelp" class="border rounded w-full px-2 py-1">
                    </div>
                    <div class="col-span-2">
                        <label class="text-sm">Meta (JSON)</label>
                        <input type="text" wire:model="qMeta" class="border rounded w-full px-2 py-1 font-mono text-xs" placeholder='{"min":0,"max":10,"invert":false}'>
                    </div>
                </div>

                {{-- Option rows for discrete types --}}
                @if(in_array($qType,['single_choice','multi_choice','boolean']))
                    <div class="mt-4">
                        <h3 class="font-semibold mb-2">Options</h3>
                        <div class="space-y-2">
                            @foreach($optionRows as $idx => $row)
                                <div class="flex flex-wrap gap-2 items-center">
                                    <input type="text" wire:model="optionRows.{{ $idx }}.label" class="border rounded px-2 py-1 w-56" placeholder="Label">
                                    <input type="text" wire:model="optionRows.{{ $idx }}.value" class="border rounded px-2 py-1 w-36" placeholder="value (optional)">
                                    <input type="text" wire:model="optionRows.{{ $idx }}.score_map" class="border rounded px-2 py-1 w-72 font-mono text-xs" placeholder='{"comfort_confidence":50}'>
                                    <input type="text" wire:model="optionRows.{{ $idx }}.flags" class="border rounded px-2 py-1 w-72 font-mono text-xs" placeholder='["Muzzle Conditioning"]'>
                                    <button class="px-2 py-1 rounded border text-red-600" wire:click="removeOptionRow({{ $idx }})">Remove</button>
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
