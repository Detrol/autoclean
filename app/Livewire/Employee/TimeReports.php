<?php

namespace App\Livewire\Employee;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TimeReports extends Component
{
    public $periodType = 'week'; // day, week, month, year

    public $selectedDate;

    public $startDate;

    public $endDate;

    public function mount()
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->updateDateRange();
    }

    public function render()
    {
        $user = Auth::user();

        // Hämta tidslogs baserat på vald period
        $timeLogs = $user->timeLogs()
            ->with('station')
            ->completed();

        // Applicera datumfilter baserat på period
        switch ($this->periodType) {
            case 'day':
                $timeLogs->forDate($this->selectedDate);
                break;
            case 'week':
                $timeLogs->forWeek($this->selectedDate);
                break;
            case 'month':
                $timeLogs->forMonth($this->selectedDate);
                break;
            case 'year':
                $timeLogs->forYear($this->selectedDate);
                break;
        }

        $timeLogs = $timeLogs->orderBy('date', 'desc')
            ->orderBy('clock_in', 'desc')
            ->get();

        // Beräkna statistik
        $regularLogs = $timeLogs->where('is_oncall', false);
        $oncallLogs = $timeLogs->where('is_oncall', true);

        $stats = [
            'total_minutes' => $timeLogs->sum('total_minutes'),
            'regular_minutes' => $regularLogs->sum('total_minutes'),
            'oncall_minutes' => $oncallLogs->sum('total_minutes'),
            'days_worked' => $timeLogs->pluck('date')->unique()->count(),
            'regular_days' => $regularLogs->pluck('date')->unique()->count(),
            'oncall_days' => $oncallLogs->pluck('date')->unique()->count(),
        ];

        // Minuter per station
        $minutesByStation = $timeLogs->groupBy('station.name')
            ->map(function ($logs) {
                return [
                    'regular_minutes' => $logs->where('is_oncall', false)->sum('total_minutes'),
                    'oncall_minutes' => $logs->where('is_oncall', true)->sum('total_minutes'),
                    'total_minutes' => $logs->sum('total_minutes'),
                ];
            });

        return view('livewire.employee.time-reports', [
            'timeLogs' => $timeLogs,
            'stats' => $stats,
            'minutesByStation' => $minutesByStation,
            'periodLabel' => $this->getPeriodLabel(),
        ]);
    }

    public function setPeriod($period)
    {
        $this->periodType = $period;
        $this->updateDateRange();
    }

    public function previousPeriod()
    {
        $date = Carbon::parse($this->selectedDate);

        switch ($this->periodType) {
            case 'day':
                $this->selectedDate = $date->subDay()->format('Y-m-d');
                break;
            case 'week':
                $this->selectedDate = $date->subWeek()->format('Y-m-d');
                break;
            case 'month':
                $this->selectedDate = $date->subMonth()->format('Y-m-d');
                break;
            case 'year':
                $this->selectedDate = $date->subYear()->format('Y-m-d');
                break;
        }

        $this->updateDateRange();
    }

    public function nextPeriod()
    {
        $date = Carbon::parse($this->selectedDate);

        // Förhindra navigation till framtiden
        if ($date->isFuture()) {
            return;
        }

        switch ($this->periodType) {
            case 'day':
                $newDate = $date->addDay();
                break;
            case 'week':
                $newDate = $date->addWeek();
                break;
            case 'month':
                $newDate = $date->addMonth();
                break;
            case 'year':
                $newDate = $date->addYear();
                break;
            default:
                $newDate = $date;
        }

        // Kontrollera att vi inte går förbi idag
        if ($newDate->isAfter(now())) {
            $this->selectedDate = now()->format('Y-m-d');
        } else {
            $this->selectedDate = $newDate->format('Y-m-d');
        }

        $this->updateDateRange();
    }

    public function currentPeriod()
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->updateDateRange();
    }

    private function updateDateRange()
    {
        $date = Carbon::parse($this->selectedDate);

        switch ($this->periodType) {
            case 'day':
                $this->startDate = $date->format('Y-m-d');
                $this->endDate = $date->format('Y-m-d');
                break;
            case 'week':
                $this->startDate = $date->startOfWeek()->format('Y-m-d');
                $this->endDate = $date->endOfWeek()->format('Y-m-d');
                break;
            case 'month':
                $this->startDate = $date->startOfMonth()->format('Y-m-d');
                $this->endDate = $date->endOfMonth()->format('Y-m-d');
                break;
            case 'year':
                $this->startDate = $date->startOfYear()->format('Y-m-d');
                $this->endDate = $date->endOfYear()->format('Y-m-d');
                break;
        }
    }

    private function getPeriodLabel()
    {
        $date = Carbon::parse($this->selectedDate);

        switch ($this->periodType) {
            case 'day':
                return $date->isoFormat('D MMMM YYYY');
            case 'week':
                $start = $date->startOfWeek();
                $end = $date->endOfWeek();

                return 'Vecka '.$date->weekOfYear.' ('.$start->isoFormat('D MMM').' - '.$end->isoFormat('D MMM YYYY').')';
            case 'month':
                return $date->isoFormat('MMMM YYYY');
            case 'year':
                return $date->format('Y');
            default:
                return '';
        }
    }
}
