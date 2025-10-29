<?php

namespace App\Http\Controllers;

use App\Models\Dog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        // storage/app/public
        if ($pathOrUrl && Storage::disk('public')->exists($pathOrUrl)) {
            $abs  = Storage::disk('public')->path($pathOrUrl);
            $mime = mime_content_type($abs) ?: 'image/jpeg';
            $data = @file_get_contents($abs) ?: '';
            if ($data !== '') return 'data:'.$mime.';base64,'.base64_encode($data);
        }
        // /public path
        if ($pathOrUrl && is_file(public_path($pathOrUrl))) {
            $abs  = public_path($pathOrUrl);
            $mime = mime_content_type($abs) ?: 'image/jpeg';
            $data = @file_get_contents($abs) ?: '';
            if ($data !== '') return 'data:'.$mime.';base64,'.base64_encode($data);
        }
        // fallback placeholder
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="512" height="512"><rect width="100%" height="100%" fill="#e5e7eb"/><text x="50%" y="50%" font-size="28" text-anchor="middle" fill="#6b7280" dy=".3em">Dog</text></svg>';
        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
