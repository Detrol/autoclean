<?php

namespace App\Http\Controllers\Exports;

use App\Http\Controllers\Controller;
use App\Models\CompletedAdditionalTask;
use App\Models\TaskSchedule;
use App\Models\TimeLog;
use App\Models\User;
use App\Support\TimeFormatter;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminUserActivityExportController extends Controller
{
    public function __invoke(Request $request): StreamedResponse|\Illuminate\Http\Response
    {
        abort_unless(Gate::allows('admin'), 403);

        $validated = $request->validate([
            'period' => 'required|in:day,week,month,year',
            'date' => 'required|date_format:Y-m-d',
            'format' => 'nullable|in:csv,pdf',
            'user_id' => 'nullable|exists:users,id',
            'station_id' => 'nullable|exists:stations,id',
            'work_type' => 'nullable|in:all,regular,oncall',
        ]);

        $period = $validated['period'];
        $selectedDate = $validated['date'];
        $format = $validated['format'] ?? 'csv';
        $userId = $validated['user_id'] ?? null;
        $stationId = $validated['station_id'] ?? null;
        $workType = $validated['work_type'] ?? 'all';

        [$startDate, $endDate] = $this->computePeriodBoundaries($selectedDate, $period);

        // Build time logs query
        $timeLogsQuery = TimeLog::query()
            ->completed()
            ->forDateRange($startDate->toDateString(), $endDate->toDateString())
            ->with(['user', 'station'])
            ->orderBy('date', 'desc')
            ->orderBy('clock_in', 'desc');

        if ($userId) {
            $timeLogsQuery->forUser($userId);
        }
        if ($stationId) {
            $timeLogsQuery->where('station_id', $stationId);
        }
        if ($workType === 'regular') {
            $timeLogsQuery->regular();
        } elseif ($workType === 'oncall') {
            $timeLogsQuery->oncall();
        }

        $timeLogs = $timeLogsQuery->get();

        // Build task completions query
        $taskCompletionsQuery = TaskSchedule::query()
            ->completed()
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->with(['task.station', 'completedBy'])
            ->orderBy('completed_at', 'desc');

        if ($userId) {
            $taskCompletionsQuery->where('completed_by', $userId);
        }
        if ($stationId) {
            $taskCompletionsQuery->whereHas('task', fn ($q) => $q->where('station_id', $stationId));
        }

        $taskCompletions = $taskCompletionsQuery->get();

        // Build additional tasks query
        $additionalTasksQuery = CompletedAdditionalTask::query()
            ->whereBetween('completed_date', [$startDate, $endDate])
            ->with(['user', 'station'])
            ->orderBy('completed_date', 'desc');

        if ($userId) {
            $additionalTasksQuery->forUser($userId);
        }
        if ($stationId) {
            $additionalTasksQuery->forStation($stationId);
        }

        $additionalTasks = $additionalTasksQuery->get();

        // Compute statistics
        $summary = $this->computeSummaryStatistics($timeLogs, $taskCompletions, $additionalTasks);

        if ($format === 'pdf') {
            return $this->generatePdfExport(
                $summary,
                $timeLogs,
                $taskCompletions,
                $additionalTasks,
                $period,
                $startDate,
                $endDate,
                $userId
            );
        }

        return $this->generateCsvExport(
            $summary,
            $timeLogs,
            $taskCompletions,
            $additionalTasks,
            $period,
            $startDate,
            $endDate
        );
    }

    private function computePeriodBoundaries(string $selectedDate, string $period): array
    {
        $date = Carbon::parse($selectedDate);

        return match ($period) {
            'day' => [$date->copy()->startOfDay(), $date->copy()->endOfDay()],
            'week' => [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()],
            'month' => [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()],
            'year' => [$date->copy()->startOfYear(), $date->copy()->endOfYear()],
            default => [$date->copy(), $date->copy()],
        };
    }

    private function computeSummaryStatistics($timeLogs, $taskCompletions, $additionalTasks): array
    {
        $regularLogs = $timeLogs->where('is_oncall', false);
        $oncallLogs = $timeLogs->where('is_oncall', true);

        return [
            'active_users' => $timeLogs->pluck('user_id')->unique()->count(),
            'total_minutes' => $timeLogs->sum('total_minutes'),
            'regular_minutes' => $regularLogs->sum('total_minutes'),
            'oncall_minutes' => $oncallLogs->sum('total_minutes'),
            'days_worked' => $timeLogs->pluck('date')->unique()->count(),
            'tasks_completed' => $taskCompletions->count(),
            'additional_tasks_completed' => $additionalTasks->count(),
            'stations_active' => $timeLogs->pluck('station_id')->unique()->count(),
        ];
    }

    private function generateCsvExport(
        array $summary,
        $timeLogs,
        $taskCompletions,
        $additionalTasks,
        string $period,
        Carbon $startDate,
        Carbon $endDate
    ): StreamedResponse {
        $filename = sprintf(
            'anvandaraktivitet-%s-%s-%s.csv',
            $period,
            $startDate->toDateString(),
            $endDate->toDateString()
        );

        return response()->streamDownload(function () use ($summary, $timeLogs, $taskCompletions, $additionalTasks) {
            $this->generateCsvContent($summary, $timeLogs, $taskCompletions, $additionalTasks);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function generateCsvContent(array $summary, $timeLogs, $taskCompletions, $additionalTasks): void
    {
        // UTF-8 BOM for Excel compatibility
        echo "\xEF\xBB\xBF";

        // Summary section
        $this->csvRow(['Sammanfattning']);
        $this->csvRow(['Aktiva användare', $summary['active_users']]);
        $this->csvRow(['Total tid', $this->formatTimeSv($summary['total_minutes']), $summary['total_minutes'].' min']);
        $this->csvRow(['Ordinarie tid', $this->formatTimeSv($summary['regular_minutes']), $summary['regular_minutes'].' min']);
        $this->csvRow(['Jour tid', $this->formatTimeSv($summary['oncall_minutes']), $summary['oncall_minutes'].' min']);
        $this->csvRow(['Uppgifter slutförda', $summary['tasks_completed']]);
        $this->csvRow(['Extra uppgifter', $summary['additional_tasks_completed']]);
        $this->csvRow([]);

        // Time logs section
        if ($timeLogs->isNotEmpty()) {
            $this->csvRow(['Tidsloggar']);
            $this->csvRow(['Datum', 'Användare', 'E-post', 'Station', 'Typ', 'Inklockat', 'Utklockat', 'Tid', 'Minuter', 'Anteckningar']);

            foreach ($timeLogs as $log) {
                $this->csvRow([
                    $log->date->format('Y-m-d'),
                    $log->user->name ?? '',
                    $log->user->email ?? '',
                    $log->station->name ?? '',
                    $log->is_oncall ? 'Jour' : 'Ordinarie',
                    $log->clock_in ? $log->clock_in->format('H:i') : '',
                    $log->clock_out ? $log->clock_out->format('H:i') : '',
                    $this->formatTimeSv($log->total_minutes),
                    $log->total_minutes,
                    $log->notes ?? '',
                ]);
            }
            $this->csvRow([]);
        }

        // Task completions section
        if ($taskCompletions->isNotEmpty()) {
            $this->csvRow(['Schemalagda uppgifter']);
            $this->csvRow(['Datum', 'Uppgift', 'Station', 'Slutförd av', 'Slutförd tid', 'Anteckningar']);

            foreach ($taskCompletions as $schedule) {
                $this->csvRow([
                    $schedule->scheduled_date->format('Y-m-d'),
                    $schedule->task->name ?? '',
                    $schedule->task->station->name ?? '',
                    $schedule->completedBy->name ?? '',
                    $schedule->completed_at ? $schedule->completed_at->format('H:i') : '',
                    $schedule->notes ?? '',
                ]);
            }
            $this->csvRow([]);
        }

        // Additional tasks section
        if ($additionalTasks->isNotEmpty()) {
            $this->csvRow(['Extra uppgifter']);
            $this->csvRow(['Datum', 'Uppgift', 'Station', 'Utförd av', 'Anteckningar']);

            foreach ($additionalTasks as $task) {
                $this->csvRow([
                    $task->completed_date->format('Y-m-d'),
                    $task->task_name ?? '',
                    $task->station->name ?? '',
                    $task->user->name ?? '',
                    $task->notes ?? '',
                ]);
            }
        }

        // Empty data handling
        if ($timeLogs->isEmpty() && $taskCompletions->isEmpty() && $additionalTasks->isEmpty()) {
            $this->csvRow(['Ingen data för vald period']);
        }
    }

    private function csvField($value): string
    {
        $value = (string) $value;

        if (str_contains($value, ';') ||
            str_contains($value, '"') ||
            str_contains($value, "\r") ||
            str_contains($value, "\n")) {
            return '"'.str_replace('"', '""', $value).'"';
        }

        return $value;
    }

    private function csvRow(array $fields): void
    {
        echo implode(';', array_map([$this, 'csvField'], $fields))."\r\n";
    }

    private function formatTimeSv(?int $minutes): string
    {
        return app(TimeFormatter::class)->formatMinutesSv($minutes);
    }

    private function generatePdfExport(
        array $summary,
        $timeLogs,
        $taskCompletions,
        $additionalTasks,
        string $period,
        Carbon $startDate,
        Carbon $endDate,
        ?int $userId
    ): \Illuminate\Http\Response {
        $filename = sprintf(
            'anvandaraktivitet-%s-%s-%s.pdf',
            $period,
            $startDate->toDateString(),
            $endDate->toDateString()
        );

        $periodLabel = $this->getPeriodLabel($period, $startDate);
        $userName = $userId ? User::find($userId)?->name : 'Alla användare';

        $pdf = Pdf::loadView('exports.admin-user-activity-pdf', [
            'summary' => $summary,
            'timeLogs' => $timeLogs,
            'taskCompletions' => $taskCompletions,
            'additionalTasks' => $additionalTasks,
            'periodLabel' => $periodLabel,
            'userName' => $userName,
            'generatedAt' => now()->isoFormat('D MMMM YYYY HH:mm'),
        ]);

        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
        ]);

        return $pdf->download($filename);
    }

    private function getPeriodLabel(string $period, Carbon $startDate): string
    {
        return match ($period) {
            'day' => $startDate->isoFormat('D MMMM YYYY'),
            'week' => 'Vecka '.$startDate->weekOfYear.' ('.
                      $startDate->isoFormat('D MMM').' - '.
                      $startDate->copy()->endOfWeek()->isoFormat('D MMM YYYY').')',
            'month' => $startDate->isoFormat('MMMM YYYY'),
            'year' => $startDate->format('Y'),
            default => '',
        };
    }
}
