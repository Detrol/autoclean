<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
    <!-- Total Users Active -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
        <div class="text-sm text-gray-600 dark:text-gray-400">Aktiva användare</div>
        <div class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ $stats['active_users'] ?? 0 }}
        </div>
    </div>

    <!-- Total Time -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
        <div class="text-sm text-gray-600 dark:text-gray-400">Total tid</div>
        <div class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ app(\App\Support\TimeFormatter::class)->formatMinutesSv($stats['total_minutes'] ?? 0) }}
        </div>
    </div>

    <!-- Regular Time -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
        <div class="text-sm text-gray-600 dark:text-gray-400">Ordinarie</div>
        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
            {{ app(\App\Support\TimeFormatter::class)->formatMinutesSv($stats['regular_minutes'] ?? 0) }}
        </div>
    </div>

    <!-- On-call Time -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
        <div class="text-sm text-gray-600 dark:text-gray-400">Jour</div>
        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
            {{ app(\App\Support\TimeFormatter::class)->formatMinutesSv($stats['oncall_minutes'] ?? 0) }}
        </div>
    </div>

    <!-- Tasks Completed -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
        <div class="text-sm text-gray-600 dark:text-gray-400">Uppgifter klara</div>
        <div class="text-2xl font-bold text-green-600 dark:text-green-400">
            {{ $stats['tasks_completed'] ?? 0 }}
        </div>
    </div>

    <!-- Stations Active -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
        <div class="text-sm text-gray-600 dark:text-gray-400">Stationer aktiva</div>
        <div class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ $stats['stations_active'] ?? 0 }}
        </div>
    </div>
</div>
