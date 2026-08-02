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

    public function store(StoreCashRemittanceRequest $request): CashRemittanceResource
    {
        $data = $request->validated();
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

        return new CashRemittanceResource($remittance);
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

        return new CashRemittanceResource($cashRemittance);
    }

    public function destroy(CashRemittance $cashRemittance): JsonResponse
    {
        $cashRemittance->delete();

        return response()->json(['message' => 'Cash remittance deleted successfully.']);
    }
}
