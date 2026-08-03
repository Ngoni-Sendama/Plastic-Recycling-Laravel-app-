<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreMaterialRequest;
use App\Http\Requests\Api\UpdateMaterialRequest;
use App\Http\Resources\MaterialResource;
use App\Models\Material;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MaterialController extends ApiController
{
    public function index(): AnonymousResourceCollection
    {
        return MaterialResource::collection(Material::orderBy('code')->get());
    }

    public function trashed(): AnonymousResourceCollection
    {
        return MaterialResource::collection(Material::onlyTrashed()->orderBy('code')->get());
    }

    public function show(Material $material): MaterialResource
    {
        return new MaterialResource($material);
    }

    public function store(StoreMaterialRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Check if a soft-deleted record with this code exists
        $deleted = Material::withTrashed()->where('code', $data['code'])->first();
        if ($deleted && $deleted->trashed()) {
            $deleted->restore();
            $deleted->update($data);

            return response()->json([
                'message' => 'This code was previously deleted and has been restored.',
                'data' => new MaterialResource($deleted),
                'restored' => true,
            ]);
        }

        $material = Material::create($data);

        return response()->json([
            'message' => 'Material created successfully.',
            'data' => new MaterialResource($material),
            'restored' => false,
        ]);
    }

    public function update(UpdateMaterialRequest $request, Material $material): MaterialResource
    {
        $material->update($request->validated());

        return new MaterialResource($material);
    }

    public function destroy(Material $material): JsonResponse
    {
        $material->delete();

        return response()->json(['message' => 'Material deleted successfully.']);
    }

    public function forceDelete(Material $material): JsonResponse
    {
        $material->forceDelete();

        return response()->json(['message' => 'Material permanently deleted.']);
    }

    public function restore(Material $material): JsonResponse
    {
        $material->restore();

        return response()->json(['message' => 'Material restored successfully.']);
    }
}
