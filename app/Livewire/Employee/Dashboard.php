<?php

namespace App\Livewire\Employee;

use App\Models\Station;
use App\Models\TaskSchedule;
use App\Models\TimeLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public $selectedDate;

    public function mount()
    {
        $this->selectedDate = now()->format('Y-m-d');
    }

    public function render()
    {
        $user = Auth::user();
        $today = Carbon::parse($this->selectedDate);
        
        // Hämta användarens stationer
        $userStations = $user->stations()->active()->get();
        
        // Hämta dagens uppgifter för användarens stationer
        $todaysTasks = TaskSchedule::whereHas('task', function ($query) use ($userStations) {
            $query->whereIn('station_id', $userStations->pluck('id'))
                  ->where('is_active', true);
        })
        ->whereDate('scheduled_date', $today)
        ->with(['task.station', 'completedBy'])
        ->orderBy('due_time')
        ->get();

        // Gruppera uppgifter per station
        $tasksByStation = $todaysTasks->groupBy('task.station_id');

        // Hämta aktiva tidslogs för användaren
        $activeTimeLogs = $user->timeLogs()
            ->active()
            ->with('station')
            ->get();

        // Statistik för användaren
        $stats = [
            'completed_today' => $todaysTasks->where('status', 'completed')
                ->where('completed_by', $user->id)->count(),
            'pending_today' => $todaysTasks->where('status', 'pending')->count(),
            'overdue_today' => $todaysTasks->where('status', 'overdue')->count(),
            'total_hours_today' => $user->timeLogs()
                ->whereDate('date', $today)
                ->completed()
                ->sum('total_minutes') / 60,
        ];

        return view('livewire.employee.dashboard', [
            'userStations' => $userStations,
            'tasksByStation' => $tasksByStation,
            'activeTimeLogs' => $activeTimeLogs,
            'stats' => $stats,
            'selectedDate' => $today,
        ]);
    }

    public function changeDate($date)
    {
        $this->selectedDate = $date;
    }

    public function clockIn($stationId)
    {
        $user = Auth::user();
        
        // Kontrollera om användaren redan är inklockat på denna station
        if ($user->hasActiveTimeLog($stationId)) {
            session()->flash('error', 'Du är redan inklockat på denna station.');
            return;
        }

        TimeLog::create([
            'user_id' => $user->id,
            'station_id' => $stationId,
            'clock_in' => now(),
            'date' => now()->format('Y-m-d'),
        ]);

        session()->flash('message', 'Inklockat framgångsrikt!');
    }

    public function clockOut($timeLogId, $notes = null)
    {
        $timeLog = TimeLog::where('id', $timeLogId)
            ->where('user_id', Auth::id())
            ->active()
            ->firstOrFail();

        $timeLog->clockOut($notes);

        session()->flash('message', 'Utklockat framgångsrikt!');
    }

    public function completeTask($taskScheduleId)
    {
        $taskSchedule = TaskSchedule::findOrFail($taskScheduleId);
        
        // Kontrollera att användaren har tillgång till stationen
        if (!Auth::user()->stations->contains($taskSchedule->task->station_id)) {
            session()->flash('error', 'Du har inte behörighet att slutföra denna uppgift.');
            return;
        }

        $taskSchedule->markAsCompleted(Auth::id());
        
        session()->flash('message', 'Uppgift markerad som slutförd!');
    }

    public function uncompleteTask($taskScheduleId)
    {
        $taskSchedule = TaskSchedule::findOrFail($taskScheduleId);
        
        // Kontrollera att användaren har tillgång till stationen
        if (!Auth::user()->stations->contains($taskSchedule->task->station_id)) {
            session()->flash('error', 'Du har inte behörighet att ändra denna uppgift.');
            return;
        }

        // Kontrollera att det är användaren som slutförde uppgiften
        if ($taskSchedule->completed_by !== Auth::id()) {
            session()->flash('error', 'Du kan bara avmarkera uppgifter som du själv har slutfört.');
            return;
        }

        $taskSchedule->status = 'pending';
        $taskSchedule->completed_at = null;
        $taskSchedule->completed_by = null;
        $taskSchedule->save();
        
        session()->flash('message', 'Uppgift avmarkerad!');
    }
}
