<?php

namespace App\Http\Controllers\Web;

use App\Models\MaterialIntake;
use Illuminate\Contracts\View\View;

class MaterialIntakeQzPrintController
{
    public function __invoke(MaterialIntake $materialIntake): View
    {
        $materialIntake->load(['material', 'buyer', 'recordedByUser']);

        $logoPath = public_path('icon.png');
        $logoBase64 = is_file($logoPath)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
            : '';

        return view('web.qz-tray.material-intake', [
            'intake' => $materialIntake,
            'payload' => [
                'title' => 'Material Intake',
                'company' => 'Highglen Plastic Industries',
                'logoBase64' => $logoBase64,
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
}
