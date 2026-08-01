<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreMaterialIntakeRequest;
use App\Http\Resources\MaterialIntakeResource;
use App\Models\MaterialIntake;
use App\Services\MaterialIntakeCalculator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MaterialIntakeController extends ApiController
{
    public function index(): AnonymousResourceCollection
    {
        return MaterialIntakeResource::collection(
            MaterialIntake::with(['material', 'recordedByUser'])->latest('date')->get(),
        );
    }

    public function store(StoreMaterialIntakeRequest $request): MaterialIntakeResource
    {
        $data = $request->validated();
        $calculated = MaterialIntakeCalculator::calculate($data);

        $intake = MaterialIntake::create([
            'date' => $data['date'],
            'grn_number' => $data['grn_number'],
            'buyer_name' => $data['buyer_name'],
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
}
