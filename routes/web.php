<?php

use App\Http\Controllers\Web\CrushingProductionPdfController;
use App\Http\Controllers\Web\MaterialIntakeQzPrintController;
use App\Http\Controllers\Web\QzTrayController;
use App\Http\Controllers\Web\StockCashControlExportController;
use App\Models\CashRemittance;
use App\Models\CrushingProduction;
use App\Models\Dispatch;
use App\Models\MaterialIntake;
use App\Models\PalletizingProduction;
use App\Models\PalletizingReceipt;
use App\Models\PelletSale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;

Route::redirect('/docs', '/docs/index.html');
Route::redirect('/qz-tray', '/qz-tray/index.html');
Route::middleware('auth')->group(function () {
    Route::get('/qz-tray/certificate', [QzTrayController::class, 'certificate'])->name('qz-tray.certificate');
    Route::post('/qz-tray/sign', [QzTrayController::class, 'sign'])->name('qz-tray.sign');
});
Route::get('/exports/stock-cash-control.xlsx', StockCashControlExportController::class)
    ->name('exports.stock-cash-control');
Route::get('/exports/crushing-productions/{crushingProduction}/pdf', CrushingProductionPdfController::class)
    ->name('exports.crushing-production.pdf')
    ->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/crushing-productions/{crushingProduction}/pdf', function (CrushingProduction $crushingProduction) {
        $crushingProduction->load(['material', 'recordedByUser']);

        $pdf = Pdf::loadView('pdf.crushing-production', [
            'production' => $crushingProduction,
        ]);

        return $pdf->download("crushing-production-{$crushingProduction->batch_number}.pdf");
    })->name('web.crushing-productions.pdf');

    Route::get('/material-intakes/{materialIntake}/pdf', function (MaterialIntake $materialIntake) {
        $materialIntake->load(['material', 'recordedByUser', 'buyer']);

        $pdf = Pdf::loadView('pdf.material-intake', [
            'intake' => $materialIntake,
        ]);

        return $pdf->download("material-intake-{$materialIntake->grn_number}.pdf");
    })->name('web.material-intakes.pdf');

    Route::get('/material-intakes/{materialIntake}/qz-print', MaterialIntakeQzPrintController::class)
        ->name('web.material-intakes.qz-print');

    Route::get('/dispatches/{dispatch}/pdf', function (Dispatch $dispatch) {
        $dispatch->load(['material', 'recordedByUser', 'crushingProduction']);

        $pdf = Pdf::loadView('pdf.dispatch-note', [
            'dispatch' => $dispatch,
        ]);

        return $pdf->download("dispatch-{$dispatch->dispatch_note_number}.pdf");
    })->name('web.dispatches.pdf');

    Route::get('/palletizing-receipts/{palletizingReceipt}/pdf', function (PalletizingReceipt $palletizingReceipt) {
        $palletizingReceipt->load(['material', 'recordedByUser', 'dispatch']);

        $pdf = Pdf::loadView('pdf.palletizing-receipt', [
            'receipt' => $palletizingReceipt,
        ]);

        return $pdf->download("palletizing-receipt-{$palletizingReceipt->grn_number}.pdf");
    })->name('web.palletizing-receipts.pdf');

    Route::get('/palletizing-productions/{palletizingProduction}/pdf', function (PalletizingProduction $palletizingProduction) {
        $palletizingProduction->load(['recordedByUser', 'palletizingReceipt']);

        $pdf = Pdf::loadView('pdf.palletizing-production', [
            'production' => $palletizingProduction,
        ]);

        return $pdf->download("palletizing-production-{$palletizingProduction->batch_number}.pdf");
    })->name('web.palletizing-productions.pdf');

    Route::get('/pellet-sales/{pelletSale}/pdf', function (PelletSale $pelletSale) {
        $pelletSale->load(['recordedByUser']);

        $pdf = Pdf::loadView('pdf.sale-receipt', [
            'sale' => $pelletSale,
        ]);

        return $pdf->download("pellet-sale-{$pelletSale->receipt_number}.pdf");
    })->name('web.pellet-sales.pdf');

    Route::get('/cash-remittances/{cashRemittance}/pdf', function (CashRemittance $cashRemittance) {
        $cashRemittance->load(['recordedByUser']);

        $pdf = Pdf::loadView('pdf.cash-remittance', [
            'remittance' => $cashRemittance,
        ]);

        return $pdf->download("cash-remittance-{$cashRemittance->voucher_number}.pdf");
    })->name('web.cash-remittances.pdf');
});
