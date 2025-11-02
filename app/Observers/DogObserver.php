<?php

namespace App\Observers;

use App\Models\Dog;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class DogObserver
{
    /**
     * Normalize photo file (orientation + EXIF strip + optional resize)
     */
    public function saved(Dog $dog): void
    {
        $path = $dog->photo ?? $dog->photo_path ?? null;
        if (!$path || !Storage::disk('public')->exists($path)) return;

        $abs = Storage::disk('public')->path($path);

        try {
            $img = Image::make($abs);

            if (function_exists('exif_read_data')) {
                // Apply physical rotation if EXIF says so
                $img->orientate();
            }

            // Keep PDFs small & consistent
            $img->resize(2400, null, function ($c) {
                $c->aspectRatio();
                $c->upsize();
            });

            // Re-encode to JPEG (strips EXIF), overwrite the same file
            $img->encode('jpg', 85)->save($abs);

        } catch (\Throwable $e) {
            // Silent fail: keep original file if something goes wrong
            \Log::warning('DogObserver normalize failed', [
                'dog_id' => $dog->id,
                'photo'  => $path,
                'err'    => $e->getMessage(),
            ]);
        }
    }
}
