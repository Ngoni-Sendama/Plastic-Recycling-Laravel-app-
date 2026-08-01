<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreCrushingProductionRequest;
use App\Http\Resources\CrushingProductionResource;
use App\Models\CrushingProduction;
use App\Services\CrushingProductionCalculator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CrushingProductionController extends ApiController
{
    public function index(): AnonymousResourceCollection
    {
        return CrushingProductionResource::collection(
            CrushingProduction::with(['material', 'recordedByUser'])->latest('date')->get(),
        );
    }

    public function store(StoreCrushingProductionRequest $request): CrushingProductionResource
    {
        $data = $request->validated();
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

        return new CrushingProductionResource($production);
    }
}
