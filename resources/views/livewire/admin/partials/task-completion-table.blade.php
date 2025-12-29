<!-- Scheduled Task Completions -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm mb-6">
    <div class="px-4 py-3 border-b dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Schemalagda uppgifter
        </h3>
    </div>

    @if($taskCompletions->isEmpty())
        <div class="p-8 text-center">
            <x-heroicon-o-clipboard-document-check class="w-12 h-12 text-gray-400 mx-auto mb-2" />
            <p class="text-gray-600 dark:text-gray-400">
                Inga uppgifter slutförda för vald period.
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
                            Uppgift
                        </th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Station
                        </th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Slutförd av
                        </th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Slutförd
                        </th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Anteckningar
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @foreach($taskCompletions as $schedule)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="py-3 px-4 text-sm text-gray-900 dark:text-white">
                            {{ $schedule->scheduled_date->isoFormat('D MMM YYYY') }}
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-900 dark:text-white">
                            {{ $schedule->task->name ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">
                            {{ $schedule->task->station->name ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-sm">
                            <div class="flex items-center gap-2">
                                @if($schedule->completedBy)
                                    <flux:avatar size="xs" :name="$schedule->completedBy->name" />
                                    <span class="text-gray-900 dark:text-white">
                                        {{ $schedule->completedBy->name }}
                                    </span>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">
                            {{ $schedule->completed_at?->format('H:i') ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate">
                            {{ $schedule->notes ?? '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t dark:border-gray-700">
            {{ $taskCompletions->links() }}
        </div>
    @endif
</div>

<!-- Additional Tasks -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
    <div class="px-4 py-3 border-b dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Extra uppgifter
        </h3>
    </div>

    @if($additionalTasks->isEmpty())
        <div class="p-8 text-center">
            <x-heroicon-o-plus-circle class="w-12 h-12 text-gray-400 mx-auto mb-2" />
            <p class="text-gray-600 dark:text-gray-400">
                Inga extra uppgifter registrerade för vald period.
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
                            Uppgift
                        </th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Station
                        </th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Utförd av
                        </th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">
                            Anteckningar
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @foreach($additionalTasks as $task)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="py-3 px-4 text-sm text-gray-900 dark:text-white">
                            {{ $task->completed_date->isoFormat('D MMM YYYY') }}
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-900 dark:text-white">
                            {{ $task->task_name }}
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">
                            {{ $task->station->name ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-sm">
                            <div class="flex items-center gap-2">
                                @if($task->user)
                                    <flux:avatar size="xs" :name="$task->user->name" />
                                    <span class="text-gray-900 dark:text-white">
                                        {{ $task->user->name }}
                                    </span>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate">
                            {{ $task->notes ?? '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t dark:border-gray-700">
            {{ $additionalTasks->links() }}
        </div>
    @endif
</div>
