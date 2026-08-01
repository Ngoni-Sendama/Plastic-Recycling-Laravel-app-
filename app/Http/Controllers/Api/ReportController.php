<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportSummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function stock(Request $request): JsonResponse
    {
        return $this->report(
            $request,
            fn (ReportSummaryService $service, ?Carbon $from, ?Carbon $to): array => $service->stockSummary($from, $to)
        );
    }

    public function production(Request $request): JsonResponse
    {
        return $this->report(
            $request,
            fn (ReportSummaryService $service, ?Carbon $from, ?Carbon $to): array => $service->productionSummary($from, $to)
        );
    }

    public function sales(Request $request): JsonResponse
    {
        return $this->report(
            $request,
            fn (ReportSummaryService $service, ?Carbon $from, ?Carbon $to): array => $service->salesSummary($from, $to)
        );
    }

    public function cashReconciliation(Request $request): JsonResponse
    {
        return $this->report(
            $request,
            fn (ReportSummaryService $service, ?Carbon $from, ?Carbon $to): array => $service->cashReconciliation($from, $to)
        );
    }

    /**
     * @param  callable(ReportSummaryService, ?Carbon, ?Carbon): array  $resolver
     */
    private function report(Request $request, callable $resolver): JsonResponse
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $from = $request->filled('from') ? Carbon::parse($request->string('from')->toString()) : null;
        $to = $request->filled('to') ? Carbon::parse($request->string('to')->toString()) : null;

        return response()->json([
            'data' => $resolver(app(ReportSummaryService::class), $from, $to),
        ]);
    }
}
