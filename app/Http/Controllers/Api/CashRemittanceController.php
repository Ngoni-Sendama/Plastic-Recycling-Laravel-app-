<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreCashRemittanceRequest;
use App\Http\Resources\CashRemittanceResource;
use App\Models\CashRemittance;
use App\Services\CashRemittanceCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CashRemittanceController extends ApiController
{
    public function index(): AnonymousResourceCollection
    {
        return CashRemittanceResource::collection(
            CashRemittance::with(['recordedByUser'])->latest('date')->get(),
        );
    }

    public function show(CashRemittance $cashRemittance): CashRemittanceResource
    {
        $cashRemittance->load(['recordedByUser']);

        return new CashRemittanceResource($cashRemittance);
    }

    public function store(StoreCashRemittanceRequest $request): JsonResponse
    {
        $data = $request->validated();

        $deleted = CashRemittance::withTrashed()->where('voucher_number', $data['voucher_number'] ?? null)->first();
        if ($deleted && $deleted->trashed()) {
            $calculated = CashRemittanceCalculator::calculate($data);
            $deleted->restore();
            $deleted->update([
                'date' => $data['date'],
                'period_covered' => $data['period_covered'] ?? null,
                'chips_delivered_kg' => $data['chips_delivered_kg'],
                'recovery_price_per_kg' => $data['recovery_price_per_kg'],
                'sales_revenue' => $data['sales_revenue'],
                'cash_remitted' => $data['cash_remitted'],
                'max_remittance_due' => $calculated['max_remittance_due'],
                'balance_retained' => $calculated['balance_retained'],
            ]);
            $deleted->load(['recordedByUser']);

            return response()->json([
                'message' => 'This record was previously deleted and has been restored.',
                'data' => new CashRemittanceResource($deleted),
                'restored' => true,
            ], 201);
        }

        $calculated = CashRemittanceCalculator::calculate($data);

        $remittance = CashRemittance::create([
            'date' => $data['date'],
            'period_covered' => $data['period_covered'] ?? null,
            'chips_delivered_kg' => $data['chips_delivered_kg'],
            'recovery_price_per_kg' => $data['recovery_price_per_kg'],
            'sales_revenue' => $data['sales_revenue'],
            'cash_remitted' => $data['cash_remitted'],
            'max_remittance_due' => $calculated['max_remittance_due'],
            'balance_retained' => $calculated['balance_retained'],
            'recorded_by_user_id' => $request->user()->id,
        ]);
        $remittance->load(['recordedByUser']);

        return response()->json([
            'message' => 'Record created successfully.',
            'data' => new CashRemittanceResource($remittance),
            'restored' => false,
        ], 201);
    }

    public function update(StoreCashRemittanceRequest $request, CashRemittance $cashRemittance): CashRemittanceResource
    {
        $data = $request->validated();
        $calculated = CashRemittanceCalculator::calculate($data);

        $cashRemittance->update([
            'date' => $data['date'],
            'period_covered' => $data['period_covered'] ?? null,
            'chips_delivered_kg' => $data['chips_delivered_kg'],
            'recovery_price_per_kg' => $data['recovery_price_per_kg'],
            'sales_revenue' => $data['sales_revenue'],
            'cash_remitted' => $data['cash_remitted'],
            'max_remittance_due' => $calculated['max_remittance_due'],
            'balance_retained' => $calculated['balance_retained'],
        ]);
        $cashRemittance->load(['recordedByUser']);

        return new CashRemittanceResource($cashRemittance);
    }

    public function destroy(CashRemittance $cashRemittance): JsonResponse
    {
        $cashRemittance->delete();

        return response()->json(['message' => 'Cash remittance deleted successfully.']);
    }

    public function restore(CashRemittance $cashRemittance): JsonResponse
    {
        $cashRemittance->restore();

        return response()->json(['message' => 'Record restored successfully.']);
    }

    public function trashed(): AnonymousResourceCollection
    {
        return CashRemittanceResource::collection(
            CashRemittance::onlyTrashed()->with(['recordedByUser'])->latest('date')->get(),
        );
    }

    public function forceDelete(CashRemittance $cashRemittance): JsonResponse
    {
        $cashRemittance->forceDelete();

        return response()->json(['message' => 'Record permanently deleted.']);
    }
}
