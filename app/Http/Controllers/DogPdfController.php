<?php

namespace App\Http\Controllers;

use App\Models\Dog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

// QR code (GD PNG output)
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRGdImagePNG;

class DogPdfController extends Controller
{
    public function overview(Request $request, Dog $dog)
    {
        $dog->loadMissing(['latestEvaluation']);

        $profileUrl = route('dogs.show', $dog);

        // --- QR (writes a real PNG file so DomPDF can load it) ---
        $options = new QROptions;
        $options->outputInterface  = QRGdImagePNG::class;
        $options->eccLevel         = QRCode::ECC_L;
        $options->scale            = 4;
        $options->margin           = 0;
        $options->outputBase64     = false;

        $qrPngBytes = (new QRCode($options))->render($profileUrl);

        $qrDir = storage_path('app/public/qr');
        if (!is_dir($qrDir)) @mkdir($qrDir, 0775, true);
        $qrFile  = sprintf('dog-%d-%s.png', $dog->id, substr(md5($profileUrl), 0, 8));
        $qrAbs   = $qrDir . DIRECTORY_SEPARATOR . $qrFile;
        file_put_contents($qrAbs, $qrPngBytes);
        $qrFileUri = 'file://' . $qrAbs;

        // --- Oriented photo for PDF (physical rotation, EXIF stripped) ---
        $photoRel = $dog->photo ?? $dog->photo_path ?? null; // adapt to your column
        $photoFileUri = $this->imageToFileUriForPdf($photoRel);

        $data = [
            'dog'            => $dog,
            'latestEval'     => $dog->latestEvaluation,
            'qrFileUri'      => $qrFileUri,
            'photoFileUri'   => $photoFileUri,  // Blade uses this
            'profileUrl'     => $profileUrl,
            'palette'        => [
                'NAVY'     => '#03314C',
                'BLUE'     => '#076BA8',
                'BLUE_ALT' => '#DAEEFF',
                'DIVIDER'  => '#E2E8F0',
                'DANGER'   => '#DC2626',
                'GREEN'    => '#16A34A',
                'ORANGE'   => '#F97316',
                'YELLOW'   => '#FFCC00',
                'OK'       => '#94A3B8',
            ],
        ];

        $html = view('pdf.dogs.overview', $data)->render();

        return Pdf::loadHTML($html)
            ->setPaper('a4', 'landscape')
            ->setWarnings(false)
            ->setOption('isRemoteEnabled', true) // allow file://
            ->setOption('dpi', 96)
            ->setOption('isHtml5ParserEnabled', false)
            ->stream('Dog-'.$dog->id.'-Overview.pdf');
    }

    /**
     * Turn a stored image (public disk or public path) into a DomPDF-safe, physically oriented JPG.
     * Returns file:// URI or null if not found.
     */
    private function imageToFileUriForPdf(?string $relOrPublicPath): ?string
    {
        // Locate original absolute path
        $abs = null;
        if ($relOrPublicPath) {
            if (Storage::disk('public')->exists($relOrPublicPath)) {
                $abs = Storage::disk('public')->path($relOrPublicPath);
            } elseif (is_file(public_path($relOrPublicPath))) {
                $abs = public_path($relOrPublicPath);
            }
        }
        if (!$abs || !is_file($abs)) return null;

        // Prepare cache target
        $cacheDir = storage_path('app/public/pdf-cache');
        if (!is_dir($cacheDir)) @mkdir($cacheDir, 0775, true);
        $out = $cacheDir.'/dog-photo-'.Str::random(10).'.jpg';

        try {
            // IMPORTANT: orientate() BEFORE any resize/fit to honor EXIF
            $img = Image::make($abs)->orientate();

            // Keep size reasonable for PDF, strip EXIF by re-encoding
            $img->resize(1600, null, function ($c) {
                $c->aspectRatio();
                $c->upsize();
            });

            $img->encode('jpg', 85)->save($out);

            return 'file://'.$out;
        } catch (\Throwable $e) {
            // Fallback: copy as-is so at least something renders
            @copy($abs, $out);
            return 'file://'.$out;
        }
    }
}
