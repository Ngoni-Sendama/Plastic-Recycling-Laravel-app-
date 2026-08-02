<?php

namespace App\Services;

use App\Models\CashRemittance;
use App\Models\CrushingProduction;
use App\Models\Dispatch;
use App\Models\MaterialIntake;
use App\Models\PalletizingProduction;
use App\Models\PalletizingReceipt;
use App\Models\PelletSale;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use RuntimeException;

class StockCashControlWorkbookExporter
{
    public function __construct(private readonly DashboardSummaryService $summaryService) {}

    public function export(?Carbon $from = null, ?Carbon $to = null): string
    {
        $templatePath = base_path('Highglen_Plastic_Industries_Stock_Cash_Control.xlsx');

        if (! is_file($templatePath)) {
            throw new RuntimeException('Workbook template not found.');
        }

        /** @var Spreadsheet $spreadsheet */
        $spreadsheet = IOFactory::load($templatePath);

        $this->fillMaterialIntakeSheet($spreadsheet->getSheetByName('1. Material Intake'), $from, $to);
        $this->fillCrushingProductionSheet($spreadsheet->getSheetByName('2. Crushing Production'), $from, $to);
        $this->fillDispatchSheet($spreadsheet->getSheetByName('3. Dispatch to Palletizing'), $from, $to);
        $this->fillPalletizingReceiptSheet($spreadsheet->getSheetByName('4. Palletizing Receipt'), $from, $to);
        $this->fillPalletizingProductionSheet($spreadsheet->getSheetByName('5. Palletizing Production'), $from, $to);
        $this->fillPelletSalesSheet($spreadsheet->getSheetByName('6. Pellet Sales'), $from, $to);
        $this->fillCashRemittanceSheet($spreadsheet->getSheetByName('7. Cash Remittance'), $from, $to);
        $this->fillDashboardSheet($spreadsheet->getSheetByName('Dashboard'), $from, $to);

        $outputPath = tempnam(sys_get_temp_dir(), 'highglen_stock_cash_');
        if ($outputPath === false) {
            throw new RuntimeException('Unable to create export file.');
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($outputPath);

        return $outputPath;
    }

    private function fillMaterialIntakeSheet(?Worksheet $sheet, ?Carbon $from, ?Carbon $to): void
    {
        if (! $sheet) {
            return;
        }

        $rows = MaterialIntake::query()
            ->with(['buyer', 'recordedByUser', 'material'])
            ->when($from, fn ($query) => $query->whereDate('date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('date', '<=', $to))
            ->orderBy('date')
            ->get()
            ->map(fn (MaterialIntake $row): array => [
                $row->date?->toDateString(),
                $row->grn_number,
                $row->buyer?->buyer_name ?? $row->buyer_name,
                $row->material?->code ?? $row->material_id,
                (float) $row->gross_weight_kg,
                (float) $row->tare_weight_kg,
                (float) $row->net_weight_kg,
                (float) $row->unit_price,
                (float) $row->total_value,
                $row->recordedByUser?->name,
            ])
            ->all();

        $this->replaceDataRows($sheet, 4, $rows);
    }

    private function fillCrushingProductionSheet(?Worksheet $sheet, ?Carbon $from, ?Carbon $to): void
    {
        if (! $sheet) {
            return;
        }

        $rows = CrushingProduction::query()
            ->with(['material', 'recordedByUser'])
            ->when($from, fn ($query) => $query->whereDate('date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('date', '<=', $to))
            ->orderBy('date')
            ->get()
            ->map(fn (CrushingProduction $row): array => [
                $row->date?->toDateString(),
                $row->batch_number,
                $row->grn_reference,
                $row->material?->code ?? $row->material_id,
                (float) $row->input_weight_kg,
                (float) $row->output_chips_kg,
                (float) $row->loss_kg,
                (float) $row->loss_percentage,
                $row->recordedByUser?->name,
            ])
            ->all();

        $this->replaceDataRows($sheet, 4, $rows);
    }

    private function fillDispatchSheet(?Worksheet $sheet, ?Carbon $from, ?Carbon $to): void
    {
        if (! $sheet) {
            return;
        }

        $rows = Dispatch::query()
            ->with(['material', 'recordedByUser'])
            ->when($from, fn ($query) => $query->whereDate('date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('date', '<=', $to))
            ->orderBy('date')
            ->get()
            ->map(fn (Dispatch $row): array => [
                $row->date?->toDateString(),
                $row->dispatch_note_number,
                $row->batch_reference,
                $row->material?->code ?? $row->material_id,
                (float) $row->weight_dispatched_kg,
                $row->transported_by,
                $row->recordedByUser?->name,
            ])
            ->all();

        $this->replaceDataRows($sheet, 4, $rows);
    }

    private function fillPalletizingReceiptSheet(?Worksheet $sheet, ?Carbon $from, ?Carbon $to): void
    {
        if (! $sheet) {
            return;
        }

        $rows = PalletizingReceipt::query()
            ->with(['material', 'recordedByUser'])
            ->when($from, fn ($query) => $query->whereDate('date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('date', '<=', $to))
            ->orderBy('date')
            ->get()
            ->map(fn (PalletizingReceipt $row): array => [
                $row->date?->toDateString(),
                $row->grn_number,
                $row->dispatch_reference,
                $row->material?->code ?? $row->material_id,
                (float) $row->weight_received_kg,
                (float) $row->rate_per_kg,
                (float) $row->amount_payable,
                $row->recordedByUser?->name,
            ])
            ->all();

        $this->replaceDataRows($sheet, 4, $rows);
    }

    private function fillPalletizingProductionSheet(?Worksheet $sheet, ?Carbon $from, ?Carbon $to): void
    {
        if (! $sheet) {
            return;
        }

        $rows = PalletizingProduction::query()
            ->with(['recordedByUser'])
            ->when($from, fn ($query) => $query->whereDate('date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('date', '<=', $to))
            ->orderBy('date')
            ->get()
            ->map(fn (PalletizingProduction $row): array => [
                $row->date?->toDateString(),
                $row->batch_number,
                $row->grn_reference,
                (float) $row->chips_input_kg,
                (float) $row->pellets_output_kg,
                (float) $row->loss_kg,
                (float) $row->loss_percentage,
                $row->recordedByUser?->name,
            ])
            ->all();

        $this->replaceDataRows($sheet, 4, $rows);
    }

    private function fillPelletSalesSheet(?Worksheet $sheet, ?Carbon $from, ?Carbon $to): void
    {
        if (! $sheet) {
            return;
        }

        $rows = PelletSale::query()
            ->with(['recordedByUser'])
            ->when($from, fn ($query) => $query->whereDate('date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('date', '<=', $to))
            ->orderBy('date')
            ->get()
            ->map(fn (PelletSale $row): array => [
                $row->date?->toDateString(),
                $row->receipt_number,
                $row->customer_name,
                (float) $row->kg_sold,
                (float) $row->unit_price,
                (float) $row->amount_received,
                $row->recordedByUser?->name,
            ])
            ->all();

        $this->replaceDataRows($sheet, 4, $rows);
    }

    private function fillCashRemittanceSheet(?Worksheet $sheet, ?Carbon $from, ?Carbon $to): void
    {
        if (! $sheet) {
            return;
        }

        $rows = CashRemittance::query()
            ->with(['recordedByUser'])
            ->when($from, fn ($query) => $query->whereDate('date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('date', '<=', $to))
            ->orderBy('date')
            ->get()
            ->map(fn (CashRemittance $row): array => [
                $row->date?->toDateString(),
                $row->voucher_number,
                $row->period_covered,
                (float) $row->chips_delivered_kg,
                (float) $row->recovery_price_per_kg,
                (float) $row->max_remittance_due,
                (float) $row->sales_revenue,
                (float) $row->cash_remitted,
                (float) $row->balance_retained,
            ])
            ->all();

        $this->replaceDataRows($sheet, 4, $rows);
    }

    private function fillDashboardSheet(?Worksheet $sheet, ?Carbon $from, ?Carbon $to): void
    {
        if (! $sheet) {
            return;
        }

        $report = $this->summaryService->summary($from, $to);

        $sheet->setCellValue('B4', $report['material_purchased_kg']);
        $sheet->setCellValue('B5', $this->sumValues(MaterialIntake::class, 'total_value', $from, $to));
        $sheet->setCellValue('B6', $this->sumValues(CrushingProduction::class, 'input_weight_kg', $from, $to));
        $sheet->setCellValue('B7', $report['chips_produced_kg']);
        $sheet->setCellValue('B8', $this->sumValues(CrushingProduction::class, 'loss_kg', $from, $to));

        $sheet->setCellValue('D4', $report['sales_revenue']);
        $sheet->setCellValue('D5', $report['payable_to_crushing']);
        $sheet->setCellValue('D6', $report['cash_remitted']);
        $sheet->setCellValue('D7', $report['outstanding_to_crushing']);
        $sheet->setCellValue('D8', $report['balance_retained']);
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     */
    private function replaceDataRows(Worksheet $sheet, int $startRow, array $rows): void
    {
        $highestRow = $sheet->getHighestRow();
        if ($highestRow >= $startRow) {
            $sheet->removeRow($startRow, $highestRow - $startRow + 1);
        }

        if ($rows === []) {
            return;
        }

        $sheet->insertNewRowBefore($startRow, count($rows));

        foreach ($rows as $index => $row) {
            $rowNumber = $startRow + $index;
            $columnIndex = 1;
            foreach (array_values($row) as $value) {
                $cellRef = Coordinate::stringFromColumnIndex($columnIndex).$rowNumber;
                $sheet->setCellValue($cellRef, $value);
                $columnIndex++;
            }
        }
    }

    private function sumValues(string $modelClass, string $column, ?Carbon $from, ?Carbon $to): float
    {
        return (float) $modelClass::query()
            ->when($from, fn ($query) => $query->whereDate('date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('date', '<=', $to))
            ->sum($column);
    }
}
