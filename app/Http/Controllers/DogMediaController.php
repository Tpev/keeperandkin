<?php

namespace App\Http\Controllers;

use App\Models\Dog;
use App\Models\DogMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DogMediaController extends Controller
{
    public function store(Request $request, Dog $dog)
    {
        $data = $request->validate([
            'media_type' => 'required|in:image,video',
            'file'       => 'nullable|file|mimes:jpg,jpeg,png,webp,mp4,mov,avi|max:51200', // 50MB
            'video_url'  => 'nullable|url',
            'caption'    => 'nullable|string|max:255',
        ]);

        // determine next sort order
        $maxSort = $dog->media()->max('sort_order') ?? 0;
        $sortOrder = $maxSort + 1;

        $media = new DogMedia();
        $media->dog_id = $dog->id;
        $media->media_type = $data['media_type'];
        $media->caption = $data['caption'] ?? null;
        $media->sort_order = $sortOrder;

        if ($data['media_type'] === 'image') {
            // upload image
            if ($request->hasFile('file')) {
                $path = $request->file('file')->store('dogs/gallery', 'public');
                $media->file_path = $path;
            } else {
                return back()->withErrors(['file' => 'Image file is required for media_type=image']);
            }
        } elseif ($data['media_type'] === 'video') {
            // two options:
            // 1) uploaded video file
            // 2) hosted video url
            if ($request->hasFile('file')) {
                $path = $request->file('file')->store('dogs/videos', 'public');
                $media->file_path = $path;
            } elseif (!empty($data['video_url'])) {
                $media->video_url = $data['video_url'];
            } else {
                return back()->withErrors(['file' => 'Provide either a video file or a video URL.']);
            }
        }

        $media->save();

        return back()->with('success', 'Media added to gallery.');
    }

    public function destroy(Dog $dog, DogMedia $media)
    {
        // safety: make sure this media belongs to the dog
        if ($media->dog_id !== $dog->id) {
            abort(403);
        }

        // delete file if stored
        if ($media->file_path) {
            Storage::disk('public')->delete($media->file_path);
        }

        $media->delete();

        return back()->with('success', 'Media removed from gallery.');
    }
}
