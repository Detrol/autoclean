<!-- User Summary Table -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm mb-6">
    <div class="px-4 py-3 border-b dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Tid per användare
        </h3>
    </div>

    @if($userTimeBreakdown->isEmpty())
        <div class="p-8 text-center">
            <x-heroicon-o-users class="w-12 h-12 text-gray-400 mx-auto mb-2" />
            <p class="text-gray-600 dark:text-gray-400">
                Ingen aktivitet för vald period.
            </p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50 border-b dark:border-gray-700">
                    <tr>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Användare
                        </th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Stationer
                        </th>
                        <th class="text-right py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Ordinarie
                        </th>
                        <th class="text-right py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Jour
                        </th>
                        <th class="text-right py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Totalt
                        </th>
                        <th class="text-right py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Uppgifter
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @foreach($userTimeBreakdown as $userData)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <flux:avatar size="sm" :name="$userData['user']->name" />
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        {{ $userData['user']->name }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $userData['user']->email }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">
                            {{ $userData['stations']->implode(', ') ?: '-' }}
                        </td>
                        <td class="py-3 px-4 text-sm text-right text-blue-600 dark:text-blue-400">
                            {{ app(\App\Support\TimeFormatter::class)->formatMinutesSv($userData['regular_minutes']) }}
                        </td>
                        <td class="py-3 px-4 text-sm text-right text-purple-600 dark:text-purple-400">
                            {{ app(\App\Support\TimeFormatter::class)->formatMinutesSv($userData['oncall_minutes']) }}
                        </td>
                        <td class="py-3 px-4 text-sm text-right font-semibold text-gray-900 dark:text-white">
                            {{ app(\App\Support\TimeFormatter::class)->formatMinutesSv($userData['total_minutes']) }}
                        </td>
                        <td class="py-3 px-4 text-sm text-right text-green-600 dark:text-green-400">
                            {{ $userData['tasks_completed'] }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<!-- Detailed Time Logs Table -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
    <div class="px-4 py-3 border-b dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Detaljerade tidsloggar
        </h3>
    </div>

    @if($timeLogs->isEmpty())
        <div class="p-8 text-center">
            <x-heroicon-o-clock class="w-12 h-12 text-gray-400 mx-auto mb-2" />
            <p class="text-gray-600 dark:text-gray-400">
                Inga tidsloggar för vald period.
            </p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50 border-b dark:border-gray-700">
                    <tr>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Datum
                        </th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Användare
                        </th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Station
                        </th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Typ
                        </th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            In
                        </th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Ut
                        </th>
                        <th class="text-right py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Tid
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @foreach($timeLogs as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="py-3 px-4 text-sm text-gray-900 dark:text-white">
                            {{ $log->date->isoFormat('D MMM YYYY') }}
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-900 dark:text-white">
                            {{ $log->user->name ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">
                            {{ $log->station->name ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-sm">
                            @if($log->is_oncall)
                                <flux:badge color="purple" size="sm">Jour</flux:badge>
                            @else
                                <flux:badge color="blue" size="sm">Ordinarie</flux:badge>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">
                            {{ $log->clock_in?->format('H:i') ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">
                            {{ $log->clock_out?->format('H:i') ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-sm text-right font-medium text-gray-900 dark:text-white">
                            {{ app(\App\Support\TimeFormatter::class)->formatMinutesSv($log->total_minutes ?? 0) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-4 py-3 border-t dark:border-gray-700">
            {{ $timeLogs->links() }}
        </div>
    @endif
</div>
