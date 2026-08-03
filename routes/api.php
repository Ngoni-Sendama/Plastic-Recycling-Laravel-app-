<?php

use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BuyerController;
use App\Http\Controllers\Api\CashRemittanceController;
use App\Http\Controllers\Api\CrushingProductionController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DispatchController;
use App\Http\Controllers\Api\FormSchemaController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\MaterialIntakeController;
use App\Http\Controllers\Api\PalletizingProductionController;
use App\Http\Controllers\Api\PalletizingReceiptController;
use App\Http\Controllers\Api\PelletSaleController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:api-login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/users', [UserController::class, 'index'])->middleware('api.permission:ViewAny:User');
    Route::post('/users', [UserController::class, 'store'])->middleware('api.permission:Create:User');
    Route::get('/users/{user}', [UserController::class, 'show'])->middleware('api.permission:View:User');
    Route::patch('/users/{user}', [UserController::class, 'update'])->middleware('api.permission:Update:User');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('api.permission:Delete:User');

    Route::get('/materials', [MaterialController::class, 'index'])->middleware('api.permission:ViewAny:Material');
    Route::get('/materials/trashed', [MaterialController::class, 'trashed'])->middleware('api.permission:ViewAny:Material');
    Route::get('/materials/{material}', [MaterialController::class, 'show'])->middleware('api.permission:View:Material');
    Route::post('/materials', [MaterialController::class, 'store'])->middleware('api.permission:Create:Material');
    Route::patch('/materials/{material}', [MaterialController::class, 'update'])->middleware('api.permission:Update:Material');
    Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->middleware('api.permission:Delete:Material');
    Route::delete('/materials/{material}/force', [MaterialController::class, 'forceDelete'])->middleware('api.permission:Delete:Material');
    Route::post('/materials/{material}/restore', [MaterialController::class, 'restore'])->middleware('api.permission:Update:Material');

    Route::get('/buyers', [BuyerController::class, 'index'])->middleware('api.permission:ViewAny:Buyer');
    Route::get('/buyers/{buyer}', [BuyerController::class, 'show'])->middleware('api.permission:View:Buyer');
    Route::post('/buyers', [BuyerController::class, 'store'])->middleware('api.permission:Create:Buyer');
    Route::patch('/buyers/{buyer}', [BuyerController::class, 'update'])->middleware('api.permission:Update:Buyer');
    Route::delete('/buyers/{buyer}', [BuyerController::class, 'destroy'])->middleware('api.permission:Delete:Buyer');

    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('api.permission:View:StatsOverview');

    Route::get('/form-schemas', [FormSchemaController::class, 'index']);

    Route::get('/reports/stock', [ReportController::class, 'stock'])->middleware('api.permission:View:StockSummary');
    Route::get('/reports/production', [ReportController::class, 'production'])->middleware('api.permission:View:ProductionSummary');
    Route::get('/reports/sales', [ReportController::class, 'sales'])->middleware('api.permission:View:SalesSummary');
    Route::get('/reports/cash-reconciliation', [ReportController::class, 'cashReconciliation'])->middleware('api.permission:View:CashReconciliation');
    Route::get('/audit-logs', [AuditLogController::class, 'index']);

    Route::get('/sync/pull', [SyncController::class, 'pull']);
    Route::post('/sync/push', [SyncController::class, 'push']);

    Route::get('/material-intakes', [MaterialIntakeController::class, 'index'])->middleware('api.permission:ViewAny:MaterialIntake');
    Route::get('/material-intakes/{materialIntake}', [MaterialIntakeController::class, 'show'])->middleware('api.permission:View:MaterialIntake');
    Route::post('/material-intakes', [MaterialIntakeController::class, 'store'])->middleware('api.permission:Create:MaterialIntake');
    Route::patch('/material-intakes/{materialIntake}', [MaterialIntakeController::class, 'update'])->middleware('api.permission:Update:MaterialIntake');

    Route::get('/crushing-productions', [CrushingProductionController::class, 'index'])->middleware('api.permission:ViewAny:CrushingProduction');
    Route::get('/crushing-productions/{crushingProduction}', [CrushingProductionController::class, 'show'])->middleware('api.permission:View:CrushingProduction');
    Route::post('/crushing-productions', [CrushingProductionController::class, 'store'])->middleware('api.permission:Create:CrushingProduction');
    Route::patch('/crushing-productions/{crushingProduction}', [CrushingProductionController::class, 'update'])->middleware('api.permission:Update:CrushingProduction');
    Route::delete('/crushing-productions/{crushingProduction}', [CrushingProductionController::class, 'destroy'])->middleware('api.permission:Delete:CrushingProduction');

    Route::get('/dispatches', [DispatchController::class, 'index'])->middleware('api.permission:ViewAny:Dispatch');
    Route::get('/dispatches/{dispatch}', [DispatchController::class, 'show'])->middleware('api.permission:View:Dispatch');
    Route::post('/dispatches', [DispatchController::class, 'store'])->middleware('api.permission:Create:Dispatch');
    Route::patch('/dispatches/{dispatch}', [DispatchController::class, 'update'])->middleware('api.permission:Update:Dispatch');
    Route::delete('/dispatches/{dispatch}', [DispatchController::class, 'destroy'])->middleware('api.permission:Delete:Dispatch');

    Route::get('/palletizing-receipts', [PalletizingReceiptController::class, 'index'])->middleware('api.permission:ViewAny:PalletizingReceipt');
    Route::get('/palletizing-receipts/{palletizingReceipt}', [PalletizingReceiptController::class, 'show'])->middleware('api.permission:View:PalletizingReceipt');
    Route::post('/palletizing-receipts', [PalletizingReceiptController::class, 'store'])->middleware('api.permission:Create:PalletizingReceipt');
    Route::patch('/palletizing-receipts/{palletizingReceipt}', [PalletizingReceiptController::class, 'update'])->middleware('api.permission:Update:PalletizingReceipt');
    Route::delete('/palletizing-receipts/{palletizingReceipt}', [PalletizingReceiptController::class, 'destroy'])->middleware('api.permission:Delete:PalletizingReceipt');

    Route::get('/palletizing-productions', [PalletizingProductionController::class, 'index'])->middleware('api.permission:ViewAny:PalletizingProduction');
    Route::get('/palletizing-productions/{palletizingProduction}', [PalletizingProductionController::class, 'show'])->middleware('api.permission:View:PalletizingProduction');
    Route::post('/palletizing-productions', [PalletizingProductionController::class, 'store'])->middleware('api.permission:Create:PalletizingProduction');
    Route::patch('/palletizing-productions/{palletizingProduction}', [PalletizingProductionController::class, 'update'])->middleware('api.permission:Update:PalletizingProduction');
    Route::delete('/palletizing-productions/{palletizingProduction}', [PalletizingProductionController::class, 'destroy'])->middleware('api.permission:Delete:PalletizingProduction');

    Route::get('/pellet-sales', [PelletSaleController::class, 'index'])->middleware('api.permission:ViewAny:PelletSale');
    Route::get('/pellet-sales/{pelletSale}', [PelletSaleController::class, 'show'])->middleware('api.permission:View:PelletSale');
    Route::get('/pellet-sales/{pelletSale}/pdf', [PelletSaleController::class, 'pdf'])->middleware('api.permission:View:PelletSale');
    Route::post('/pellet-sales', [PelletSaleController::class, 'store'])->middleware('api.permission:Create:PelletSale');
    Route::patch('/pellet-sales/{pelletSale}', [PelletSaleController::class, 'update'])->middleware('api.permission:Update:PelletSale');
    Route::delete('/pellet-sales/{pelletSale}', [PelletSaleController::class, 'destroy'])->middleware('api.permission:Delete:PelletSale');

    Route::get('/cash-remittances', [CashRemittanceController::class, 'index'])->middleware('api.permission:ViewAny:CashRemittance');
    Route::get('/cash-remittances/{cashRemittance}', [CashRemittanceController::class, 'show'])->middleware('api.permission:View:CashRemittance');
    Route::post('/cash-remittances', [CashRemittanceController::class, 'store'])->middleware('api.permission:Create:CashRemittance');
    Route::patch('/cash-remittances/{cashRemittance}', [CashRemittanceController::class, 'update'])->middleware('api.permission:Update:CashRemittance');
    Route::delete('/cash-remittances/{cashRemittance}', [CashRemittanceController::class, 'destroy'])->middleware('api.permission:Delete:CashRemittance');
});
