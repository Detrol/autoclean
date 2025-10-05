<?php

namespace App\Livewire\Employee;

use App\Models\CompletedAdditionalTask;
use App\Models\Station;
use App\Models\StationInventory;
use App\Models\TaskSchedule;
use App\Models\TaskTemplate;
use App\Models\TimeLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public $selectedDate;
    public $taskComments = [];
    public $showCommentForm = [];
    
    // Additional task properties
    public $showAdditionalTaskForm = [];
    public $selectedTemplateId = [];
    public $customTaskName = [];
    public $additionalTaskNotes = [];

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
        
        // Hämta dagens avslutade tidslogs
        $completedTimeLogs = $user->timeLogs()
            ->whereDate('date', $today)
            ->completed()
            ->with('station')
            ->orderBy('clock_in', 'desc')
            ->get();

        // Statistik för användaren
        $stats = [
            'completed_today' => $todaysTasks->where('status', 'completed')
                ->where('completed_by', $user->id)->count(),
            'pending_today' => $todaysTasks->where('status', 'pending')->count(),
            'overdue_today' => $todaysTasks->where('status', 'overdue')->count(),
            'total_minutes_today' => max(0, $user->timeLogs()
                ->whereDate('date', $today)
                ->completed()
                ->sum('total_minutes')),
        ];

        // Hämta task templates för alla stationer
        $taskTemplates = TaskTemplate::active()
            ->orderBy('name')
            ->get();

        // Hämta dagens additional tasks för alla stationer
        $todayAdditionalTasks = CompletedAdditionalTask::whereIn('station_id', $userStations->pluck('id'))
            ->forDate($today)
            ->with(['user', 'taskTemplate', 'station'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('station_id');

        // Hämta kritiska lagervarningar för användarens stationer
        $criticalInventoryAlerts = StationInventory::whereIn('station_id', $userStations->pluck('id'))
            ->with(['inventoryItem', 'station'])
            ->whereRaw('current_quantity <= 0 OR current_quantity <= minimum_quantity')
            ->orderBy('current_quantity', 'asc')
            ->take(10)
            ->get();

        return view('livewire.employee.dashboard', [
            'userStations' => $userStations,
            'tasksByStation' => $tasksByStation,
            'activeTimeLogs' => $activeTimeLogs,
            'completedTimeLogs' => $completedTimeLogs,
            'stats' => $stats,
            'selectedDate' => $today,
            'taskTemplates' => $taskTemplates,
            'todayAdditionalTasks' => $todayAdditionalTasks,
            'criticalInventoryAlerts' => $criticalInventoryAlerts,
        ]);
    }

    public function changeDate($date)
    {
        $this->selectedDate = $date;
    }


    public function completeTask($taskScheduleId)
    {
        $taskSchedule = TaskSchedule::findOrFail($taskScheduleId);
        $user = Auth::user();
        $stationId = $taskSchedule->task->station_id;
        
        // Kontrollera att användaren har tillgång till stationen (admins har alltid tillgång)
        if (!$user->is_admin && !$user->stations->contains($stationId)) {
            session()->flash('error', 'Du har inte behörighet att slutföra denna uppgift.');
            return;
        }

        $taskSchedule->markAsCompleted(Auth::id());
        
        session()->flash('message', 'Uppgift markerad som slutförd!');
    }

    public function uncompleteTask($taskScheduleId)
    {
        $taskSchedule = TaskSchedule::findOrFail($taskScheduleId);
        $user = Auth::user();
        $stationId = $taskSchedule->task->station_id;
        
        // Kontrollera att användaren har tillgång till stationen (admins har alltid tillgång)
        if (!$user->is_admin && !$user->stations->contains($stationId)) {
            session()->flash('error', 'Du har inte behörighet att ändra denna uppgift.');
            return;
        }

        // Kontrollera att det är användaren som slutförde uppgiften (admins kan avmarkera alla)
        if (!$user->is_admin && $taskSchedule->completed_by !== Auth::id()) {
            session()->flash('error', 'Du kan bara avmarkera uppgifter som du själv har slutfört.');
            return;
        }

        $taskSchedule->status = 'pending';
        $taskSchedule->completed_at = null;
        $taskSchedule->completed_by = null;
        $taskSchedule->save();
        
        session()->flash('message', 'Uppgift avmarkerad!');
    }

    public function toggleCommentForm($taskScheduleId)
    {
        $this->showCommentForm[$taskScheduleId] = !($this->showCommentForm[$taskScheduleId] ?? false);
        
        // Load existing comment if not already loaded
        if (!isset($this->taskComments[$taskScheduleId])) {
            $taskSchedule = TaskSchedule::find($taskScheduleId);
            $this->taskComments[$taskScheduleId] = $taskSchedule->notes ?? '';
        }
    }

    public function saveTaskComment($taskScheduleId)
    {
        $taskSchedule = TaskSchedule::findOrFail($taskScheduleId);
        $user = Auth::user();
        $stationId = $taskSchedule->task->station_id;
        
        // Kontrollera att användaren har tillgång till stationen (admins har alltid tillgång)
        if (!$user->is_admin && !$user->stations->contains($stationId)) {
            session()->flash('error', 'Du har inte behörighet att kommentera denna uppgift.');
            return;
        }
        
        $taskSchedule->update([
            'notes' => $this->taskComments[$taskScheduleId] ?? null
        ]);
        
        $this->showCommentForm[$taskScheduleId] = false;
        session()->flash('message', 'Kommentar sparad!');
    }

    public function cancelComment($taskScheduleId)
    {
        $this->showCommentForm[$taskScheduleId] = false;
        // Reset to original value
        $taskSchedule = TaskSchedule::find($taskScheduleId);
        $this->taskComments[$taskScheduleId] = $taskSchedule->notes ?? '';
    }

    public function showAddAdditionalTaskForm($stationId)
    {
        $user = Auth::user();
        
        // Kontrollera att användaren är inklockat på stationen
        if (!$user->is_admin && !$user->hasActiveTimeLog($stationId)) {
            session()->flash('error', 'Du måste vara inklockat på stationen för att lägga till extra uppgifter.');
            return;
        }

        $this->showAdditionalTaskForm[$stationId] = true;
        $this->selectedTemplateId[$stationId] = null;
        $this->customTaskName[$stationId] = '';
        $this->additionalTaskNotes[$stationId] = '';
    }

    public function hideAddAdditionalTaskForm($stationId)
    {
        $this->showAdditionalTaskForm[$stationId] = false;
        $this->selectedTemplateId[$stationId] = null;
        $this->customTaskName[$stationId] = '';
        $this->additionalTaskNotes[$stationId] = '';
    }

    public function saveAdditionalTask($stationId)
    {
        $user = Auth::user();
        
        // Kontrollera att användaren är inklockat på stationen
        if (!$user->is_admin && !$user->hasActiveTimeLog($stationId)) {
            session()->flash('error', 'Du måste vara inklockat på stationen för att lägga till extra uppgifter.');
            return;
        }

        // Bestäm task name
        $taskName = '';
        if ($this->selectedTemplateId[$stationId] ?? null) {
            $template = TaskTemplate::find($this->selectedTemplateId[$stationId]);
            $taskName = $template ? $template->name : '';
        } else {
            $taskName = trim($this->customTaskName[$stationId] ?? '');
        }

        // Validera att vi har ett task name
        if (empty($taskName)) {
            session()->flash('error', 'Du måste välja en mall eller ange ett anpassat namn för uppgiften.');
            return;
        }

        // Spara additional task
        CompletedAdditionalTask::create([
            'station_id' => $stationId,
            'user_id' => $user->id,
            'task_template_id' => ($this->selectedTemplateId[$stationId] ?? null) ?: null,
            'task_name' => $taskName,
            'completed_date' => now()->format('Y-m-d'),
            'notes' => ($this->additionalTaskNotes[$stationId] ?? null) ?: null,
        ]);

        $this->hideAddAdditionalTaskForm($stationId);
        session()->flash('message', "Extra uppgift '{$taskName}' har lagts till!");
    }
}
