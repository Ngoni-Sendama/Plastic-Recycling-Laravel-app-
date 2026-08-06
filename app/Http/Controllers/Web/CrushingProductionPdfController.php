<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CrushingProduction;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class CrushingProductionPdfController extends Controller
{
    public function __invoke(CrushingProduction $crushingProduction): Response
    {
        $crushingProduction->load(['material', 'recordedByUser']);

        $pdf = Pdf::loadView('pdf.crushing-production', ['production' => $crushingProduction]);

        return $pdf->download("crushing-production-{$crushingProduction->batch_number}.pdf");
    }
}
