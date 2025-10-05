<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tidsrapport - {{ $periodLabel }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 15px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            color: #0284c7;
            font-size: 18px;
            font-weight: bold;
        }
        
        .header .period {
            margin: 5px 0 0 0;
            font-size: 12px;
            color: #666;
        }
        
        .user-info {
            margin-bottom: 20px;
            font-size: 11px;
        }
        
        .summary {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .summary h2 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #374151;
        }
        
        .stats-grid {
            display: table;
            width: 100%;
        }
        
        .stats-row {
            display: table-row;
        }
        
        .stats-cell {
            display: table-cell;
            padding: 3px 15px 3px 0;
        }
        
        .stats-label {
            font-weight: normal;
            color: #666;
        }
        
        .stats-value {
            font-weight: bold;
            color: #0284c7;
        }
        
        .section {
            margin-bottom: 20px;
        }
        
        .section h3 {
            margin: 0 0 10px 0;
            font-size: 12px;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 3px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        
        table th {
            background: #f3f4f6;
            color: #374151;
            font-weight: bold;
            padding: 6px;
            text-align: left;
            border-bottom: 1px solid #d1d5db;
            font-size: 9px;
        }
        
        table td {
            padding: 5px 6px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }
        
        table tr:nth-child(even) {
            background: #fafafa;
        }
        
        .type-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .type-regular {
            background: #dbeafe;
            color: #1d4ed8;
        }
        
        .type-oncall {
            background: #ede9fe;
            color: #7c3aed;
        }
        
        .text-right {
            text-align: right;
        }
        
        .font-bold {
            font-weight: bold;
        }
        
        .text-sm {
            font-size: 9px;
        }
        
        .footer {
            position: fixed;
            bottom: 15px;
            left: 15px;
            right: 15px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Tidsrapport</h1>
        <div class="period">{{ $periodLabel }}</div>
    </div>
    
    <div class="user-info">
        <strong>Anställd:</strong> {{ $userName }}<br>
        <strong>Genererad:</strong> {{ now()->isoFormat('D MMMM YYYY HH:mm') }}
    </div>

    <!-- Summary Section -->
    <div class="summary">
        <h2>Sammanfattning</h2>
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stats-cell stats-label">Total tid:</div>
                <div class="stats-cell stats-value">{{ app(App\Support\TimeFormatter::class)->formatMinutesSv($summary['total_minutes'] ?? 0) }}</div>
                <div class="stats-cell stats-label" style="padding-left: 30px;">Arbetade dagar:</div>
                <div class="stats-cell stats-value">{{ $summary['days_worked'] }}</div>
            </div>
            <div class="stats-row">
                <div class="stats-cell stats-label">Ordinarie tid:</div>
                <div class="stats-cell stats-value">{{ app(App\Support\TimeFormatter::class)->formatMinutesSv($summary['regular_minutes'] ?? 0) }}</div>
                <div class="stats-cell stats-label" style="padding-left: 30px;">Ordinarie dagar:</div>
                <div class="stats-cell stats-value">{{ $summary['regular_days'] }}</div>
            </div>
            <div class="stats-row">
                <div class="stats-cell stats-label">Jour tid:</div>
                <div class="stats-cell stats-value">{{ app(App\Support\TimeFormatter::class)->formatMinutesSv($summary['oncall_minutes'] ?? 0) }}</div>
                <div class="stats-cell stats-label" style="padding-left: 30px;">Jour dagar:</div>
                <div class="stats-cell stats-value">{{ $summary['oncall_days'] }}</div>
            </div>
        </div>
    </div>

    <!-- Per Station Section -->
    @if($perStationStats->isNotEmpty())
    <div class="section">
        <h3>Tid per station</h3>
        <table>
            <thead>
                <tr>
                    <th>Station</th>
                    <th class="text-right">Ordinarie</th>
                    <th class="text-right">Jour</th>
                    <th class="text-right">Totalt</th>
                </tr>
            </thead>
            <tbody>
                @foreach($perStationStats as $stationName => $minutes)
                <tr>
                    <td>{{ $stationName ?: 'Okänd station' }}</td>
                    <td class="text-right">{{ app(App\Support\TimeFormatter::class)->formatMinutesSv($minutes['regular_minutes'] ?? 0) }}</td>
                    <td class="text-right">{{ app(App\Support\TimeFormatter::class)->formatMinutesSv($minutes['oncall_minutes'] ?? 0) }}</td>
                    <td class="text-right font-bold">{{ app(App\Support\TimeFormatter::class)->formatMinutesSv($minutes['total_minutes'] ?? 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Detailed Logs Section -->
    <div class="section">
        <h3>Detaljerad tidslogg</h3>
        @if($timeLogs->isEmpty())
            <p style="text-align: center; color: #666; padding: 20px;">Inga tidsloggar för vald period.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Veckodag</th>
                        <th>Station</th>
                        <th>Typ</th>
                        <th class="text-right">Tid</th>
                        <th>Anteckningar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($timeLogs as $log)
                    <tr>
                        <td class="text-sm">{{ $log->date->format('Y-m-d') }}</td>
                        <td class="text-sm">{{ call_user_func($getWeekday, $log->date) }}</td>
                        <td class="text-sm">{{ $log->station->name ?? '' }}</td>
                        <td>
                            <span class="type-badge {{ $log->is_oncall ? 'type-oncall' : 'type-regular' }}">
                                {{ $log->is_oncall ? 'Jour' : 'Ordinarie' }}
                            </span>
                        </td>
                        <td class="text-right font-bold">{{ app(App\Support\TimeFormatter::class)->formatMinutesSv($log->total_minutes ?? 0) }}</td>
                        <td class="text-sm">{{ $log->notes ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="footer">
        Genererad från {{ config('app.name') }} • {{ now()->format('Y-m-d H:i') }}
    </div>
</body>
</html>