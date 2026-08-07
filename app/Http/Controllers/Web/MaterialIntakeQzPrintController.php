<?php

namespace App\Http\Controllers\Web;

use App\Models\MaterialIntake;
use Illuminate\Contracts\View\View;

class MaterialIntakeQzPrintController
{
    public function __invoke(MaterialIntake $materialIntake): View
    {
        $materialIntake->load(['material', 'buyer', 'recordedByUser']);

        return view('web.qz-tray.material-intake', [
            'intake' => $materialIntake,
            'payload' => [
                'title' => 'Material Intake',
                'company' => 'Highglen Plastic Industries',
                'logoEscpos' => $this->logoToEscpos(),
                'date' => (string) ($materialIntake->date?->toDateString() ?? '-'),
                'grnNumber' => (string) ($materialIntake->grn_number ?? '-'),
                'buyerName' => (string) ($materialIntake->buyer?->buyer_name ?? $materialIntake->buyer_name ?? '-'),
                'material' => (string) ($materialIntake->material?->code ? $materialIntake->material->code.' - '.$materialIntake->material->name : ($materialIntake->material?->name ?? '-')),
                'grossWeight' => number_format((float) ($materialIntake->gross_weight_kg ?? 0), 3),
                'tareWeight' => number_format((float) ($materialIntake->tare_weight_kg ?? 0), 3),
                'netWeight' => number_format((float) ($materialIntake->net_weight_kg ?? 0), 3),
                'unitPrice' => number_format((float) ($materialIntake->unit_price ?? 0), 2),
                'totalValue' => number_format((float) ($materialIntake->total_value ?? 0), 2),
            ],
        ]);
    }

    private function logoToEscpos(): string
    {
        $path = public_path('icon.png');

        if (! is_file($path) || ! function_exists('imagecreatefrompng')) {
            return '';
        }

        $src = imagecreatefrompng($path);

        if ($src === false) {
            return '';
        }

        $targetWidth = 72;
        $srcWidth = imagesx($src);
        $srcHeight = imagesy($src);
        $targetHeight = (int) round(($targetWidth / $srcWidth) * $srcHeight);

        $resized = imagecreatetruecolor($targetWidth, $targetHeight);
        $white = imagecolorallocate($resized, 255, 255, 255);
        imagefill($resized, 0, 0, $white);
        imagecopyresampled($resized, $src, 0, 0, 0, 0, $targetWidth, $targetHeight, $srcWidth, $srcHeight);

        $bytes = [];
        $rowBytes = (int) ceil($targetWidth / 8);

        for ($x = 0; $x < $targetWidth; $x++) {
            for ($y = 0; $y < $targetHeight; $y += 8) {
                $byte = 0;
                for ($bit = 0; $bit < 8; $bit++) {
                    $py = $y + $bit;
                    if ($py < $targetHeight) {
                        $rgb = imagecolorat($resized, $x, $py);
                        $r = ($rgb >> 16) & 0xFF;
                        if ($r < 128) {
                            $byte |= (1 << (7 - $bit));
                        }
                    }
                }
                $bytes[] = $byte;
            }
        }

        imagedestroy($src);
        imagedestroy($resized);

        $xL = $targetWidth % 256;
        $xH = (int) ($targetWidth / 256);
        $yL = $targetHeight % 256;
        $yH = (int) ($targetHeight / 256);

        $escpos = chr(0x1D).chr(0x76).chr(0x30).chr(0x00)
            .chr($xL).chr($xH).chr($yL).chr($yH)
            .implode('', array_map('chr', $bytes));

        return bin2hex($escpos);
    }
}
