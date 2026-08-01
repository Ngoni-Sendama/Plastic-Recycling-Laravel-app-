<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StorePelletSaleRequest;
use App\Http\Resources\PelletSaleResource;
use App\Models\PelletSale;
use App\Services\PelletSaleCalculator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PelletSaleController extends ApiController
{
    public function index(): AnonymousResourceCollection
    {
        return PelletSaleResource::collection(
            PelletSale::with(['recordedByUser'])->latest('date')->get(),
        );
    }

    public function store(StorePelletSaleRequest $request): PelletSaleResource
    {
        $data = $request->validated();
        $calculated = PelletSaleCalculator::calculate($data);

        $sale = PelletSale::create([
            'date' => $data['date'],
            'receipt_number' => $data['receipt_number'],
            'customer_name' => $data['customer_name'],
            'kg_sold' => $data['kg_sold'],
            'unit_price' => $data['unit_price'],
            'amount_received' => $calculated['amount_received'],
            'recorded_by_user_id' => $request->user()->id,
        ]);

        return new PelletSaleResource($sale);
    }
}
