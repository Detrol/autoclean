<?php

namespace App\Http\Controllers\Exports;

use App\Http\Controllers\Controller;
use App\Models\TimeLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimeReportsExportController extends Controller
{
    public function __invoke(Request $request)
    {
        // Validate request parameters
        $validated = $request->validate([
            'period' => 'required|in:day,week,month,year',
            'date' => 'required|date_format:Y-m-d',
            'format' => 'nullable|in:csv',
        ]);

        $period = $validated['period'];
        $selectedDate = $validated['date'];
        $format = $validated['format'] ?? 'csv';

        // Compute period boundaries
        [$startDate, $endDate] = $this->computePeriodBoundaries($selectedDate, $period);

        // Fetch logs for the authenticated user
        $user = $request->user();
        $timeLogs = TimeLog::query()
            ->forUser($user->id)
            ->completed()
            ->forDateRange($startDate->toDateString(), $endDate->toDateString())
            ->with(['station'])
            ->orderBy('date', 'desc')
            ->orderBy('clock_in', 'desc')
            ->get();

        // Compute statistics
        $summary = $this->computeSummaryStatistics($timeLogs);
        $perStationStats = $this->computePerStationStatistics($timeLogs);

        // Generate filename
        $filename = sprintf(
            'tidsrapport-%s-%s-%s.csv',
            $period,
            $startDate->toDateString(),
            $endDate->toDateString()
        );

        // Return CSV download
        return response()->streamDownload(function () use ($summary, $perStationStats, $timeLogs) {
            $this->generateCsvContent($summary, $perStationStats, $timeLogs);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function computePeriodBoundaries(string $selectedDate, string $period): array
    {
        $date = Carbon::parse($selectedDate);

        switch ($period) {
            case 'day':
                return [$date->copy()->startOfDay(), $date->copy()->endOfDay()];
            case 'week':
                return [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()];
            case 'month':
                return [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()];
            case 'year':
                return [$date->copy()->startOfYear(), $date->copy()->endOfYear()];
            default:
                return [$date->copy(), $date->copy()];
        }
    }

    private function computeSummaryStatistics($timeLogs): array
    {
        $regularLogs = $timeLogs->where('is_oncall', false);
        $oncallLogs = $timeLogs->where('is_oncall', true);

        return [
            'total_hours' => round($timeLogs->sum('total_minutes') / 60, 2),
            'regular_hours' => round($regularLogs->sum('total_minutes') / 60, 2),
            'oncall_hours' => round($oncallLogs->sum('total_minutes') / 60, 2),
            'days_worked' => $timeLogs->pluck('date')->unique()->count(),
            'regular_days' => $regularLogs->pluck('date')->unique()->count(),
            'oncall_days' => $oncallLogs->pluck('date')->unique()->count(),
        ];
    }

    private function computePerStationStatistics($timeLogs)
    {
        return $timeLogs->groupBy('station.name')
            ->map(function ($logs) {
                return [
                    'regular' => round($logs->where('is_oncall', false)->sum('total_minutes') / 60, 2),
                    'oncall' => round($logs->where('is_oncall', true)->sum('total_minutes') / 60, 2),
                    'total' => round($logs->sum('total_minutes') / 60, 2),
                ];
            })
            ->sortKeys();
    }

    private function generateCsvContent(array $summary, $perStationStats, $timeLogs): void
    {
        // UTF-8 BOM
        echo "\xEF\xBB\xBF";

        // Summary section
        $this->csvRow(['Summering']);
        $this->csvRow(['Totalt timmar', $this->hoursSv($summary['total_hours'])]);
        $this->csvRow(['Ordinarie timmar', $this->hoursSv($summary['regular_hours'])]);
        $this->csvRow(['Jour timmar', $this->hoursSv($summary['oncall_hours'])]);
        $this->csvRow(['Dagar', $summary['days_worked']]);
        $this->csvRow(['Ordinarie dagar', $summary['regular_days']]);
        $this->csvRow(['Jour dagar', $summary['oncall_days']]);
        
        // Blank row
        $this->csvRow([]);

        // Per-station table
        if ($perStationStats->isNotEmpty()) {
            $this->csvRow(['Timmar per station']);
            $this->csvRow(['Station', 'Ordinarie timmar', 'Jour timmar', 'Totalt']);
            
            foreach ($perStationStats as $stationName => $hours) {
                $this->csvRow([
                    $stationName ?: 'Okänd station',
                    $this->hoursSv($hours['regular']),
                    $this->hoursSv($hours['oncall']),
                    $this->hoursSv($hours['total'])
                ]);
            }
        }

        // Blank row
        $this->csvRow([]);

        // Detailed logs table
        $this->csvRow(['Datum', 'Station', 'Typ', 'Timmar', 'Anteckningar']);
        
        foreach ($timeLogs as $log) {
            $this->csvRow([
                $log->date->format('Y-m-d'),
                $log->station->name ?? '',
                $log->is_oncall ? 'Jour' : 'Ordinarie',
                $this->hoursSv($log->total_hours),
                $log->notes ?? ''
            ]);
        }
    }

    private function csvField($value): string
    {
        $value = (string) $value;
        
        // Check if field needs quoting (contains semicolon, quote, CR, or LF)
        if (str_contains($value, ';') || 
            str_contains($value, '"') || 
            str_contains($value, "\r") || 
            str_contains($value, "\n")) {
            
            // Escape quotes by doubling them and wrap in quotes
            return '"' . str_replace('"', '""', $value) . '"';
        }
        
        return $value;
    }

    private function csvRow(array $fields): void
    {
        echo implode(';', array_map([$this, 'csvField'], $fields)) . "\r\n";
    }

    private function hoursSv(float $hours): string
    {
        return number_format($hours, 1, ',', '');
    }
}