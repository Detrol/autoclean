<?php

namespace App\Livewire\Employee;

use App\Models\Station;
use App\Models\Task;
use App\Models\TaskSchedule;
use App\Models\TimeLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StationDetails extends Component
{
    public Station $station;

    public function mount($id)
    {
        $user = Auth::user();
        
        // Hämta station och kontrollera att användaren har åtkomst
        $this->station = Station::findOrFail($id);
        
        if (!$user->stations->contains($this->station)) {
            abort(403, 'Du har inte åtkomst till denna station.');
        }
    }

    public function render()
    {
        $user = Auth::user();
        
        // Hämta alla uppgifter för stationen
        $tasks = Task::where('station_id', $this->station->id)
            ->where('is_active', true)
            ->with(['schedules' => function($query) {
                $query->orderBy('scheduled_date', 'desc');
            }])
            ->get();

        // Bearbeta data för varje uppgift
        $taskData = $tasks->map(function ($task) {
            // Senaste utförda uppgift
            $lastCompleted = TaskSchedule::where('task_id', $task->id)
                ->where('status', 'completed')
                ->with('completedBy')
                ->orderBy('completed_at', 'desc')
                ->first();

            // Nästa schemalagda uppgift
            $nextScheduled = TaskSchedule::where('task_id', $task->id)
                ->where('status', 'pending')
                ->whereDate('scheduled_date', '>=', now())
                ->orderBy('scheduled_date', 'asc')
                ->first();

            // Dagens uppgift om den finns
            $todayTask = TaskSchedule::where('task_id', $task->id)
                ->whereDate('scheduled_date', now())
                ->first();

            // Räkna totalt antal genomförda
            $completedCount = TaskSchedule::where('task_id', $task->id)
                ->where('status', 'completed')
                ->count();

            return [
                'task' => $task,
                'last_completed' => $lastCompleted,
                'next_scheduled' => $nextScheduled,
                'today_task' => $todayTask,
                'completed_count' => $completedCount,
                'interval_text' => $this->getIntervalText($task),
            ];
        });

        // Kontrollera om användaren är inklockat
        $isLoggedIn = $user->hasActiveTimeLog($this->station->id);
        
        // Hämta senaste tidslogs
        $recentTimeLogs = TimeLog::where('station_id', $this->station->id)
            ->whereNotNull('clock_out')
            ->with('user')
            ->orderBy('clock_out', 'desc')
            ->take(5)
            ->get();

        return view('livewire.employee.station-details', [
            'taskData' => $taskData,
            'isLoggedIn' => $isLoggedIn,
            'recentTimeLogs' => $recentTimeLogs,
        ]);
    }

    public function clockIn()
    {
        $user = Auth::user();
        
        if ($user->hasActiveTimeLog($this->station->id)) {
            session()->flash('error', 'Du är redan inklockat på denna station.');
            return;
        }

        TimeLog::create([
            'user_id' => $user->id,
            'station_id' => $this->station->id,
            'clock_in' => now(),
            'date' => now()->format('Y-m-d'),
        ]);

        session()->flash('message', 'Inklockat framgångsrikt!');
    }

    public function completeTask($taskScheduleId)
    {
        $taskSchedule = TaskSchedule::findOrFail($taskScheduleId);
        $user = Auth::user();
        
        // Kontrollera att användaren är inklockat på denna station
        if (!$user->hasActiveTimeLog($this->station->id)) {
            session()->flash('error', 'Du måste vara inklockat på stationen för att markera uppgifter som slutförda.');
            return;
        }

        $taskSchedule->markAsCompleted(Auth::id());
        
        session()->flash('message', 'Uppgift markerad som slutförd!');
    }

    public function uncompleteTask($taskScheduleId)
    {
        $taskSchedule = TaskSchedule::findOrFail($taskScheduleId);
        $user = Auth::user();
        
        // Kontrollera att användaren är inklockat på denna station
        if (!$user->hasActiveTimeLog($this->station->id)) {
            session()->flash('error', 'Du måste vara inklockat på stationen för att avmarkera uppgifter.');
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

    private function getIntervalText($task)
    {
        switch ($task->interval_type) {
            case 'daily':
                return $task->interval_value == 1 
                    ? 'Dagligen' 
                    : "Varje {$task->interval_value} dagar";
            case 'weekly':
                return $task->interval_value == 1 
                    ? 'Varje vecka' 
                    : "Varje {$task->interval_value} veckor";
            case 'monthly':
                return $task->interval_value == 1 
                    ? 'Varje månad' 
                    : "Varje {$task->interval_value} månader";
            case 'custom':
                return "Varje {$task->interval_value} dagar (anpassat)";
            default:
                return 'Okänt intervall';
        }
    }
}