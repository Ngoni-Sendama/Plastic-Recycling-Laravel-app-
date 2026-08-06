<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreMaterialIntakeRequest;
use App\Http\Requests\Api\UpdateMaterialIntakeRequest;
use App\Http\Resources\MaterialIntakeResource;
use App\Models\Buyer;
use App\Models\MaterialIntake;
use App\Services\MaterialIntakeCalculator;
use Illuminate\Http\JsonResponse;
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

    public function store(StoreMaterialIntakeRequest $request): JsonResponse
    {
        $data = $request->validated();

        $deleted = MaterialIntake::withTrashed()->where('grn_number', $data['grn_number'] ?? null)->first();
        if ($deleted && $deleted->trashed()) {
            $calculated = MaterialIntakeCalculator::calculate($data);
            $buyerId = $data['buyer_id'] ?? null;
            $deleted->restore();
            $deleted->update([
                'date' => $data['date'],
                'buyer_id' => $buyerId,
                'buyer_name' => $buyerId ? $this->resolveBuyerName($buyerId) : ($data['buyer_name'] ?? null),
                'material_id' => $this->resolveMaterialId($data),
                'gross_weight_kg' => $data['gross_weight_kg'],
                'tare_weight_kg' => $data['tare_weight_kg'],
                'unit_price' => $data['unit_price'],
                'net_weight_kg' => $calculated['net_weight_kg'],
                'total_value' => $calculated['total_value'],
            ]);
            $deleted->load(['buyer', 'material', 'recordedByUser']);

            return response()->json([
                'message' => 'This record was previously deleted and has been restored.',
                'data' => new MaterialIntakeResource($deleted),
                'restored' => true,
            ], 201);
        }

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
        $intake->load(['buyer', 'material', 'recordedByUser']);

        return response()->json([
            'message' => 'Record created successfully.',
            'data' => new MaterialIntakeResource($intake),
            'restored' => false,
        ], 201);
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
        $materialIntake->load(['buyer', 'material', 'recordedByUser']);

        return new MaterialIntakeResource($materialIntake);
    }

    public function destroy(MaterialIntake $materialIntake): JsonResponse
    {
        $materialIntake->delete();

        return response()->json(['message' => 'Material intake deleted successfully.']);
    }

    public function restore(MaterialIntake $materialIntake): JsonResponse
    {
        $materialIntake->restore();

        return response()->json(['message' => 'Record restored successfully.']);
    }

    public function trashed(): AnonymousResourceCollection
    {
        return MaterialIntakeResource::collection(
            MaterialIntake::onlyTrashed()->with(['buyer', 'material', 'recordedByUser'])->latest('date')->get(),
        );
    }

    public function forceDelete(MaterialIntake $materialIntake): JsonResponse
    {
        $materialIntake->forceDelete();

        return response()->json(['message' => 'Record permanently deleted.']);
    }

    private function resolveBuyerName(int $buyerId): string
    {
        return Buyer::query()->findOrFail($buyerId)->buyer_name;
    }
}
