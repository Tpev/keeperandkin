<?php

namespace App\Http\Controllers;

use App\Models\Dog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

        /**
         * Generate QR PNG to a real file (most reliable for DomPDF)
         */
        $options = new QROptions;
        $options->outputInterface  = QRGdImagePNG::class; // <- use the GD image outputter
        $options->eccLevel         = QRCode::ECC_L;
        $options->scale            = 4;      // size of modules (increase if needed)
        $options->margin           = 0;      // tight QR
        $options->outputBase64     = false;  // we want raw PNG bytes

        $qrPngBytes = (new QRCode($options))->render($profileUrl);

        // Ensure storage path exists
        $dir   = storage_path('app/public/qr');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $file  = sprintf('dog-%d-%s.png', $dog->id, substr(md5($profileUrl), 0, 8));
        $abs   = $dir . DIRECTORY_SEPARATOR . $file;

        // Write the PNG bytes
        file_put_contents($abs, $qrPngBytes);

        // Build a file:// URI for DomPDF <img src>
        $qrFileUri = 'file://' . $abs;

        /**
         * View data (no logo for now)
         */
        $data = [
            'dog'          => $dog,
            'latestEval'   => $dog->latestEvaluation,
            'photoDataUri' => $this->imageToDataUri($dog->photo ?? $dog->photo_path ?? null),
            'qrFileUri'    => $qrFileUri,
            'profileUrl'   => $profileUrl,
            'palette'      => [
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
            ->setOption('isRemoteEnabled', true)     // allow file:// URIs
            ->setOption('dpi', 96)
            ->setOption('isHtml5ParserEnabled', false)
            ->stream('Dog-'.$dog->id.'-Overview.pdf');
    }



private function imageToDataUri(?string $pathOrUrl): string
{
    $process = function (string $abs): ?string {
        // Try Intervention first (with conditional EXIF), then fall back to raw bytes
        try {
            $img = Image::make($abs);

            if (function_exists('exif_read_data')) {
                // Only call orientate if EXIF exists locally
                $img->orientate();
            }

            // Keep memory under control for DomPDF
            $img->resize(1600, null, function ($c) { $c->aspectRatio(); $c->upsize(); });

            $bytes = (string) $img->encode('jpg', 85); // strip EXIF, flatten
            return 'data:image/jpeg;base64,' . base64_encode($bytes);
        } catch (\Throwable $e) {
            // Fallback: raw file bytes (works even without EXIF/Intervention)
            $bytes = @file_get_contents($abs);
            if ($bytes !== false && $bytes !== '') {
                $mime = mime_content_type($abs) ?: 'image/jpeg';
                return 'data:' . $mime . ';base64,' . base64_encode($bytes);
            }
            return null;
        }
    };

    // storage/app/public
    if ($pathOrUrl && \Storage::disk('public')->exists($pathOrUrl)) {
        $abs = \Storage::disk('public')->path($pathOrUrl);
        if ($dataUri = $process($abs)) return $dataUri;
    }

    // public/ path
    if ($pathOrUrl && is_file(public_path($pathOrUrl))) {
        $abs = public_path($pathOrUrl);
        if ($dataUri = $process($abs)) return $dataUri;
    }

    // Last-resort PNG placeholder (DomPDF-friendly; avoid SVG data URIs)
    $blankPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAuMBgVd2+5kAAAAASUVORK5CYII=');
    return 'data:image/png;base64,' . base64_encode($blankPng);
}

}
