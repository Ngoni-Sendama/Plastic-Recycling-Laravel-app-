<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\StockCashControlWorkbookExporter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StockCashControlExportController extends Controller
{
    public function __invoke(Request $request, StockCashControlWorkbookExporter $exporter): BinaryFileResponse
    {
        $path = $exporter->export();

        return response()->download($path, 'Highglen_Plastic_Industries_Stock_Cash_Control.xlsx')
            ->deleteFileAfterSend(true);
    }
}
