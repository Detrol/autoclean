<?php

namespace App\Http\Controllers\Exports;

use App\Http\Controllers\Controller;
use App\Models\TimeLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TimeReportsExportController extends Controller
{
    public function __invoke(Request $request)
    {
        // Validate request parameters
        $validated = $request->validate([
            'period' => 'required|in:day,week,month,year',
            'date' => 'required|date_format:Y-m-d',
            'format' => 'nullable|in:csv,pdf',
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

        // Handle different formats
        if ($format === 'pdf') {
            return $this->generatePdfExport($user, $summary, $perStationStats, $timeLogs, $period, $startDate, $endDate);
        } else {
            return $this->generateCsvExport($summary, $perStationStats, $timeLogs, $period, $startDate, $endDate);
        }
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
            'total_minutes' => $timeLogs->sum('total_minutes'),
            'regular_minutes' => $regularLogs->sum('total_minutes'),
            'oncall_minutes' => $oncallLogs->sum('total_minutes'),
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
                    'regular_minutes' => $logs->where('is_oncall', false)->sum('total_minutes'),
                    'oncall_minutes' => $logs->where('is_oncall', true)->sum('total_minutes'),
                    'total_minutes' => $logs->sum('total_minutes'),
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
        $this->csvRow(['Total tid', $this->formatTimeSv($summary['total_minutes']), $summary['total_minutes']]);
        $this->csvRow(['Ordinarie tid', $this->formatTimeSv($summary['regular_minutes']), $summary['regular_minutes']]);
        $this->csvRow(['Jour tid', $this->formatTimeSv($summary['oncall_minutes']), $summary['oncall_minutes']]);
        $this->csvRow(['Dagar', $summary['days_worked']]);
        $this->csvRow(['Ordinarie dagar', $summary['regular_days']]);
        $this->csvRow(['Jour dagar', $summary['oncall_days']]);
        
        // Blank row
        $this->csvRow([]);

        // Per-station table
        if ($perStationStats->isNotEmpty()) {
            $this->csvRow(['Tid per station']);
            $this->csvRow(['Station', 'Ordinarie tid', 'Ordinarie minuter', 'Jour tid', 'Jour minuter', 'Total tid', 'Total minuter']);
            
            foreach ($perStationStats as $stationName => $minutes) {
                $this->csvRow([
                    $stationName ?: 'Okänd station',
                    $this->formatTimeSv($minutes['regular_minutes']),
                    $minutes['regular_minutes'],
                    $this->formatTimeSv($minutes['oncall_minutes']),
                    $minutes['oncall_minutes'],
                    $this->formatTimeSv($minutes['total_minutes']),
                    $minutes['total_minutes']
                ]);
            }
        }

        // Blank row
        $this->csvRow([]);

        // Detailed logs table
        $this->csvRow(['Datum', 'Veckodag', 'Station', 'Typ', 'Starttid', 'Sluttid', 'Tid', 'Minuter', 'Anteckningar']);
        
        foreach ($timeLogs as $log) {
            $this->csvRow([
                $log->date->format('Y-m-d'),
                $this->getSwedishWeekdayAbbrev($log->date),
                $log->station->name ?? '',
                $log->is_oncall ? 'Jour' : 'Ordinarie',
                $log->clock_in ? $log->clock_in->format('H:i') : '',
                $log->clock_out ? $log->clock_out->format('H:i') : '',
                $this->formatTimeSv($log->total_minutes),
                $log->total_minutes,
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

    private function formatTimeSv(?int $minutes): string
    {
        return app(\App\Support\TimeFormatter::class)->formatMinutesSv($minutes);
    }

    private function generateCsvExport($summary, $perStationStats, $timeLogs, $period, $startDate, $endDate)
    {
        $filename = sprintf(
            'tidsrapport-%s-%s-%s.csv',
            $period,
            $startDate->toDateString(),
            $endDate->toDateString()
        );

        return response()->streamDownload(function () use ($summary, $perStationStats, $timeLogs) {
            $this->generateCsvContent($summary, $perStationStats, $timeLogs);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function generatePdfExport($user, $summary, $perStationStats, $timeLogs, $period, $startDate, $endDate)
    {
        $filename = sprintf(
            'tidsrapport-%s-%s-%s.pdf',
            $period,
            $startDate->toDateString(),
            $endDate->toDateString()
        );

        $periodLabel = $this->getPeriodLabel($period, $startDate);

        $pdf = Pdf::loadView('exports.time-reports-pdf', [
            'summary' => $summary,
            'perStationStats' => $perStationStats,
            'timeLogs' => $timeLogs,
            'periodLabel' => $periodLabel,
            'userName' => $user->name,
            'getWeekday' => [$this, 'getSwedishWeekdayAbbrev'],
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
            'fontSubsetting' => false,
        ]);

        return $pdf->download($filename);
    }

    public function getSwedishWeekdayAbbrev(?Carbon $date): string
    {
        if (!$date) {
            return '';
        }
        
        // Swedish weekday abbreviations (lowercase with diacritics)
        $weekdays = [
            1 => 'mån', // måndag
            2 => 'tis', // tisdag
            3 => 'ons', // onsdag
            4 => 'tor', // torsdag
            5 => 'fre', // fredag
            6 => 'lör', // lördag
            7 => 'sön', // söndag
        ];
        
        // Get day of week (1 = Monday, 7 = Sunday)
        $dayOfWeek = $date->dayOfWeekIso;
        
        return $weekdays[$dayOfWeek] ?? '';
    }

    private function getPeriodLabel(string $period, Carbon $startDate): string
    {
        switch ($period) {
            case 'day':
                return $startDate->isoFormat('D MMMM YYYY');
            case 'week':
                $endDate = $startDate->copy()->endOfWeek();
                return 'Vecka ' . $startDate->weekOfYear . ' (' . 
                       $startDate->isoFormat('D MMM') . ' - ' . 
                       $endDate->isoFormat('D MMM YYYY') . ')';
            case 'month':
                return $startDate->isoFormat('MMMM YYYY');
            case 'year':
                return $startDate->format('Y');
            default:
                return '';
        }
    }
}
