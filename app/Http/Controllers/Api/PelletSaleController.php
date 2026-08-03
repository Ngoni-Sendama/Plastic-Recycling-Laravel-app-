<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StorePelletSaleRequest;
use App\Http\Resources\PelletSaleResource;
use App\Models\PelletSale;
use App\Services\PelletSaleCalculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class PelletSaleController extends ApiController
{
    public function index(): AnonymousResourceCollection
    {
        return PelletSaleResource::collection(
            PelletSale::with(['recordedByUser'])->latest('date')->get(),
        );
    }

    public function show(PelletSale $pelletSale): PelletSaleResource
    {
        $pelletSale->load(['recordedByUser']);

        return new PelletSaleResource($pelletSale);
    }

    public function store(StorePelletSaleRequest $request): JsonResponse
    {
        $data = $request->validated();

        $deleted = PelletSale::withTrashed()->where('receipt_number', $data['receipt_number'] ?? null)->first();
        if ($deleted && $deleted->trashed()) {
            $calculated = PelletSaleCalculator::calculate($data);
            $deleted->restore();
            $deleted->update([
                'date' => $data['date'],
                'customer_name' => $data['customer_name'],
                'kg_sold' => $data['kg_sold'],
                'unit_price' => $data['unit_price'],
                'amount_received' => $calculated['amount_received'],
            ]);

            return response()->json([
                'message' => 'This record was previously deleted and has been restored.',
                'data' => new PelletSaleResource($deleted),
                'restored' => true,
            ], 201);
        }

        $calculated = PelletSaleCalculator::calculate($data);

        $sale = PelletSale::create([
            'date' => $data['date'],
            'customer_name' => $data['customer_name'],
            'kg_sold' => $data['kg_sold'],
            'unit_price' => $data['unit_price'],
            'amount_received' => $calculated['amount_received'],
            'recorded_by_user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Record created successfully.',
            'data' => new PelletSaleResource($sale),
            'restored' => false,
        ], 201);
    }

    public function update(StorePelletSaleRequest $request, PelletSale $pelletSale): PelletSaleResource
    {
        $data = $request->validated();
        $calculated = PelletSaleCalculator::calculate($data);

        $pelletSale->update([
            'date' => $data['date'],
            'customer_name' => $data['customer_name'],
            'kg_sold' => $data['kg_sold'],
            'unit_price' => $data['unit_price'],
            'amount_received' => $calculated['amount_received'],
        ]);

        return new PelletSaleResource($pelletSale);
    }

    public function destroy(PelletSale $pelletSale): JsonResponse
    {
        $pelletSale->delete();

        return response()->json(['message' => 'Pellet sale deleted successfully.']);
    }

    public function restore(PelletSale $pelletSale): JsonResponse
    {
        $pelletSale->restore();

        return response()->json(['message' => 'Record restored successfully.']);
    }

    public function trashed(): AnonymousResourceCollection
    {
        return PelletSaleResource::collection(PelletSale::onlyTrashed()->latest('date')->get());
    }

    public function forceDelete(PelletSale $pelletSale): JsonResponse
    {
        $pelletSale->forceDelete();

        return response()->json(['message' => 'Record permanently deleted.']);
    }

    public function pdf(PelletSale $pelletSale): Response
    {
        $pelletSale->load(['recordedByUser']);

        $pdf = Pdf::loadView('pdf.sale-receipt', ['sale' => $pelletSale]);

        return $pdf->download("sale-receipt-{$pelletSale->receipt_number}.pdf");
    }
}
