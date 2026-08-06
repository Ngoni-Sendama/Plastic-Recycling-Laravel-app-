<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreCrushingProductionRequest;
use App\Http\Requests\Api\StoreCrushingProductionRequest as UpdateCrushingProductionRequest;
use App\Http\Resources\CrushingProductionResource;
use App\Models\CrushingProduction;
use App\Services\CrushingProductionCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CrushingProductionController extends ApiController
{
    public function index(): AnonymousResourceCollection
    {
        return CrushingProductionResource::collection(
            CrushingProduction::with(['material', 'recordedByUser'])->latest('date')->get(),
        );
    }

    public function show(CrushingProduction $crushingProduction): CrushingProductionResource
    {
        $crushingProduction->load(['material', 'recordedByUser']);

        return new CrushingProductionResource($crushingProduction);
    }

    public function store(StoreCrushingProductionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $deleted = CrushingProduction::withTrashed()->where('batch_number', $data['batch_number'] ?? null)->first();
        if ($deleted && $deleted->trashed()) {
            $calculated = CrushingProductionCalculator::calculate($data);
            $deleted->restore();
            $deleted->update([
                'date' => $data['date'],
                'material_intake_id' => $data['material_intake_id'] ?? null,
                'grn_reference' => $data['grn_reference'] ?? null,
                'material_id' => $this->resolveMaterialId($data),
                'input_weight_kg' => $data['input_weight_kg'],
                'output_chips_kg' => $data['output_chips_kg'],
                'loss_kg' => $calculated['loss_kg'],
                'loss_percentage' => $calculated['loss_percentage'],
            ]);
            $deleted->load(['material', 'recordedByUser']);

            return response()->json([
                'message' => 'This record was previously deleted and has been restored.',
                'data' => new CrushingProductionResource($deleted),
                'restored' => true,
            ], 201);
        }

        $calculated = CrushingProductionCalculator::calculate($data);

        $production = CrushingProduction::create([
            'date' => $data['date'],
            'material_intake_id' => $data['material_intake_id'] ?? null,
            'grn_reference' => $data['grn_reference'] ?? null,
            'material_id' => $this->resolveMaterialId($data),
            'input_weight_kg' => $data['input_weight_kg'],
            'output_chips_kg' => $data['output_chips_kg'],
            'loss_kg' => $calculated['loss_kg'],
            'loss_percentage' => $calculated['loss_percentage'],
            'recorded_by_user_id' => $request->user()->id,
        ]);
        $production->load(['material', 'recordedByUser']);

        return response()->json([
            'message' => 'Record created successfully.',
            'data' => new CrushingProductionResource($production),
            'restored' => false,
        ], 201);
    }

    public function update(UpdateCrushingProductionRequest $request, CrushingProduction $crushingProduction): CrushingProductionResource
    {
        $data = $request->validated();
        $calculated = CrushingProductionCalculator::calculate($data);

        $crushingProduction->update([
            'date' => $data['date'],
            'material_id' => $data['material_id'] ?? $crushingProduction->material_id,
            'grn_reference' => $data['grn_reference'] ?? null,
            'input_weight_kg' => $data['input_weight_kg'],
            'output_chips_kg' => $data['output_chips_kg'],
            'loss_kg' => $calculated['loss_kg'],
            'loss_percentage' => $calculated['loss_percentage'],
        ]);
        $crushingProduction->load(['material', 'recordedByUser']);

        return new CrushingProductionResource($crushingProduction);
    }

    public function destroy(CrushingProduction $crushingProduction): JsonResponse
    {
        $crushingProduction->delete();

        return response()->json(['message' => 'Crushing production deleted successfully.']);
    }

    public function restore(CrushingProduction $crushingProduction): JsonResponse
    {
        $crushingProduction->restore();

        return response()->json(['message' => 'Record restored successfully.']);
    }

    public function trashed(): AnonymousResourceCollection
    {
        return CrushingProductionResource::collection(
            CrushingProduction::onlyTrashed()->with(['material', 'recordedByUser'])->latest('date')->get(),
        );
    }

    public function forceDelete(CrushingProduction $crushingProduction): JsonResponse
    {
        $crushingProduction->forceDelete();

        return response()->json(['message' => 'Record permanently deleted.']);
    }
}
