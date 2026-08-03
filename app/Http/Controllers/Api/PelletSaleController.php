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

    public function store(StorePelletSaleRequest $request): PelletSaleResource
    {
        $data = $request->validated();
        $calculated = PelletSaleCalculator::calculate($data);

        $sale = PelletSale::create([
            'date' => $data['date'],
            'customer_name' => $data['customer_name'],
            'kg_sold' => $data['kg_sold'],
            'unit_price' => $data['unit_price'],
            'amount_received' => $calculated['amount_received'],
            'recorded_by_user_id' => $request->user()->id,
        ]);

        return new PelletSaleResource($sale);
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

    public function pdf(PelletSale $pelletSale): Response
    {
        $pelletSale->load(['recordedByUser']);

        $pdf = Pdf::loadView('pdf.sale-receipt', ['sale' => $pelletSale]);

        return $pdf->download("sale-receipt-{$pelletSale->receipt_number}.pdf");
    }
}
