<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Date range filter --}}
        <x-filament::section>
            <div class="flex flex-wrap items-end gap-4">
                <div>
                    <label for="from" class="block text-sm font-medium text-gray-600 dark:text-gray-400">From</label>
                    <input type="date" id="from" wire:model.live="from" class="mt-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                </div>
                <div>
                    <label for="to" class="block text-sm font-medium text-gray-600 dark:text-gray-400">To</label>
                    <input type="date" id="to" wire:model.live="to" class="mt-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                </div>
                @if ($from || $to)
                    <x-filament::button color="gray" size="sm" wire:click="clearFilters">Clear</x-filament::button>
                @endif
                <div class="ml-auto text-sm text-gray-500 dark:text-gray-400">
                    @if ($report['period']['from'] || $report['period']['to'])
                        Period: {{ $report['period']['from'] ?? 'start' }} → {{ $report['period']['to'] ?? 'today' }}
                    @else
                        All time
                    @endif
                </div>
            </div>
        </x-filament::section>

        {{-- Totals --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $stats = [
                    ['label' => 'Pellets sold', 'value' => number_format($report['totals']['kg_sold'], 1).' kg', 'icon' => 'heroicon-o-shopping-cart'],
                    ['label' => 'Revenue', 'value' => '$'.number_format($report['totals']['revenue'], 2), 'icon' => 'heroicon-o-banknotes'],
                    ['label' => 'Avg. price / kg', 'value' => '$'.number_format($report['totals']['average_price_per_kg'], 2), 'icon' => 'heroicon-o-currency-dollar'],
                    ['label' => 'Transactions', 'value' => number_format($report['totals']['transactions']), 'icon' => 'heroicon-o-receipt-percent'],
                ];
            @endphp
            @foreach ($stats as $stat)
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $stat['label'] }}</p>
                        <x-filament::icon :icon="$stat['icon']" class="h-5 w-5 text-success-600 dark:text-success-400" />
                    </div>
                    <p class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Per customer --}}
        <x-filament::section heading="By customer" description="Sales grouped by customer, ranked by revenue.">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            <th class="px-3 py-2 font-medium">Customer</th>
                            <th class="px-3 py-2 text-right font-medium">Transactions</th>
                            <th class="px-3 py-2 text-right font-medium">Kg sold</th>
                            <th class="px-3 py-2 text-right font-medium">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($report['per_customer'] as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-3 py-2 font-medium text-gray-950 dark:text-white">{{ $row['customer_name'] }}</td>
                                <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($row['transactions']) }}</td>
                                <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($row['kg_sold'], 1) }} kg</td>
                                <td class="px-3 py-2 text-right font-semibold text-gray-950 dark:text-white">${{ number_format($row['revenue'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">No sales in this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- Monthly --}}
        <x-filament::section heading="Monthly sales" description="Kg sold and revenue per month.">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            <th class="px-3 py-2 font-medium">Month</th>
                            <th class="px-3 py-2 text-right font-medium">Kg sold</th>
                            <th class="px-3 py-2 text-right font-medium">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($report['monthly'] as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-3 py-2 font-medium text-gray-950 dark:text-white">{{ $row['period'] }}</td>
                                <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($row['kg_sold'], 1) }} kg</td>
                                <td class="px-3 py-2 text-right font-semibold text-gray-950 dark:text-white">${{ number_format($row['revenue'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">No sales in this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
