<?php

namespace App\Livewire\Dogs;

use App\Models\Dog;
use App\Models\DogMedia;
use Livewire\Component;
use Livewire\WithFileUploads;

class DogGallery extends Component
{
    use WithFileUploads;

    public Dog $dog;

    // Form fields
    public string $media_type = 'image';
    public $file;
    public ?string $video_url = null;
    public ?string $caption = null;

    protected $rules = [
        'media_type' => 'required|in:image,video',
        'file'       => 'nullable|file|max:51200', // 50MB, mime handled manually
        'video_url'  => 'nullable|url',
        'caption'    => 'nullable|string|max:255',
    ];

    public function mount(Dog $dog): void
    {
        $this->dog = $dog;
    }

    public function updatedMediaType(): void
    {
        // Reset fields that don't apply
        $this->file = null;
        $this->video_url = null;
    }

    public function addMedia(): void
    {
        $this->validate();

        // Quick mime guard
        if ($this->file) {
            $mime = $this->file->getMimeType();
            $isImage = str_starts_with($mime, 'image/');
            $isVideo = str_starts_with($mime, 'video/');

            if ($this->media_type === 'image' && !$isImage) {
                $this->addError('file', 'Le fichier doit être une image.');
                return;
            }

            if ($this->media_type === 'video' && !$isVideo) {
                $this->addError('file', 'Le fichier doit être une vidéo.');
                return;
            }
        }

        $maxSort = DogMedia::where('dog_id', $this->dog->id)->max('sort_order') ?? 0;
        $sortOrder = $maxSort + 1;

        $media = new DogMedia();
        $media->dog_id = $this->dog->id;
        $media->media_type = $this->media_type;
        $media->caption = $this->caption ?: null;
        $media->sort_order = $sortOrder;

        if ($this->media_type === 'image') {
            if (!$this->file) {
                $this->addError('file', 'Merci de sélectionner une image.');
                return;
            }
            $path = $this->file->store('dogs/gallery', 'public');
            $media->file_path = $path;
        }

        if ($this->media_type === 'video') {
            if ($this->file) {
                $path = $this->file->store('dogs/videos', 'public');
                $media->file_path = $path;
            } elseif ($this->video_url) {
                $media->video_url = $this->video_url;
            } else {
                $this->addError('file', 'Ajoutez un fichier vidéo ou une URL.');
                return;
            }
        }

        $media->save();

        // Reset form fields
        $this->reset(['file', 'video_url', 'caption']);
        $this->media_type = 'image';

        // Refresh dog relation
        $this->dog->refresh();

        session()->flash('gallery_success', 'Média ajouté à la galerie.');
    }

    public function deleteMedia(int $mediaId): void
    {
        $media = DogMedia::where('dog_id', $this->dog->id)->findOrFail($mediaId);

        if ($media->file_path) {
            \Storage::disk('public')->delete($media->file_path);
        }

        $media->delete();

        $this->dog->refresh();

        session()->flash('gallery_success', 'Média supprimé de la galerie.');
    }

    public function render()
    {
        $mediaItems = $this->dog->media()->orderBy('sort_order')->get();

        return view('livewire.dogs.dog-gallery', [
            'mediaItems' => $mediaItems,
        ]);
    }
}
