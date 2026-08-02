<?php

use App\Http\Controllers\Web\StockCashControlExportController;
use Illuminate\Support\Facades\Route;

Route::redirect('/docs', '/docs/index.html');
Route::get('/exports/stock-cash-control.xlsx', StockCashControlExportController::class)
    ->name('exports.stock-cash-control');
