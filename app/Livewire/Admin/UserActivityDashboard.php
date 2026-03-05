<?php

namespace App\Livewire\Admin;

use App\Models\CompletedAdditionalTask;
use App\Models\Station;
use App\Models\TaskSchedule;
use App\Models\TimeLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class UserActivityDashboard extends Component
{
    use WithPagination;

    public string $periodType = 'day';

    public string $selectedDate;

    public ?int $selectedUserId = null;

    public ?int $selectedStationId = null;

    public string $workType = 'all';

    public string $activeTab = 'time';

    // Modal state
    public bool $showTimeLogModal = false;

    public bool $isCreating = false;

    public ?int $editingTimeLogId = null;

    // Form fields
    public ?int $formUserId = null;

    public ?int $formStationId = null;

    public string $formDate = '';

    public string $formClockIn = '';

    public string $formClockOut = '';

    public ?int $formDurationHours = null;

    public ?int $formDurationMinutes = null;

    public bool $formIsOncall = false;

    public string $formNotes = '';

    protected $queryString = [
        'periodType' => ['except' => 'day'],
        'selectedDate' => ['except' => ''],
        'selectedUserId' => ['except' => null],
        'selectedStationId' => ['except' => null],
        'workType' => ['except' => 'all'],
        'activeTab' => ['except' => 'time'],
    ];

    public function mount(): void
    {
        abort_unless(Gate::allows('admin'), 403);
        $this->selectedDate = now()->toDateString();
    }

    public function setPeriod(string $period): void
    {
        $this->periodType = $period;
        $this->resetPage();
    }

    public function previousPeriod(): void
    {
        $date = Carbon::parse($this->selectedDate);
        $this->selectedDate = match ($this->periodType) {
            'day' => $date->subDay()->toDateString(),
            'week' => $date->subWeek()->toDateString(),
            'month' => $date->subMonth()->toDateString(),
            'year' => $date->subYear()->toDateString(),
        };
        $this->resetPage();
    }

    public function nextPeriod(): void
    {
        $date = Carbon::parse($this->selectedDate);
        $this->selectedDate = match ($this->periodType) {
            'day' => $date->addDay()->toDateString(),
            'week' => $date->addWeek()->toDateString(),
            'month' => $date->addMonth()->toDateString(),
            'year' => $date->addYear()->toDateString(),
        };
        $this->resetPage();
    }

    public function currentPeriod(): void
    {
        $this->selectedDate = now()->toDateString();
        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatedSelectedUserId(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedStationId(): void
    {
        $this->resetPage();
    }

    public function updatedWorkType(): void
    {
        $this->resetPage();
    }

    public function updatedFormClockIn(): void
    {
        $this->recalculateDurationFromTimes();
    }

    public function updatedFormClockOut(): void
    {
        $this->recalculateDurationFromTimes();
    }

    public function updatedFormDurationHours(): void
    {
        $this->recalculateClockOutFromDuration();
    }

    public function updatedFormDurationMinutes(): void
    {
        $this->recalculateClockOutFromDuration();
    }

    private function recalculateDurationFromTimes(): void
    {
        if (! preg_match('/^\d{2}:\d{2}$/', $this->formClockIn) || ! preg_match('/^\d{2}:\d{2}$/', $this->formClockOut)) {
            return;
        }

        $clockIn = Carbon::createFromFormat('H:i', $this->formClockIn);
        $clockOut = Carbon::createFromFormat('H:i', $this->formClockOut);

        if ($clockOut->lessThanOrEqualTo($clockIn)) {
            $clockOut->addDay();
        }
        $totalMinutes = $clockIn->diffInMinutes($clockOut);
        $this->formDurationHours = intdiv($totalMinutes, 60);
        $this->formDurationMinutes = $totalMinutes % 60;
    }

    private function recalculateClockOutFromDuration(): void
    {
        if (! preg_match('/^\d{2}:\d{2}$/', $this->formClockIn)) {
            return;
        }

        $hours = max(0, (int) ($this->formDurationHours ?? 0));
        $minutes = max(0, (int) ($this->formDurationMinutes ?? 0));

        $clockIn = Carbon::createFromFormat('H:i', $this->formClockIn);
        $clockOut = $clockIn->copy()->addHours($hours)->addMinutes($minutes);
        $this->formClockOut = $clockOut->format('H:i');
    }

    public function editTimeLog(int $timeLogId): void
    {
        $timeLog = TimeLog::findOrFail($timeLogId);

        $this->editingTimeLogId = $timeLog->id;
        $this->formUserId = $timeLog->user_id;
        $this->formStationId = $timeLog->station_id;
        $this->formDate = $timeLog->date->format('Y-m-d');
        $this->formClockIn = $timeLog->clock_in->format('H:i');
        $this->formClockOut = $timeLog->clock_out?->format('H:i') ?? '';
        $this->formIsOncall = $timeLog->is_oncall;
        $this->formNotes = $timeLog->notes ?? '';
        $totalMinutes = $timeLog->total_minutes ?? 0;
        $this->formDurationHours = intdiv($totalMinutes, 60);
        $this->formDurationMinutes = $totalMinutes % 60;
        $this->isCreating = false;
        $this->showTimeLogModal = true;
    }

    public function createTimeLog(): void
    {
        $this->resetTimeLogForm();
        $this->formDate = $this->selectedDate;
        $this->formUserId = $this->selectedUserId;
        $this->isCreating = true;
        $this->showTimeLogModal = true;
    }

    public function saveTimeLog(): void
    {
        $this->validate([
            'formUserId' => 'required|exists:users,id',
            'formStationId' => 'required|exists:stations,id',
            'formDate' => 'required|date',
            'formClockIn' => 'required|date_format:H:i',
            'formClockOut' => 'required|date_format:H:i',
            'formIsOncall' => 'boolean',
            'formNotes' => 'nullable|string|max:500',
        ], [
            'formUserId.required' => 'Användare krävs.',
            'formUserId.exists' => 'Ogiltig användare.',
            'formStationId.required' => 'Station krävs.',
            'formStationId.exists' => 'Ogiltig station.',
            'formDate.required' => 'Datum krävs.',
            'formDate.date' => 'Ogiltigt datum.',
            'formClockIn.required' => 'Starttid krävs.',
            'formClockIn.date_format' => 'Starttid måste vara i formatet HH:MM.',
            'formClockOut.required' => 'Sluttid krävs.',
            'formClockOut.date_format' => 'Sluttid måste vara i formatet HH:MM.',
            'formNotes.max' => 'Anteckningar får vara max 500 tecken.',
        ]);

        $clockIn = Carbon::parse($this->formDate.' '.$this->formClockIn);
        $clockOut = Carbon::parse($this->formDate.' '.$this->formClockOut);
        if ($clockOut->lessThanOrEqualTo($clockIn)) {
            $clockOut->addDay();
        }
        $totalMinutes = $clockIn->diffInMinutes($clockOut);

        $data = [
            'user_id' => $this->formUserId,
            'station_id' => $this->formStationId,
            'date' => $this->formDate,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'total_minutes' => $totalMinutes,
            'is_oncall' => $this->formIsOncall,
            'notes' => $this->formNotes ?: null,
        ];

        if ($this->isCreating) {
            TimeLog::create($data);
            session()->flash('success', 'Tidslogg skapad.');
        } else {
            TimeLog::findOrFail($this->editingTimeLogId)->update($data);
            session()->flash('success', 'Tidslogg uppdaterad.');
        }

        $this->closeTimeLogModal();
    }

    public function deleteTimeLog(): void
    {
        TimeLog::findOrFail($this->editingTimeLogId)->delete();
        session()->flash('success', 'Tidslogg borttagen.');
        $this->closeTimeLogModal();
    }

    public function closeTimeLogModal(): void
    {
        $this->resetTimeLogForm();
        $this->showTimeLogModal = false;
    }

    private function resetTimeLogForm(): void
    {
        $this->editingTimeLogId = null;
        $this->formUserId = null;
        $this->formStationId = null;
        $this->formDate = '';
        $this->formClockIn = '';
        $this->formClockOut = '';
        $this->formDurationHours = null;
        $this->formDurationMinutes = null;
        $this->formIsOncall = false;
        $this->formNotes = '';
    }

    public function adminClockOut(int $timeLogId): void
    {
        $timeLog = TimeLog::findOrFail($timeLogId);
        $timeLog->clockOut('Utklockad av admin');
        session()->flash('success', 'Användaren har klockats ut.');
    }

    public function render(): View
    {
        [$startDate, $endDate] = $this->computePeriodBoundaries();

        return view('livewire.admin.user-activity-dashboard', [
            'users' => User::orderBy('name')->get(),
            'stations' => Station::active()->orderBy('name')->get(),
            'periodLabel' => $this->getPeriodLabel($startDate),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'activeTimeLogs' => $this->getActiveTimeLogs(),
            'timeLogs' => $this->getTimeLogs($startDate, $endDate),
            'timeStats' => $this->getTimeStats($startDate, $endDate),
            'userTimeBreakdown' => $this->getUserTimeBreakdown($startDate, $endDate),
            'taskCompletions' => $this->getTaskCompletions($startDate, $endDate),
            'additionalTasks' => $this->getAdditionalTasks($startDate, $endDate),
        ]);
    }

    public function exportUrl(string $format): string
    {
        return route('admin.user-activity.export', [
            'period' => $this->periodType,
            'date' => $this->selectedDate,
            'format' => $format,
            'user_id' => $this->selectedUserId,
            'station_id' => $this->selectedStationId,
            'work_type' => $this->workType,
        ]);
    }

    private function getActiveTimeLogs(): Collection
    {
        return TimeLog::query()
            ->active()
            ->with(['user', 'station'])
            ->when($this->selectedUserId, fn ($q) => $q->forUser($this->selectedUserId))
            ->when($this->selectedStationId, fn ($q) => $q->where('station_id', $this->selectedStationId))
            ->orderBy('clock_in', 'asc')
            ->get();
    }

    private function computePeriodBoundaries(): array
    {
        $date = Carbon::parse($this->selectedDate);

        return match ($this->periodType) {
            'day' => [$date->copy()->startOfDay(), $date->copy()->endOfDay()],
            'week' => [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()],
            'month' => [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()],
            'year' => [$date->copy()->startOfYear(), $date->copy()->endOfYear()],
            default => [$date->copy(), $date->copy()],
        };
    }

    private function getPeriodLabel(Carbon $startDate): string
    {
        return match ($this->periodType) {
            'day' => $startDate->isoFormat('D MMMM YYYY'),
            'week' => 'Vecka '.$startDate->weekOfYear.' ('.
                      $startDate->isoFormat('D MMM').' - '.
                      $startDate->copy()->endOfWeek()->isoFormat('D MMM YYYY').')',
            'month' => $startDate->isoFormat('MMMM YYYY'),
            'year' => $startDate->format('Y'),
            default => '',
        };
    }

    private function getTimeLogs(Carbon $startDate, Carbon $endDate): LengthAwarePaginator
    {
        $query = TimeLog::query()
            ->completed()
            ->forDateRange($startDate->toDateString(), $endDate->toDateString())
            ->with(['user', 'station'])
            ->orderBy('date', 'desc')
            ->orderBy('clock_in', 'desc');

        if ($this->selectedUserId) {
            $query->forUser($this->selectedUserId);
        }

        if ($this->selectedStationId) {
            $query->where('station_id', $this->selectedStationId);
        }

        if ($this->workType === 'regular') {
            $query->regular();
        } elseif ($this->workType === 'oncall') {
            $query->oncall();
        }

        return $query->paginate(15);
    }

    private function getTimeStats(Carbon $startDate, Carbon $endDate): array
    {
        $query = TimeLog::query()
            ->completed()
            ->forDateRange($startDate->toDateString(), $endDate->toDateString());

        if ($this->selectedUserId) {
            $query->forUser($this->selectedUserId);
        }

        if ($this->selectedStationId) {
            $query->where('station_id', $this->selectedStationId);
        }

        if ($this->workType === 'regular') {
            $query->regular();
        } elseif ($this->workType === 'oncall') {
            $query->oncall();
        }

        $timeLogs = $query->get();

        $taskCount = TaskSchedule::query()
            ->completed()
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->when($this->selectedUserId, fn ($q) => $q->where('completed_by', $this->selectedUserId))
            ->when($this->selectedStationId, fn ($q) => $q->whereHas('task', fn ($tq) => $tq->where('station_id', $this->selectedStationId)))
            ->count();

        $additionalTaskCount = CompletedAdditionalTask::query()
            ->whereBetween('completed_date', [$startDate, $endDate])
            ->when($this->selectedUserId, fn ($q) => $q->forUser($this->selectedUserId))
            ->when($this->selectedStationId, fn ($q) => $q->forStation($this->selectedStationId))
            ->count();

        return [
            'active_users' => $timeLogs->pluck('user_id')->unique()->count(),
            'total_minutes' => $timeLogs->sum('total_minutes'),
            'regular_minutes' => $timeLogs->where('is_oncall', false)->sum('total_minutes'),
            'oncall_minutes' => $timeLogs->where('is_oncall', true)->sum('total_minutes'),
            'tasks_completed' => $taskCount + $additionalTaskCount,
            'stations_active' => $timeLogs->pluck('station_id')->unique()->count(),
        ];
    }

    private function getUserTimeBreakdown(Carbon $startDate, Carbon $endDate): Collection
    {
        $query = TimeLog::query()
            ->completed()
            ->forDateRange($startDate->toDateString(), $endDate->toDateString())
            ->with(['user', 'station']);

        if ($this->selectedUserId) {
            $query->forUser($this->selectedUserId);
        }

        if ($this->selectedStationId) {
            $query->where('station_id', $this->selectedStationId);
        }

        if ($this->workType === 'regular') {
            $query->regular();
        } elseif ($this->workType === 'oncall') {
            $query->oncall();
        }

        $timeLogs = $query->get()->groupBy('user_id');
        $userIds = $timeLogs->keys();

        // Eager load task counts for all users at once (prevents N+1)
        $taskCounts = TaskSchedule::query()
            ->completed()
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->whereIn('completed_by', $userIds)
            ->when($this->selectedStationId, fn ($q) => $q->whereHas('task', fn ($tq) => $tq->where('station_id', $this->selectedStationId)))
            ->select('completed_by', DB::raw('count(*) as count'))
            ->groupBy('completed_by')
            ->pluck('count', 'completed_by');

        $additionalCounts = CompletedAdditionalTask::query()
            ->whereBetween('completed_date', [$startDate, $endDate])
            ->whereIn('user_id', $userIds)
            ->when($this->selectedStationId, fn ($q) => $q->forStation($this->selectedStationId))
            ->select('user_id', DB::raw('count(*) as count'))
            ->groupBy('user_id')
            ->pluck('count', 'user_id');

        return $timeLogs
            ->map(function ($logs, $userId) use ($taskCounts, $additionalCounts) {
                return [
                    'user' => $logs->first()->user,
                    'stations' => $logs->pluck('station.name')->unique()->filter(),
                    'regular_minutes' => $logs->where('is_oncall', false)->sum('total_minutes'),
                    'oncall_minutes' => $logs->where('is_oncall', true)->sum('total_minutes'),
                    'total_minutes' => $logs->sum('total_minutes'),
                    'tasks_completed' => ($taskCounts[$userId] ?? 0) + ($additionalCounts[$userId] ?? 0),
                ];
            })
            ->sortByDesc('total_minutes')
            ->values();
    }

    private function getTaskCompletions(Carbon $startDate, Carbon $endDate): LengthAwarePaginator
    {
        $query = TaskSchedule::query()
            ->completed()
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->with(['task.station', 'completedBy'])
            ->orderBy('completed_at', 'desc');

        if ($this->selectedUserId) {
            $query->where('completed_by', $this->selectedUserId);
        }

        if ($this->selectedStationId) {
            $query->whereHas('task', fn ($q) => $q->where('station_id', $this->selectedStationId));
        }

        return $query->paginate(15, ['*'], 'tasksPage');
    }

    private function getAdditionalTasks(Carbon $startDate, Carbon $endDate): LengthAwarePaginator
    {
        $query = CompletedAdditionalTask::query()
            ->whereBetween('completed_date', [$startDate, $endDate])
            ->with(['user', 'station'])
            ->orderBy('completed_date', 'desc');

        if ($this->selectedUserId) {
            $query->forUser($this->selectedUserId);
        }

        if ($this->selectedStationId) {
            $query->forStation($this->selectedStationId);
        }

        return $query->paginate(15, ['*'], 'additionalPage');
    }
}
