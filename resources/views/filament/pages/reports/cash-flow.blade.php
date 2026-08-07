<x-filament-panels::page>
    <div class="space-y-6">
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

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @php
                $stats = [
                    ['label' => 'Cash in', 'value' => '$'.number_format($report['totals']['cash_in'], 2), 'icon' => 'heroicon-o-arrow-trending-up', 'color' => 'text-success-600 dark:text-success-400'],
                    ['label' => 'Cash out', 'value' => '$'.number_format($report['totals']['cash_out'], 2), 'icon' => 'heroicon-o-arrow-trending-down', 'color' => 'text-danger-600 dark:text-danger-400'],
                    ['label' => 'Available cash balance', 'value' => '$'.number_format($report['totals']['available_cash_balance'], 2), 'icon' => 'heroicon-o-banknotes', 'color' => 'text-info-600 dark:text-info-400'],
                    ['label' => 'Transactions', 'value' => number_format($report['totals']['transactions']), 'icon' => 'heroicon-o-rectangle-stack', 'color' => 'text-warning-600 dark:text-warning-400'],
                ];
            @endphp
            @foreach ($stats as $stat)
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $stat['label'] }}</p>
                        <x-filament::icon :icon="$stat['icon']" class="h-5 w-5 {{ $stat['color'] }}" />
                    </div>
                    <p class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            @php
                $detailStats = [
                    ['label' => 'Sales revenue', 'value' => '$'.number_format($report['totals']['sales_revenue'], 2), 'icon' => 'heroicon-o-banknotes'],
                    ['label' => 'Cash remitted', 'value' => '$'.number_format($report['totals']['cash_remitted'], 2), 'icon' => 'heroicon-o-arrow-right-circle'],
                    ['label' => 'Expenses', 'value' => '$'.number_format($report['totals']['expenses'], 2), 'icon' => 'heroicon-o-receipt-percent'],
                ];
            @endphp
            @foreach ($detailStats as $stat)
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $stat['label'] }}</p>
                        <x-filament::icon :icon="$stat['icon']" class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                    </div>
                    <p class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </div>

        <x-filament::section heading="Cash flow ledger" description="Sales, remittances, and expenses combined into one running balance.">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            <th class="px-3 py-2 font-medium">Date</th>
                            <th class="px-3 py-2 font-medium">Type</th>
                            <th class="px-3 py-2 font-medium">Reference</th>
                            <th class="px-3 py-2 font-medium">Description</th>
                            <th class="px-3 py-2 text-right font-medium">Cash in</th>
                            <th class="px-3 py-2 text-right font-medium">Cash out</th>
                            <th class="px-3 py-2 text-right font-medium">Running balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($report['entries'] as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $row['date'] }}</td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $row['direction'] === 'in' ? 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-300' : 'bg-danger-50 text-danger-700 dark:bg-danger-500/10 dark:text-danger-300' }}">
                                        {{ $row['type'] }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 font-medium text-gray-950 dark:text-white">{{ $row['reference'] }}</td>
                                <td class="px-3 py-2 text-gray-700 dark:text-gray-300">
                                    <div>{{ $row['description'] }}</div>
                                    @if ($row['payment_method'])
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $row['payment_method'] }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right font-semibold text-success-700 dark:text-success-300">
                                    {{ $row['cash_in'] > 0 ? '$'.number_format($row['cash_in'], 2) : '—' }}
                                </td>
                                <td class="px-3 py-2 text-right font-semibold text-danger-700 dark:text-danger-300">
                                    {{ $row['cash_out'] > 0 ? '$'.number_format($row['cash_out'], 2) : '—' }}
                                </td>
                                <td class="px-3 py-2 text-right font-bold text-gray-950 dark:text-white">${{ number_format($row['balance'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">No cash flow records found for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
