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

        {{-- Reconciliation status banner --}}
        @php
            $status = $report['totals']['reconciliation_status'];
        @endphp
        <div class="rounded-xl p-4 shadow-sm ring-1 {{ $status === 'balanced' ? 'bg-success-50 ring-success-200 dark:bg-success-500/10 dark:ring-success-500/30' : 'bg-danger-50 ring-danger-200 dark:bg-danger-500/10 dark:ring-danger-500/30' }}">
            <div class="flex items-center gap-3">
                <x-filament::icon :icon="$status === 'balanced' ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-triangle'" class="h-6 w-6 {{ $status === 'balanced' ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}" />
                <div>
                    <p class="text-sm font-semibold {{ $status === 'balanced' ? 'text-success-700 dark:text-success-300' : 'text-danger-700 dark:text-danger-300' }}">
                        {{ $status === 'balanced' ? 'Balanced' : 'Shortfall' }}
                    </p>
                    <p class="text-xs {{ $status === 'balanced' ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                        {{ $status === 'balanced' ? 'Cash collected covers the retained balance.' : 'Cash remitted is less than the sales revenue collected.' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Totals --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @php
                $stats = [
                    ['label' => 'Sales revenue', 'value' => '$'.number_format($report['totals']['sales_revenue'], 2), 'icon' => 'heroicon-o-banknotes', 'color' => 'text-success-600 dark:text-success-400'],
                    ['label' => 'Cash remitted', 'value' => '$'.number_format($report['totals']['cash_remitted'], 2), 'icon' => 'heroicon-o-banknotes', 'color' => 'text-success-600 dark:text-success-400'],
                    ['label' => 'Balance retained', 'value' => '$'.number_format($report['totals']['balance_retained'], 2), 'icon' => 'heroicon-o-archive-box', 'color' => 'text-info-600 dark:text-info-400'],
                    ['label' => 'Cash collection gap', 'value' => '$'.number_format($report['totals']['cash_collection_gap'], 2), 'icon' => 'heroicon-o-exclamation-triangle', 'color' => 'text-warning-600 dark:text-warning-400'],
                    ['label' => 'Payable to crushing', 'value' => '$'.number_format($report['totals']['payable_to_crushing'], 2), 'icon' => 'heroicon-o-currency-dollar', 'color' => 'text-info-600 dark:text-info-400'],
                    ['label' => 'Outstanding to crushing', 'value' => '$'.number_format($report['totals']['outstanding_to_crushing'], 2), 'icon' => 'heroicon-o-arrow-trending-down', 'color' => 'text-danger-600 dark:text-danger-400'],
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

        {{-- Remittances --}}
        <x-filament::section heading="Remittances" description="Per-voucher breakdown of cash remitted, due and retained.">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            <th class="px-3 py-2 font-medium">Date</th>
                            <th class="px-3 py-2 font-medium">Voucher</th>
                            <th class="px-3 py-2 font-medium">Period</th>
                            <th class="px-3 py-2 text-right font-medium">Chips (kg)</th>
                            <th class="px-3 py-2 text-right font-medium">Recovery $/kg</th>
                            <th class="px-3 py-2 text-right font-medium">Sales revenue</th>
                            <th class="px-3 py-2 text-right font-medium">Remitted</th>
                            <th class="px-3 py-2 text-right font-medium">Max due</th>
                            <th class="px-3 py-2 text-right font-medium">Retained</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($report['remittances'] as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $row['date'] }}</td>
                                <td class="px-3 py-2 font-medium text-gray-950 dark:text-white">{{ $row['voucher_number'] }}</td>
                                <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $row['period_covered'] ?? '-' }}</td>
                                <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($row['chips_delivered_kg'], 1) }}</td>
                                <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">${{ number_format($row['recovery_price_per_kg'], 2) }}</td>
                                <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">${{ number_format($row['sales_revenue'], 2) }}</td>
                                <td class="px-3 py-2 text-right font-semibold text-gray-950 dark:text-white">${{ number_format($row['cash_remitted'], 2) }}</td>
                                <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">${{ number_format($row['max_remittance_due'], 2) }}</td>
                                <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">${{ number_format($row['balance_retained'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">No remittances in this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
