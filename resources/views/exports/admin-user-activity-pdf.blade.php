<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Användaraktivitet - {{ $periodLabel }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #1f2937;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
        }
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 5px;
        }
        .header .period {
            font-size: 14px;
            color: #6b7280;
        }
        .header .meta {
            font-size: 9px;
            color: #9ca3af;
            margin-top: 5px;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .stat-box {
            display: table-cell;
            width: 16.66%;
            text-align: center;
            padding: 10px 5px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        .stat-box .label {
            font-size: 8px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .stat-box .value {
            font-size: 14px;
            font-weight: bold;
            color: #111827;
        }
        .stat-box .value.blue { color: #2563eb; }
        .stat-box .value.purple { color: #7c3aed; }
        .stat-box .value.green { color: #059669; }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #e5e7eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        th {
            background: #f3f4f6;
            padding: 6px 4px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 1px solid #d1d5db;
            text-transform: uppercase;
            font-size: 8px;
        }
        td {
            padding: 5px 4px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }
        tr:nth-child(even) {
            background: #f9fafb;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 500;
        }
        .badge-blue {
            background: #dbeafe;
            color: #1e40af;
        }
        .badge-purple {
            background: #ede9fe;
            color: #5b21b6;
        }
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #6b7280;
            font-style: italic;
        }
        .page-break {
            page-break-after: always;
        }
        .footer {
            position: fixed;
            bottom: 10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Användaraktivitet</h1>
        <div class="period">{{ $periodLabel }}</div>
        <div class="meta">
            Filter: {{ $userName }} | Genererad: {{ $generatedAt }}
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-box">
            <div class="label">Aktiva användare</div>
            <div class="value">{{ $summary['active_users'] ?? 0 }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Total tid</div>
            <div class="value">{{ app(\App\Support\TimeFormatter::class)->formatMinutesSv($summary['total_minutes'] ?? 0) }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Ordinarie</div>
            <div class="value blue">{{ app(\App\Support\TimeFormatter::class)->formatMinutesSv($summary['regular_minutes'] ?? 0) }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Jour</div>
            <div class="value purple">{{ app(\App\Support\TimeFormatter::class)->formatMinutesSv($summary['oncall_minutes'] ?? 0) }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Uppgifter</div>
            <div class="value green">{{ ($summary['tasks_completed'] ?? 0) + ($summary['additional_tasks_completed'] ?? 0) }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Stationer</div>
            <div class="value">{{ $summary['stations_active'] ?? 0 }}</div>
        </div>
    </div>

    <!-- Time Logs -->
    <div class="section">
        <div class="section-title">Tidsloggar</div>
        @if($timeLogs->isEmpty())
            <div class="empty-state">Inga tidsloggar för vald period.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Användare</th>
                        <th>Station</th>
                        <th>Typ</th>
                        <th>In</th>
                        <th>Ut</th>
                        <th class="text-right">Tid</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($timeLogs as $log)
                    <tr>
                        <td>{{ $log->date->format('Y-m-d') }}</td>
                        <td>{{ $log->user->name ?? '-' }}</td>
                        <td>{{ $log->station->name ?? '-' }}</td>
                        <td>
                            @if($log->is_oncall)
                                <span class="badge badge-purple">Jour</span>
                            @else
                                <span class="badge badge-blue">Ordinarie</span>
                            @endif
                        </td>
                        <td>{{ $log->clock_in ? $log->clock_in->format('H:i') : '-' }}</td>
                        <td>{{ $log->clock_out ? $log->clock_out->format('H:i') : '-' }}</td>
                        <td class="text-right">{{ app(\App\Support\TimeFormatter::class)->formatMinutesSv($log->total_minutes ?? 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Task Completions -->
    @if($taskCompletions->isNotEmpty())
    <div class="section">
        <div class="section-title">Schemalagda uppgifter</div>
        <table>
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Uppgift</th>
                    <th>Station</th>
                    <th>Slutförd av</th>
                    <th>Tid</th>
                    <th>Anteckningar</th>
                </tr>
            </thead>
            <tbody>
                @foreach($taskCompletions as $schedule)
                <tr>
                    <td>{{ $schedule->scheduled_date->format('Y-m-d') }}</td>
                    <td>{{ $schedule->task->name ?? '-' }}</td>
                    <td>{{ $schedule->task->station->name ?? '-' }}</td>
                    <td>{{ $schedule->completedBy->name ?? '-' }}</td>
                    <td>{{ $schedule->completed_at ? $schedule->completed_at->format('H:i') : '-' }}</td>
                    <td>{{ Str::limit($schedule->notes ?? '-', 50) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Additional Tasks -->
    @if($additionalTasks->isNotEmpty())
    <div class="section">
        <div class="section-title">Extra uppgifter</div>
        <table>
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Uppgift</th>
                    <th>Station</th>
                    <th>Utförd av</th>
                    <th>Anteckningar</th>
                </tr>
            </thead>
            <tbody>
                @foreach($additionalTasks as $task)
                <tr>
                    <td>{{ $task->completed_date->format('Y-m-d') }}</td>
                    <td>{{ $task->task_name ?? '-' }}</td>
                    <td>{{ $task->station->name ?? '-' }}</td>
                    <td>{{ $task->user->name ?? '-' }}</td>
                    <td>{{ Str::limit($task->notes ?? '-', 50) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Empty state if no data at all -->
    @if($timeLogs->isEmpty() && $taskCompletions->isEmpty() && $additionalTasks->isEmpty())
    <div class="empty-state">
        Ingen aktivitet registrerad för vald period.
    </div>
    @endif

    <div class="footer">
        AutoClean - Användaraktivitet | {{ $periodLabel }} | Sida 1
    </div>
</body>
</html>
