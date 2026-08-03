<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StorePalletizingReceiptRequest;
use App\Http\Resources\PalletizingReceiptResource;
use App\Models\PalletizingReceipt;
use App\Services\PalletizingReceiptCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PalletizingReceiptController extends ApiController
{
    public function index(): AnonymousResourceCollection
    {
        return PalletizingReceiptResource::collection(
            PalletizingReceipt::with(['material', 'recordedByUser'])->latest('date')->get(),
        );
    }

    public function show(PalletizingReceipt $palletizingReceipt): PalletizingReceiptResource
    {
        $palletizingReceipt->load(['material', 'recordedByUser']);

        return new PalletizingReceiptResource($palletizingReceipt);
    }

    public function store(StorePalletizingReceiptRequest $request): JsonResponse
    {
        $data = $request->validated();

        $deleted = PalletizingReceipt::withTrashed()->where('grn_number', $data['grn_number'] ?? null)->first();
        if ($deleted && $deleted->trashed()) {
            $calculated = PalletizingReceiptCalculator::calculate($data);
            $deleted->restore();
            $deleted->update([
                'date' => $data['date'],
                'dispatch_id' => $data['dispatch_id'] ?? null,
                'dispatch_reference' => $data['dispatch_reference'] ?? null,
                'material_id' => $this->resolveMaterialId($data),
                'weight_received_kg' => $data['weight_received_kg'],
                'rate_per_kg' => $data['rate_per_kg'],
                'amount_payable' => $calculated['amount_payable'],
            ]);

            return response()->json([
                'message' => 'This record was previously deleted and has been restored.',
                'data' => new PalletizingReceiptResource($deleted),
                'restored' => true,
            ], 201);
        }

        $calculated = PalletizingReceiptCalculator::calculate($data);

        $receipt = PalletizingReceipt::create([
            'date' => $data['date'],
            'dispatch_id' => $data['dispatch_id'] ?? null,
            'dispatch_reference' => $data['dispatch_reference'] ?? null,
            'material_id' => $this->resolveMaterialId($data),
            'weight_received_kg' => $data['weight_received_kg'],
            'rate_per_kg' => $data['rate_per_kg'],
            'amount_payable' => $calculated['amount_payable'],
            'recorded_by_user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Record created successfully.',
            'data' => new PalletizingReceiptResource($receipt),
            'restored' => false,
        ], 201);
    }

    public function update(StorePalletizingReceiptRequest $request, PalletizingReceipt $palletizingReceipt): PalletizingReceiptResource
    {
        $data = $request->validated();
        $calculated = PalletizingReceiptCalculator::calculate($data);

        $palletizingReceipt->update([
            'date' => $data['date'],
            'dispatch_id' => $data['dispatch_id'] ?? null,
            'dispatch_reference' => $data['dispatch_reference'] ?? null,
            'material_id' => $this->resolveMaterialId($data),
            'weight_received_kg' => $data['weight_received_kg'],
            'rate_per_kg' => $data['rate_per_kg'],
            'amount_payable' => $calculated['amount_payable'],
        ]);

        return new PalletizingReceiptResource($palletizingReceipt);
    }

    public function destroy(PalletizingReceipt $palletizingReceipt): JsonResponse
    {
        $palletizingReceipt->delete();

        return response()->json(['message' => 'Palletizing receipt deleted successfully.']);
    }

    public function restore(PalletizingReceipt $palletizingReceipt): JsonResponse
    {
        $palletizingReceipt->restore();

        return response()->json(['message' => 'Record restored successfully.']);
    }

    public function trashed(): AnonymousResourceCollection
    {
        return PalletizingReceiptResource::collection(PalletizingReceipt::onlyTrashed()->latest('date')->get());
    }

    public function forceDelete(PalletizingReceipt $palletizingReceipt): JsonResponse
    {
        $palletizingReceipt->forceDelete();

        return response()->json(['message' => 'Record permanently deleted.']);
    }
}
