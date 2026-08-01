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

        {{-- Crushing vs palletizing --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            @foreach ([
                ['title' => 'Crushing', 'data' => $report['crushing'], 'icon' => 'heroicon-o-cog-6-tooth'],
                ['title' => 'Palletizing', 'data' => $report['palletizing'], 'icon' => 'heroicon-o-cube'],
            ] as $stage)
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-gray-950 dark:text-white">{{ $stage['title'] }}</p>
                        <x-filament::icon :icon="$stage['icon']" class="h-5 w-5 text-info-600 dark:text-info-400" />
                    </div>
                    <dl class="mt-4 grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Input</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">{{ number_format($stage['data']['input_kg'], 1) }} kg</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Output</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">{{ number_format($stage['data']['output_kg'], 1) }} kg</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Loss</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">{{ number_format($stage['data']['loss_kg'], 1) }} kg</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Loss %</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">{{ number_format($stage['data']['loss_percentage'] * 100, 2) }}%</dd>
                        </div>
                    </dl>
                </div>
            @endforeach
        </div>

        {{-- Per material --}}
        <x-filament::section heading="Crushing by material" description="Input, output and loss grouped by material.">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            <th class="px-3 py-2 font-medium">Material</th>
                            <th class="px-3 py-2 text-right font-medium">Input</th>
                            <th class="px-3 py-2 text-right font-medium">Output</th>
                            <th class="px-3 py-2 text-right font-medium">Loss</th>
                            <th class="px-3 py-2 text-right font-medium">Loss %</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($report['per_material'] as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-3 py-2 font-medium text-gray-950 dark:text-white">
                                    <span class="rounded bg-primary-50 px-1.5 py-0.5 text-xs font-semibold text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">{{ $row['material_code'] }}</span>
                                    <span class="ml-2 text-gray-600 dark:text-gray-400">{{ $row['material_name'] }}</span>
                                </td>
                                <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($row['input_kg'], 1) }} kg</td>
                                <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($row['output_kg'], 1) }} kg</td>
                                <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($row['loss_kg'], 1) }} kg</td>
                                <td class="px-3 py-2 text-right font-semibold text-gray-950 dark:text-white">{{ number_format($row['loss_percentage'] * 100, 2) }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">No production in this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- Monthly --}}
        <x-filament::section heading="Monthly output" description="Chips and pellets produced per month.">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            <th class="px-3 py-2 font-medium">Month</th>
                            <th class="px-3 py-2 text-right font-medium">Chips output</th>
                            <th class="px-3 py-2 text-right font-medium">Pellets output</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($report['monthly'] as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-3 py-2 font-medium text-gray-950 dark:text-white">{{ $row['period'] }}</td>
                                <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($row['crushing_output_kg'], 1) }} kg</td>
                                <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($row['palletizing_output_kg'], 1) }} kg</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">No production in this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
