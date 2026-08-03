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

    public function store(StoreMaterialRequest $request): MaterialResource
    {
        $material = Material::create($request->validated());

        return new MaterialResource($material);
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

    public function restore(Material $material): JsonResponse
    {
        $material->restore();

        return response()->json(['message' => 'Material restored successfully.']);
    }
}
