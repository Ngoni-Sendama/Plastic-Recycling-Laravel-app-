<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreDispatchRequest;
use App\Http\Resources\DispatchResource;
use App\Models\Dispatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DispatchController extends ApiController
{
    public function index(): AnonymousResourceCollection
    {
        return DispatchResource::collection(
            Dispatch::with(['material', 'recordedByUser'])->latest('date')->get(),
        );
    }

    public function show(Dispatch $dispatch): DispatchResource
    {
        $dispatch->load(['material', 'recordedByUser']);

        return new DispatchResource($dispatch);
    }

    public function store(StoreDispatchRequest $request): JsonResponse
    {
        $data = $request->validated();

        $deleted = Dispatch::withTrashed()->where('dispatch_note_number', $data['dispatch_note_number'] ?? null)->first();
        if ($deleted && $deleted->trashed()) {
            $deleted->restore();
            $deleted->update([
                'date' => $data['date'],
                'crushing_production_id' => $data['crushing_production_id'] ?? null,
                'batch_reference' => $data['batch_reference'] ?? null,
                'material_id' => $this->resolveMaterialId($data),
                'weight_dispatched_kg' => $data['weight_dispatched_kg'],
                'transported_by' => $data['transported_by'] ?? null,
            ]);
            $deleted->load(['material', 'recordedByUser']);

            return response()->json([
                'message' => 'This record was previously deleted and has been restored.',
                'data' => new DispatchResource($deleted),
                'restored' => true,
            ], 201);
        }

        $dispatch = Dispatch::create([
            'date' => $data['date'],
            'crushing_production_id' => $data['crushing_production_id'] ?? null,
            'batch_reference' => $data['batch_reference'] ?? null,
            'material_id' => $this->resolveMaterialId($data),
            'weight_dispatched_kg' => $data['weight_dispatched_kg'],
            'transported_by' => $data['transported_by'] ?? null,
            'recorded_by_user_id' => $request->user()->id,
        ]);
        $dispatch->load(['material', 'recordedByUser']);

        return response()->json([
            'message' => 'Record created successfully.',
            'data' => new DispatchResource($dispatch),
            'restored' => false,
        ], 201);
    }

    public function update(StoreDispatchRequest $request, Dispatch $dispatch): DispatchResource
    {
        $data = $request->validated();

        $dispatch->update([
            'date' => $data['date'],
            'crushing_production_id' => $data['crushing_production_id'] ?? null,
            'batch_reference' => $data['batch_reference'] ?? null,
            'material_id' => $this->resolveMaterialId($data),
            'weight_dispatched_kg' => $data['weight_dispatched_kg'],
            'transported_by' => $data['transported_by'] ?? null,
        ]);
        $dispatch->load(['material', 'recordedByUser']);

        return new DispatchResource($dispatch);
    }

    public function destroy(Dispatch $dispatch): JsonResponse
    {
        $dispatch->delete();

        return response()->json(['message' => 'Dispatch deleted successfully.']);
    }

    public function restore(Dispatch $dispatch): JsonResponse
    {
        $dispatch->restore();

        return response()->json(['message' => 'Record restored successfully.']);
    }

    public function trashed(): AnonymousResourceCollection
    {
        return DispatchResource::collection(
            Dispatch::onlyTrashed()->with(['material', 'recordedByUser'])->latest('date')->get(),
        );
    }

    public function forceDelete(Dispatch $dispatch): JsonResponse
    {
        $dispatch->forceDelete();

        return response()->json(['message' => 'Record permanently deleted.']);
    }
}
