<?php

namespace App\Http\Controllers;

use App\Models\Dog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

// Intervention Image v3 (no facade)
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class DogPdfController extends Controller
{
    /**
     * QUICK VISUAL TEST (recommended first):
     * Returns the oriented JPEG bytes directly in the browser, no PDF.
     * Route GET /dogs/{dog}/photo-test to this to validate orientation fast.
     */
    public function photoTest(Request $request, Dog $dog)
    {
        $rel = $dog->photo ?? $dog->photo_path ?? null;
        if (!$rel || !Storage::disk('public')->exists($rel)) {
            abort(404, 'Photo not found on public disk.');
        }

        $abs = Storage::disk('public')->path($rel);

        // v3 manager (GD); auto EXIF orientation is on by default,
        // but we call ->orient() explicitly to be safe.
        $manager = new ImageManager(Driver::class);

        try {
            $img = $manager->read($abs)->orient(); // ← core fix

            // optional: cap width so we don’t send 10MB to the browser
            $img->scaleDown(width: 1600);

            // Encode to JPEG and send raw bytes
            $encoded = $img->toJpeg(quality: 85);

            return response($encoded->getString(), 200, [
                'Content-Type' => 'image/jpeg',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            ]);
        } catch (\Throwable $e) {
            abort(500, 'Failed to read/orient image: '.$e->getMessage());
        }
    }

    /**
     * BAREBONES PDF (image only):
     * Creates a tiny HTML with just the oriented image embedded as a data URI.
     * This keeps the pipeline minimal while we verify orientation inside DomPDF.
     */
    public function overview(Request $request, Dog $dog)
    {
        $rel = $dog->photo ?? $dog->photo_path ?? null;
        if (!$rel || !Storage::disk('public')->exists($rel)) {
            // If no photo, render a tiny PDF saying so
            $html = '<!doctype html><html><body style="font-family:DejaVu Sans, Arial">
                        <p>No photo found for this dog.</p>
                     </body></html>';
            return Pdf::loadHTML($html)
                ->setPaper('a4', 'landscape')
                ->setWarnings(false)
                ->setOption('dpi', 96)
                ->stream('Dog-'.$dog->id.'-Overview.pdf');
        }

        $abs = Storage::disk('public')->path($rel);

        $manager = new ImageManager(Driver::class);

        try {
            $img = $manager->read($abs)->orient(); // ← core fix

            // keep it reasonable for PDF and strip EXIF by re-encoding
            $img->scaleDown(width: 1600);

            // Use data URI so we avoid file:// headaches during this test
            $dataUri = $img->toJpeg(quality: 85)->toDataUri();

            $html = <<<HTML
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    @page { size: A4 landscape; margin: 8mm; }
    body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; }
    img  { display:block; max-width:100%; height:auto; }
  </style>
</head>
<body>
  <div style="text-align:center;">
    <img src="{$dataUri}" alt="Dog Photo">
  </div>
</body>
</html>
HTML;

            return Pdf::loadHTML($html)
                ->setPaper('a4', 'landscape')
                ->setWarnings(false)
                ->setOption('dpi', 96)
                ->setOption('isRemoteEnabled', true)
                ->stream('Dog-'.$dog->id.'-Overview.pdf');

        } catch (\Throwable $e) {
            $fallback = '<!doctype html><html><body style="font-family:DejaVu Sans, Arial">
                           <p>Failed to read/orient image: '.e($e->getMessage()).'</p>
                         </body></html>';
            return Pdf::loadHTML($fallback)
                ->setPaper('a4', 'landscape')
                ->setWarnings(false)
                ->setOption('dpi', 96)
                ->stream('Dog-'.$dog->id.'-Overview.pdf');
        }
    }
}
