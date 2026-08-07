<?php

namespace App\Services;

use App\Models\CashRemittance;
use App\Models\Expense;
use App\Models\PelletSale;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CashFlowReportService
{
    /**
     * Build a unified cash flow ledger from existing records.
     *
     * Sales are cash in, remittances and expenses are cash out.
     *
     * @return array{
     *     period: array{from: ?string, to: ?string},
     *     totals: array{
     *         cash_in: float,
     *         cash_out: float,
     *         sales_revenue: float,
     *         cash_remitted: float,
     *         expenses: float,
     *         available_cash_balance: float,
     *         transactions: int,
     *         inflows: int,
     *         outflows: int
     *     },
     *     entries: array<int, array<string, mixed>>
     * }
     */
    public function report(?Carbon $from = null, ?Carbon $to = null): array
    {
        $entries = collect()
            ->merge($this->salesEntries($from, $to))
            ->merge($this->remittanceEntries($from, $to))
            ->merge($this->expenseEntries($from, $to))
            ->sortBy([
                ['date', 'asc'],
                ['rank', 'asc'],
                ['id', 'asc'],
            ])
            ->values();

        $runningBalance = 0.0;

        $entries = $entries->map(function (array $entry) use (&$runningBalance): array {
            $runningBalance += $entry['cash_in'];
            $runningBalance -= $entry['cash_out'];

            $entry['balance'] = round($runningBalance, 2);

            return $entry;
        });

        $salesRevenue = (float) $entries->sum('cash_in');
        $cashRemitted = (float) $entries->where('source_type', 'remittance')->sum('cash_out');
        $expenses = (float) $entries->where('source_type', 'expense')->sum('cash_out');

        return [
            'period' => [
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
            ],
            'totals' => [
                'cash_in' => round($salesRevenue, 2),
                'cash_out' => round($cashRemitted + $expenses, 2),
                'sales_revenue' => round($salesRevenue, 2),
                'cash_remitted' => round($cashRemitted, 2),
                'expenses' => round($expenses, 2),
                'available_cash_balance' => round($salesRevenue - $cashRemitted - $expenses, 2),
                'transactions' => $entries->count(),
                'inflows' => $entries->where('direction', 'in')->count(),
                'outflows' => $entries->where('direction', 'out')->count(),
            ],
            'entries' => $entries->all(),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function salesEntries(?Carbon $from, ?Carbon $to): Collection
    {
        return PelletSale::query()
            ->select(['id', 'date', 'receipt_number', 'customer_name', 'amount_received'])
            ->when($from, fn ($query, Carbon $from) => $query->whereDate('date', '>=', $from))
            ->when($to, fn ($query, Carbon $to) => $query->whereDate('date', '<=', $to))
            ->latest('date')
            ->latest('id')
            ->get()
            ->map(fn (PelletSale $sale): array => [
                'id' => $sale->id,
                'date' => $sale->date?->toDateString(),
                'rank' => 0,
                'direction' => 'in',
                'source_type' => 'sale',
                'type' => 'Sales receipt',
                'reference' => $sale->receipt_number,
                'description' => $sale->customer_name ?: 'Pellet sale',
                'payment_method' => null,
                'source_url' => route('filament.admin.resources.pellet-sales.view', ['record' => $sale]),
                'cash_in' => (float) $sale->amount_received,
                'cash_out' => 0.0,
                'balance' => 0.0,
            ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function remittanceEntries(?Carbon $from, ?Carbon $to): Collection
    {
        return CashRemittance::query()
            ->select(['id', 'date', 'voucher_number', 'period_covered', 'cash_remitted'])
            ->when($from, fn ($query, Carbon $from) => $query->whereDate('date', '>=', $from))
            ->when($to, fn ($query, Carbon $to) => $query->whereDate('date', '<=', $to))
            ->latest('date')
            ->latest('id')
            ->get()
            ->map(fn (CashRemittance $remittance): array => [
                'id' => $remittance->id,
                'date' => $remittance->date?->toDateString(),
                'rank' => 1,
                'direction' => 'out',
                'source_type' => 'remittance',
                'type' => 'Cash remittance',
                'reference' => $remittance->voucher_number,
                'description' => $remittance->period_covered ?: 'Cash remittance to Crushing Office',
                'payment_method' => 'Cash transfer',
                'source_url' => route('filament.admin.resources.cash-remittances.view', ['record' => $remittance]),
                'cash_in' => 0.0,
                'cash_out' => (float) $remittance->cash_remitted,
                'balance' => 0.0,
            ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function expenseEntries(?Carbon $from, ?Carbon $to): Collection
    {
        return Expense::query()
            ->with('category:id,name')
            ->select(['id', 'date', 'expense_number', 'expense_category_id', 'description', 'amount', 'payment_method'])
            ->when($from, fn ($query, Carbon $from) => $query->whereDate('date', '>=', $from))
            ->when($to, fn ($query, Carbon $to) => $query->whereDate('date', '<=', $to))
            ->latest('date')
            ->latest('id')
            ->get()
            ->map(fn (Expense $expense): array => [
                'id' => $expense->id,
                'date' => $expense->date?->toDateString(),
                'rank' => 2,
                'direction' => 'out',
                'source_type' => 'expense',
                'type' => 'Expense',
                'reference' => $expense->expense_number,
                'description' => trim(($expense->category?->name ? $expense->category->name.' - ' : '').($expense->description ?: '')),
                'payment_method' => $expense->payment_method,
                'source_url' => route('filament.admin.resources.expenses.view', ['record' => $expense]),
                'cash_in' => 0.0,
                'cash_out' => (float) $expense->amount,
                'balance' => 0.0,
            ]);
    }
}
