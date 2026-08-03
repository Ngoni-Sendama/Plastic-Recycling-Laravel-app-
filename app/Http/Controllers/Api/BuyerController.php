<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreBuyerRequest;
use App\Http\Requests\Api\UpdateBuyerRequest;
use App\Http\Resources\BuyerResource;
use App\Models\Buyer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BuyerController extends ApiController
{
    public function index(): AnonymousResourceCollection
    {
        return BuyerResource::collection(Buyer::query()->orderBy('buyer_name')->get());
    }

    public function show(Buyer $buyer): BuyerResource
    {
        return new BuyerResource($buyer);
    }

    public function store(StoreBuyerRequest $request): JsonResponse
    {
        $data = $request->validated();

        $deleted = Buyer::withTrashed()->where('buyer_name', $data['buyer_name'] ?? null)->first();
        if ($deleted && $deleted->trashed()) {
            $deleted->restore();
            $deleted->update($data);

            return response()->json([
                'message' => 'This record was previously deleted and has been restored.',
                'data' => new BuyerResource($deleted),
                'restored' => true,
            ], 201);
        }

        $record = Buyer::create($data);

        return response()->json([
            'message' => 'Record created successfully.',
            'data' => new BuyerResource($record),
            'restored' => false,
        ], 201);
    }

    public function update(UpdateBuyerRequest $request, Buyer $buyer): BuyerResource
    {
        $buyer->update([
            ...$request->validated(),
            'lock_version' => (int) $buyer->lock_version + 1,
        ]);

        return new BuyerResource($buyer->refresh());
    }

    public function destroy(Buyer $buyer): JsonResponse
    {
        if ($buyer->materialIntakes()->exists()) {
            return response()->json([
                'message' => 'Cannot delete buyer with existing material intake records.',
            ], 422);
        }

        $buyer->delete();

        return response()->json(['message' => 'Buyer deleted successfully.']);
    }

    public function restore(Buyer $buyer): JsonResponse
    {
        $buyer->restore();

        return response()->json(['message' => 'Record restored successfully.']);
    }

    public function trashed(): AnonymousResourceCollection
    {
        return BuyerResource::collection(Buyer::onlyTrashed()->latest('date')->get());
    }

    public function forceDelete(Buyer $buyer): JsonResponse
    {
        $buyer->forceDelete();

        return response()->json(['message' => 'Record permanently deleted.']);
    }
}
