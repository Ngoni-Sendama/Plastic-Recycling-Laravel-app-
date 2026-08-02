<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreMaterialIntakeRequest;
use App\Http\Requests\Api\UpdateMaterialIntakeRequest;
use App\Http\Resources\MaterialIntakeResource;
use App\Models\Buyer;
use App\Models\MaterialIntake;
use App\Services\MaterialIntakeCalculator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MaterialIntakeController extends ApiController
{
    public function index(): AnonymousResourceCollection
    {
        return MaterialIntakeResource::collection(
            MaterialIntake::with(['buyer', 'material', 'recordedByUser'])->latest('date')->get(),
        );
    }

    public function show(MaterialIntake $materialIntake): MaterialIntakeResource
    {
        $materialIntake->load(['buyer', 'material', 'recordedByUser']);

        return new MaterialIntakeResource($materialIntake);
    }

    public function store(StoreMaterialIntakeRequest $request): MaterialIntakeResource
    {
        $data = $request->validated();
        $calculated = MaterialIntakeCalculator::calculate($data);
        $buyerId = $data['buyer_id'] ?? null;

        $intake = MaterialIntake::create([
            'date' => $data['date'],
            'buyer_id' => $buyerId,
            'buyer_name' => $buyerId ? $this->resolveBuyerName($buyerId) : ($data['buyer_name'] ?? null),
            'material_id' => $this->resolveMaterialId($data),
            'gross_weight_kg' => $data['gross_weight_kg'],
            'tare_weight_kg' => $data['tare_weight_kg'],
            'unit_price' => $data['unit_price'],
            'net_weight_kg' => $calculated['net_weight_kg'],
            'total_value' => $calculated['total_value'],
            'recorded_by_user_id' => $request->user()->id,
        ]);

        return new MaterialIntakeResource($intake);
    }

    public function update(UpdateMaterialIntakeRequest $request, MaterialIntake $materialIntake): MaterialIntakeResource
    {
        $data = $request->validated();
        $calculated = MaterialIntakeCalculator::calculate($data);
        $buyerId = $data['buyer_id'] ?? null;

        $materialIntake->update([
            'date' => $data['date'],
            'buyer_id' => $buyerId,
            'buyer_name' => $buyerId ? $this->resolveBuyerName($buyerId) : ($data['buyer_name'] ?? null),
            'material_id' => $this->resolveMaterialId($data),
            'gross_weight_kg' => $data['gross_weight_kg'],
            'tare_weight_kg' => $data['tare_weight_kg'],
            'net_weight_kg' => $calculated['net_weight_kg'],
            'unit_price' => $data['unit_price'],
            'total_value' => $calculated['total_value'],
        ]);

        return new MaterialIntakeResource($materialIntake);
    }

    private function resolveBuyerName(int $buyerId): string
    {
        return Buyer::query()->findOrFail($buyerId)->buyer_name;
    }
}
