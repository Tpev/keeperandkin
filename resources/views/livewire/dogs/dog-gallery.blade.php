{{-- resources/views/livewire/dogs/dog-gallery.blade.php --}}

<section class="mt-8 border rounded-lg overflow-hidden" style="border-color:#E2E8F0;">
    {{-- Header --}}
    <div class="px-4 py-3 flex items-center justify-between" style="background:#DAEEFF;">
        <h2 class="text-lg font-bold text-[#03314C]">Gallery</h2>

        @if (session()->has('gallery_success'))
            <span class="text-xs text-green-700">
                {{ session('gallery_success') }}
            </span>
        @endif
    </div>

    <div class="p-4 bg-white space-y-6">

        {{-- GRID --}}
        @if($mediaItems->count())
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @foreach($mediaItems as $item)
                    <div class="relative group border rounded-md overflow-hidden bg-gray-50"
                         wire:key="media-{{ $item->id }}">

                        {{-- IMAGE --}}
                        @if($item->media_type === 'image')
                            <img src="{{ asset('storage/'.$item->file_path) }}"
                                 alt="Dog media"
                                 class="w-full h-32 object-cover group-hover:opacity-90 transition">
                        @endif

                        {{-- VIDEO --}}
                        @if($item->media_type === 'video')
                            <video class="w-full h-32 object-cover" controls>
                                <source src="{{ asset('storage/'.$item->file_path) }}">
                                Your browser does not support the video tag.
                            </video>
                        @endif

                        {{-- CAPTION --}}
                        @if($item->caption)
                            <div class="px-2 py-1 text-xs text-gray-700 truncate">
                                {{ $item->caption }}
                            </div>
                        @endif

                        {{-- DELETE --}}
                        <button type="button"
                                class="absolute top-1 right-1 inline-flex items-center justify-center w-7 h-7 rounded-full bg-white/90 text-red-600 text-xs shadow hover:bg-red-50"
                                wire:click="deleteMedia({{ $item->id }})"
                                onclick="return confirm('Remove this media?')">
                            ✕
                        </button>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500">No media yet. Add photos or videos below.</p>
        @endif

        <hr class="border-t border-gray-200">

        {{-- ADD FORM --}}
        <div>
            <h3 class="text-sm font-semibold text-[#03314C] mb-2">Add media to gallery</h3>

            <form wire:submit.prevent="addMedia" class="space-y-4">

                {{-- TYPE --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Media type</label>
                    <select wire:model="media_type" class="form-select block w-full text-sm">
                        <option value="image">Image (file upload)</option>
                        <option value="video">Video (file upload)</option>
                    </select>
                    @error('media_type') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- FILE --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">File</label>
                    <input type="file" wire:model="file" class="block w-full text-xs">
                    <p class="mt-1 text-xs text-gray-400">
                        Allowed: JPG, PNG, WEBP, MP4, MOV (max 50 MB)
                    </p>
                    @error('file') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- CAPTION --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Caption (optional)</label>
                    <input type="text" wire:model="caption" maxlength="255" class="form-input block w-full text-sm">
                    @error('caption') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit"
                        class="inline-flex items-center px-4 py-2 text-xs font-semibold rounded-md shadow-sm text-white"
                        style="background:#076BA8;">
                    Add to gallery
                </button>

            </form>
        </div>

    </div>
</section>
