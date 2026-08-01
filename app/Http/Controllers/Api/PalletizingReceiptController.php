<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StorePalletizingReceiptRequest;
use App\Http\Resources\PalletizingReceiptResource;
use App\Models\PalletizingReceipt;
use App\Services\PalletizingReceiptCalculator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PalletizingReceiptController extends ApiController
{
    public function index(): AnonymousResourceCollection
    {
        return PalletizingReceiptResource::collection(
            PalletizingReceipt::with(['material', 'recordedByUser'])->latest('date')->get(),
        );
    }

    public function store(StorePalletizingReceiptRequest $request): PalletizingReceiptResource
    {
        $data = $request->validated();
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

        return new PalletizingReceiptResource($receipt);
    }
}
