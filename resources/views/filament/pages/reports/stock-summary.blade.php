<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Date range filter --}}
        <x-filament::section>
            <div class="flex flex-wrap items-end gap-4">
                <div>
                    <label for="from" class="block text-sm font-medium text-gray-600 dark:text-gray-400">From</label>
                    <input
                        type="date"
                        id="from"
                        wire:model.live="from"
                        class="mt-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    >
                </div>
                <div>
                    <label for="to" class="block text-sm font-medium text-gray-600 dark:text-gray-400">To</label>
                    <input
                        type="date"
                        id="to"
                        wire:model.live="to"
                        class="mt-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    >
                </div>
                @if ($from || $to)
                    <x-filament::button color="gray" size="sm" wire:click="clearFilters">
                        Clear
                    </x-filament::button>
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
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @php
                $stats = [
                    ['label' => 'Material purchased', 'value' => number_format($report['totals']['material_purchased_kg'], 1).' kg', 'icon' => 'heroicon-o-arrow-trending-up', 'color' => 'text-success-600 dark:text-success-400'],
                    ['label' => 'Chips produced', 'value' => number_format($report['totals']['chips_produced_kg'], 1).' kg', 'icon' => 'heroicon-o-cog-6-tooth', 'color' => 'text-info-600 dark:text-info-400'],
                    ['label' => 'Chips dispatched', 'value' => number_format($report['totals']['chips_dispatched_kg'], 1).' kg', 'icon' => 'heroicon-o-truck', 'color' => 'text-warning-600 dark:text-warning-400'],
                    ['label' => 'Chips on hand', 'value' => number_format($report['totals']['chips_on_hand_kg'], 1).' kg', 'icon' => 'heroicon-o-scale', 'color' => 'text-info-600 dark:text-info-400'],
                    ['label' => 'Chips received (palletizing)', 'value' => number_format($report['totals']['chips_received_kg'], 1).' kg', 'icon' => 'heroicon-o-inbox-arrow-down', 'color' => 'text-info-600 dark:text-info-400'],
                    ['label' => 'Receiving variance', 'value' => number_format($report['totals']['receiving_variance_kg'], 1).' kg', 'icon' => 'heroicon-o-exclamation-triangle', 'color' => 'text-danger-600 dark:text-danger-400'],
                    ['label' => 'Pellets produced', 'value' => number_format($report['totals']['pellets_produced_kg'], 1).' kg', 'icon' => 'heroicon-o-cube', 'color' => 'text-success-600 dark:text-success-400'],
                    ['label' => 'Pellets sold', 'value' => number_format($report['totals']['pellets_sold_kg'], 1).' kg', 'icon' => 'heroicon-o-shopping-cart', 'color' => 'text-success-600 dark:text-success-400'],
                    ['label' => 'Finished stock', 'value' => number_format($report['totals']['finished_stock_kg'], 1).' kg', 'icon' => 'heroicon-o-archive-box', 'color' => 'text-info-600 dark:text-info-400'],
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

        {{-- Per material --}}
        <x-filament::section heading="Material breakdown" description="Intake, production, dispatch and receipt by material for the selected period.">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            <th class="px-3 py-2 font-medium">Material</th>
                            <th class="px-3 py-2 text-right font-medium">Purchased</th>
                            <th class="px-3 py-2 text-right font-medium">Produced</th>
                            <th class="px-3 py-2 text-right font-medium">Dispatched</th>
                            <th class="px-3 py-2 text-right font-medium">Received</th>
                            <th class="px-3 py-2 text-right font-medium">On hand</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($report['per_material'] as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-3 py-2 font-medium text-gray-950 dark:text-white">
                                    <span class="rounded bg-primary-50 px-1.5 py-0.5 text-xs font-semibold text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">{{ $row['material_code'] }}</span>
                                    <span class="ml-2 text-gray-600 dark:text-gray-400">{{ $row['material_name'] }}</span>
                                </td>
                                <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($row['purchased_kg'], 1) }} kg</td>
                                <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($row['produced_kg'], 1) }} kg</td>
                                <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($row['dispatched_kg'], 1) }} kg</td>
                                <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($row['received_kg'], 1) }} kg</td>
                                <td class="px-3 py-2 text-right font-semibold text-gray-950 dark:text-white">{{ number_format($row['on_hand_kg'], 1) }} kg</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">No material movement in this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
