<div>
    @if($open)
        {{-- Overlay --}}
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/40" wire:click="close"></div>

            {{-- Modal --}}
            <div class="relative z-10 w-full max-w-3xl bg-white border shadow-xl hard-corners"
                 style="border-color:#E2E8F0;">
                <div class="px-5 py-3 border-b" style="background:#DAEEFF; border-color:#E2E8F0;">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold">
                            Evaluation Details
                            @if($header['date'])
                                <span class="ml-2 text-sm font-normal text-gray-600">({{ $header['date'] }})</span>
                            @endif
                        </h3>

                        <button type="button"
                                class="px-2 py-1 text-sm border"
                                style="color:#03314C; border-color:#E2E8F0;"
                                wire:click="close">
                            Close
                        </button>
                    </div>
                </div>

                <div class="p-5 space-y-6 max-h-[70vh] overflow-y-auto">
                    {{-- Score summary --}}
                    <div class="border" style="border-color:#E2E8F0;">
                        <div class="px-4 py-2 border-b font-semibold" style="background:#F8FAFC; border-color:#E2E8F0;">
                            Score Summary
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3">
                            @foreach($header['scores'] as $label => $val)
                                @php
                                    $color = '#94A3B8';
                                    if(is_numeric($val)){
                                        $v = (int)$val;
                                        $color = $v <= 25 ? '#DC2626' : ($v <= 50 ? '#F97316' : ($v <= 75 ? '#FFCC00' : '#16A34A'));
                                    }
                                    $width = is_numeric($val) ? max(0, min(100, (int)$val)) : 0;
                                    $text = is_numeric($val) ? ($val.' / 100') : '—';
                                @endphp
                                <div class="p-4 border-t sm:border-l sm:first:border-l-0" style="border-color:#E2E8F0;">
                                    <p class="text-sm font-semibold" style="color:#03314C">{{ $label }}</p>
                                    <div class="h-3 w-full overflow-hidden mt-2 border" style="background:#E2E8F0; border-color:#E2E8F0;">
                                        <div class="h-full" style="background: {{ $color }}; width: {{ $width }}%"></div>
                                    </div>
                                    <p class="text-right mt-1 text-sm font-medium" style="color:{{ $color }}">{{ $text }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Q/A list --}}
                    <div class="border" style="border-color:#E2E8F0;">
                        <div class="px-4 py-2 border-b font-semibold" style="background:#F8FAFC; border-color:#E2E8F0;">
                            Questions & Answers
                        </div>

                        @if(empty($qa))
                            <div class="p-4 text-sm text-gray-600">No answers recorded for this evaluation.</div>
                        @else
                            <ul class="divide-y" style="border-color:#E2E8F0;">
                                @foreach($qa as $row)
                                    <li class="p-4 space-y-1">
                                        <div class="text-sm font-semibold" style="color:#03314C">
                                            {{ $row['question'] }}
                                        </div>
                                        @if($row['answer'])
                                            <div class="text-sm">{{ $row['answer'] }}</div>
                                        @endif
                                        @if($row['notes'])
                                            <div class="text-xs text-gray-600">
                                                Notes: {{ $row['notes'] }}
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                <div class="px-5 py-3 border-t flex justify-end gap-2" style="border-color:#E2E8F0;">
                    <button type="button"
                            class="px-3 py-1.5 text-sm border"
                            style="color:#03314C; border-color:#E2E8F0;"
                            wire:click="close">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
