<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CashRemittanceController;
use App\Http\Controllers\Api\CrushingProductionController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DispatchController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\MaterialIntakeController;
use App\Http\Controllers\Api\PalletizingProductionController;
use App\Http\Controllers\Api\PalletizingReceiptController;
use App\Http\Controllers\Api\PelletSaleController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:api-login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::patch('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);

    Route::get('/materials', [MaterialController::class, 'index']);
    Route::post('/materials', [MaterialController::class, 'store']);
    Route::patch('/materials/{material}', [MaterialController::class, 'update']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/sync/pull', [SyncController::class, 'pull']);
    Route::post('/sync/push', [SyncController::class, 'push']);

    Route::get('/material-intakes', [MaterialIntakeController::class, 'index']);
    Route::post('/material-intakes', [MaterialIntakeController::class, 'store']);

    Route::get('/crushing-productions', [CrushingProductionController::class, 'index']);
    Route::post('/crushing-productions', [CrushingProductionController::class, 'store']);

    Route::get('/dispatches', [DispatchController::class, 'index']);
    Route::post('/dispatches', [DispatchController::class, 'store']);

    Route::get('/palletizing-receipts', [PalletizingReceiptController::class, 'index']);
    Route::post('/palletizing-receipts', [PalletizingReceiptController::class, 'store']);

    Route::get('/palletizing-productions', [PalletizingProductionController::class, 'index']);
    Route::post('/palletizing-productions', [PalletizingProductionController::class, 'store']);

    Route::get('/pellet-sales', [PelletSaleController::class, 'index']);
    Route::post('/pellet-sales', [PelletSaleController::class, 'store']);

    Route::get('/cash-remittances', [CashRemittanceController::class, 'index']);
    Route::post('/cash-remittances', [CashRemittanceController::class, 'store']);
});
