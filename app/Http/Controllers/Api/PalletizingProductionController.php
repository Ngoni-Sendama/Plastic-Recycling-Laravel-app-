<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StorePalletizingProductionRequest;
use App\Http\Resources\PalletizingProductionResource;
use App\Models\PalletizingProduction;
use App\Services\PalletizingProductionCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PalletizingProductionController extends ApiController
{
    public function index(): AnonymousResourceCollection
    {
        return PalletizingProductionResource::collection(
            PalletizingProduction::with(['recordedByUser'])->latest('date')->get(),
        );
    }

    public function store(StorePalletizingProductionRequest $request): PalletizingProductionResource
    {
        $data = $request->validated();
        $calculated = PalletizingProductionCalculator::calculate($data);

        $production = PalletizingProduction::create([
            'date' => $data['date'],
            'palletizing_receipt_id' => $data['palletizing_receipt_id'] ?? null,
            'grn_reference' => $data['grn_reference'] ?? null,
            'chips_input_kg' => $data['chips_input_kg'],
            'pellets_output_kg' => $data['pellets_output_kg'],
            'loss_kg' => $calculated['loss_kg'],
            'loss_percentage' => $calculated['loss_percentage'],
            'recorded_by_user_id' => $request->user()->id,
        ]);

        return new PalletizingProductionResource($production);
    }

    public function update(StorePalletizingProductionRequest $request, PalletizingProduction $palletizingProduction): PalletizingProductionResource
    {
        $data = $request->validated();
        $calculated = PalletizingProductionCalculator::calculate($data);

        $palletizingProduction->update([
            'date' => $data['date'],
            'palletizing_receipt_id' => $data['palletizing_receipt_id'] ?? null,
            'grn_reference' => $data['grn_reference'] ?? null,
            'chips_input_kg' => $data['chips_input_kg'],
            'pellets_output_kg' => $data['pellets_output_kg'],
            'loss_kg' => $calculated['loss_kg'],
            'loss_percentage' => $calculated['loss_percentage'],
        ]);

        return new PalletizingProductionResource($palletizingProduction);
    }

    public function destroy(PalletizingProduction $palletizingProduction): JsonResponse
    {
        $palletizingProduction->delete();

        return response()->json(['message' => 'Palletizing production deleted successfully.']);
    }
}
