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

        /**
         * Generate QR PNG file
         */
        $options = new QROptions;
        $options->outputInterface  = QRGdImagePNG::class;
        $options->eccLevel         = QRCode::ECC_L;
        $options->scale            = 4;
        $options->margin           = 0;
        $options->outputBase64     = false;

        $qrBytes = (new QRCode($options))->render($profileUrl);
        $qrDir   = storage_path('app/public/qr');
        if (!is_dir($qrDir)) @mkdir($qrDir, 0775, true);
        $qrFile  = sprintf('dog-%d-%s.png', $dog->id, substr(md5($profileUrl), 0, 8));
        $qrAbs   = $qrDir . DIRECTORY_SEPARATOR . $qrFile;
        file_put_contents($qrAbs, $qrBytes);
        $qrFileUri = 'file://' . $qrAbs;

        /**
         * Prepare data for view
         */
        $data = [
            'dog'          => $dog,
            'latestEval'   => $dog->latestEvaluation,
            'photoFileUri' => $this->imageToFileUri($dog->photo ?? $dog->photo_path ?? null),
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
            ->setOption('isRemoteEnabled', true)
            ->setOption('chroot', base_path())  // allow storage paths
            ->setOption('dpi', 96)
            ->setOption('isHtml5ParserEnabled', false)
            ->stream('Dog-' . $dog->id . '-Overview.pdf');
    }

    /**
     * Normalize image orientation and return file:// URI
     */
    private function imageToFileUri(?string $pathOrUrl): ?string
    {
        $process = function (string $abs): ?string {
            try {
                $img = Image::make($abs);
                if (function_exists('exif_read_data')) {
                    $img->orientate();
                }

                // Resize for PDF and flatten EXIF
                $img->resize(1600, null, function ($c) {
                    $c->aspectRatio();
                    $c->upsize();
                })->encode('jpg', 85);

                $dir = storage_path('app/public/pdf-cache');
                if (!is_dir($dir)) @mkdir($dir, 0775, true);
                $fname = 'dog-photo-' . Str::random(10) . '.jpg';
                $out = $dir . DIRECTORY_SEPARATOR . $fname;
                $img->save($out);

                return 'file://' . $out;
            } catch (\Throwable $e) {
                // Fallback: copy raw file
                $bytes = @file_get_contents($abs);
                if ($bytes) {
                    $dir = storage_path('app/public/pdf-cache');
                    if (!is_dir($dir)) @mkdir($dir, 0775, true);
                    $ext = pathinfo($abs, PATHINFO_EXTENSION) ?: 'jpg';
                    $out = $dir . DIRECTORY_SEPARATOR . 'dog-photo-raw-' . Str::random(8) . '.' . $ext;
                    @file_put_contents($out, $bytes);
                    return 'file://' . $out;
                }
                return null;
            }
        };

        // Try storage disk
        if ($pathOrUrl && Storage::disk('public')->exists($pathOrUrl)) {
            $abs = Storage::disk('public')->path($pathOrUrl);
            if ($uri = $process($abs)) return $uri;
        }

        // Try public/
        if ($pathOrUrl && is_file(public_path($pathOrUrl))) {
            $abs = public_path($pathOrUrl);
            if ($uri = $process($abs)) return $uri;
        }

        return null;
    }
}
