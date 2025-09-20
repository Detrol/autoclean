<div class="p-8 max-w-full">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tidsrapporter</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Översikt över dina arbetade timmar</p>
        </div>

        <!-- Period selector -->
        <div class="flex gap-2">
            <flux:button.group>
                <flux:button
                    wire:click="setPeriod('day')"
                    :variant="$periodType === 'day' ? 'filled' : 'ghost'"
                    size="sm">
                    Dag
                </flux:button>
                <flux:button
                    wire:click="setPeriod('week')"
                    :variant="$periodType === 'week' ? 'filled' : 'ghost'"
                    size="sm">
                    Vecka
                </flux:button>
                <flux:button
                    wire:click="setPeriod('month')"
                    :variant="$periodType === 'month' ? 'filled' : 'ghost'"
                    size="sm">
                    Månad
                </flux:button>
                <flux:button
                    wire:click="setPeriod('year')"
                    :variant="$periodType === 'year' ? 'filled' : 'ghost'"
                    size="sm">
                    År
                </flux:button>
            </flux:button.group>
        </div>
    </div>

    <!-- Period navigation -->
    <div class="flex items-center justify-between mb-6 p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
        <flux:button
            wire:click="previousPeriod"
            variant="ghost"
            icon="chevron-left">
            Föregående
        </flux:button>

        <div class="text-center">
            <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ $periodLabel }}</div>
            <flux:button
                wire:click="currentPeriod"
                variant="subtle"
                size="xs"
                class="mt-1">
                Gå till idag
            </flux:button>
        </div>

        <flux:button
            wire:click="nextPeriod"
            variant="ghost"
            icon-trailing="chevron-right">
            Nästa
        </flux:button>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <!-- Total Hours -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <div class="text-sm text-gray-600 dark:text-gray-400">Totala timmar</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_hours'], 1) }}h</div>
        </div>

        <!-- Regular Hours -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <div class="text-sm text-gray-600 dark:text-gray-400">Ordinarie</div>
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($stats['regular_hours'], 1) }}h</div>
        </div>

        <!-- On-call Hours -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <div class="text-sm text-gray-600 dark:text-gray-400">Jour</div>
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($stats['oncall_hours'], 1) }}h</div>
        </div>

        <!-- Days Worked -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <div class="text-sm text-gray-600 dark:text-gray-400">Arbetade dagar</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['days_worked'] }}</div>
        </div>

        <!-- Regular Days -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <div class="text-sm text-gray-600 dark:text-gray-400">Ordinarie dagar</div>
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['regular_days'] }}</div>
        </div>

        <!-- On-call Days -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <div class="text-sm text-gray-600 dark:text-gray-400">Jourdagar</div>
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['oncall_days'] }}</div>
        </div>
    </div>

    <!-- Hours by Station -->
    @if($hoursByStation->isNotEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm mb-6 p-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Timmar per station</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b dark:border-gray-700">
                    <tr>
                        <th class="text-left py-2 px-2 text-sm font-medium text-gray-700 dark:text-gray-300">Station</th>
                        <th class="text-right py-2 px-2 text-sm font-medium text-gray-700 dark:text-gray-300">Ordinarie</th>
                        <th class="text-right py-2 px-2 text-sm font-medium text-gray-700 dark:text-gray-300">Jour</th>
                        <th class="text-right py-2 px-2 text-sm font-medium text-gray-700 dark:text-gray-300">Totalt</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @foreach($hoursByStation as $station => $hours)
                    <tr>
                        <td class="py-2 px-2 text-sm text-gray-900 dark:text-white">{{ $station }}</td>
                        <td class="py-2 px-2 text-sm text-right text-blue-600 dark:text-blue-400">{{ number_format($hours['regular'], 1) }}h</td>
                        <td class="py-2 px-2 text-sm text-right text-purple-600 dark:text-purple-400">{{ number_format($hours['oncall'], 1) }}h</td>
                        <td class="py-2 px-2 text-sm text-right font-semibold text-gray-900 dark:text-white">{{ number_format($hours['total'], 1) }}h</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Time Logs Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Detaljerad tidslogg</h3>
        </div>

        @if($timeLogs->isEmpty())
            <div class="p-8 text-center">
                <x-heroicon-o-clock class="w-12 h-12 text-gray-400 mx-auto mb-2" />
                <p class="text-gray-600 dark:text-gray-400">Inga tidsloggar för vald period.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 border-b dark:border-gray-700">
                        <tr>
                            <th class="text-left py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Datum</th>
                            <th class="text-left py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Station</th>
                            <th class="text-left py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Typ</th>
                            <th class="text-left py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Inklockat</th>
                            <th class="text-left py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Utklockat</th>
                            <th class="text-right py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Timmar</th>
                            <th class="text-left py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Anteckningar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y dark:divide-gray-700">
                        @foreach($timeLogs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="py-3 px-4 text-sm text-gray-900 dark:text-white">
                                {{ $log->date->isoFormat('D MMM YYYY') }}
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-900 dark:text-white">
                                {{ $log->station->name ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-sm">
                                @if($log->is_oncall)
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-200">Jour</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-200">Ordinarie</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $log->clock_in->format('H:i') }}
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $log->clock_out ? $log->clock_out->format('H:i') : '-' }}
                            </td>
                            <td class="py-3 px-4 text-sm text-right font-medium text-gray-900 dark:text-white">
                                {{ number_format($log->total_hours, 1) }}h
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $log->notes ?? '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-700/50 border-t dark:border-gray-700">
                        <tr>
                            <td colspan="5" class="py-3 px-4 text-sm font-semibold text-gray-900 dark:text-white">
                                Totalt för perioden
                            </td>
                            <td class="py-3 px-4 text-sm text-right font-bold text-gray-900 dark:text-white">
                                {{ number_format($stats['total_hours'], 1) }}h
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

    <!-- Info text -->
    <div class="mt-6 text-sm text-gray-600 dark:text-gray-400 text-center">
        <p>Tips: Du kan markera och kopiera tabelldata för att klistra in i externa rapporteringssystem.</p>
    </div>
</div>