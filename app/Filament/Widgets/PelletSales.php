<?php

namespace App\Filament\Widgets;

use App\Models\PelletSale;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PelletSales extends ChartWidget
{
    use HasWidgetShield;

    protected ?string $heading = 'Pellet Sales';

    protected string $color = 'info';

    public ?string $filter = 'month';

    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected function getFilters(): ?array
    {
        return [
            'week' => 'Last 7 days',
            'month' => 'Last 30 days',
            'year' => 'This year',
            'all' => 'All time',
        ];
    }

    public function getDescription(): ?string
    {
        return 'Revenue and kilograms sold from recorded pellet sales.';
    }

    protected function getData(): array
    {
        $filter = array_key_exists($this->filter, $this->getFilters() ?? [])
            ? $this->filter
            : 'month';

        [$startDate, $labels, $format] = $this->chartRange($filter);

        $sales = PelletSale::query()
            ->select(['date', 'kg_sold', 'amount_received'])
            ->when($startDate, fn ($query) => $query->whereDate('date', '>=', $startDate))
            ->oldest('date')
            ->get();

        $revenueByPeriod = $this->sumByPeriod($sales, 'amount_received', $format);
        $kgByPeriod = $this->sumByPeriod($sales, 'kg_sold', $format);

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => array_map(fn (string $label): float => round($revenueByPeriod[$label] ?? 0, 2), $labels),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.16)',
                ],
                [
                    'label' => 'Kg sold',
                    'data' => array_map(fn (string $label): float => round($kgByPeriod[$label] ?? 0, 3), $labels),
                    'borderColor' => '#0ea5e9',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.12)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * @return array{0: Carbon|null, 1: array<int, string>, 2: string}
     */
    private function chartRange(string $filter): array
    {
        $today = now()->startOfDay();

        return match ($filter) {
            'week' => [
                $today->copy()->subDays(6),
                collect(range(6, 0))
                    ->map(fn (int $daysAgo): string => $today->copy()->subDays($daysAgo)->format('M d'))
                    ->all(),
                'M d',
            ],
            'year' => [
                $today->copy()->startOfYear(),
                collect(range(1, 12))
                    ->map(fn (int $month): string => $today->copy()->month($month)->format('M'))
                    ->all(),
                'M',
            ],
            'all' => [
                null,
                $this->allTimeLabels(),
                'M Y',
            ],
            default => [
                $today->copy()->subDays(29),
                collect(range(29, 0))
                    ->map(fn (int $daysAgo): string => $today->copy()->subDays($daysAgo)->format('M d'))
                    ->all(),
                'M d',
            ],
        };
    }

    /**
     * @param  Collection<int, PelletSale>  $sales
     * @return array<string, float>
     */
    private function sumByPeriod(Collection $sales, string $column, string $format): array
    {
        return $sales
            ->groupBy(fn (PelletSale $sale): string => $sale->date->format($format))
            ->map(fn (Collection $records): float => (float) $records->sum($column))
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function allTimeLabels(): array
    {
        $firstSaleDate = PelletSale::query()->oldest('date')->value('date');

        if ($firstSaleDate === null) {
            return [now()->format('M Y')];
        }

        $start = Carbon::parse($firstSaleDate)->startOfMonth();
        $end = now()->startOfMonth();
        $labels = [];

        while ($start->lte($end)) {
            $labels[] = $start->format('M Y');
            $start->addMonth();
        }

        return $labels;
    }
}
