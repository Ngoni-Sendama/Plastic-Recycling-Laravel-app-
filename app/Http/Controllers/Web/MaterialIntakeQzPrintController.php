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
                'title' => 'Material Intake / Goods Received Note',
                'form' => 'Form CR-01 - Crushing Office',
                'company' => 'HIGHGLEN PLASTIC INDUSTRIES',
                'date' => (string) ($materialIntake->date?->toDateString() ?? '-'),
                'grnNumber' => (string) ($materialIntake->grn_number ?? '-'),
                'buyerName' => (string) ($materialIntake->buyer?->buyer_name ?? $materialIntake->buyer_name ?? '-'),
                'buyerContact' => (string) ($materialIntake->buyer?->contact_number ?? '-'),
                'material' => (string) ($materialIntake->material?->code ? $materialIntake->material->code.' - '.$materialIntake->material->name : ($materialIntake->material?->name ?? '-')),
                'grossWeight' => number_format((float) ($materialIntake->gross_weight_kg ?? 0), 3),
                'tareWeight' => number_format((float) ($materialIntake->tare_weight_kg ?? 0), 3),
                'netWeight' => number_format((float) ($materialIntake->net_weight_kg ?? 0), 3),
                'unitPrice' => number_format((float) ($materialIntake->unit_price ?? 0), 2),
                'totalValue' => number_format((float) ($materialIntake->total_value ?? 0), 2),
                'remarks' => (string) ($materialIntake->remarks ?? $materialIntake->note ?? '-'),
            ],
        ]);
    }
}
