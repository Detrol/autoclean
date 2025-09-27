<?php

namespace App\Livewire\Employee;

use App\Models\CompletedAdditionalTask;
use App\Models\Station;
use App\Models\StationInventory;
use App\Models\Task;
use App\Models\TaskSchedule;
use App\Models\TaskTemplate;
use App\Models\TimeLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StationDetails extends Component
{
    public Station $station;

    public $taskComments = [];

    public $showCommentForm = [];

    // Additional task properties
    public $showAdditionalTaskForm = false;

    public $selectedTemplateId = null;

    public $customTaskName = '';

    public $additionalTaskNotes = '';

    public function mount($id)
    {
        $user = Auth::user();

        // Hämta station och kontrollera att användaren har åtkomst
        $this->station = Station::findOrFail($id);

        if (! $user->stations->contains($this->station)) {
            abort(403, 'Du har inte åtkomst till denna station.');
        }
    }

    public function render()
    {
        $user = Auth::user();

        // Hämta alla uppgifter för stationen
        $tasks = Task::where('station_id', $this->station->id)
            ->where('is_active', true)
            ->with(['schedules' => function ($query) {
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

        // Hämta dagens additional tasks
        $todayAdditionalTasks = CompletedAdditionalTask::forStation($this->station->id)
            ->forDate(now())
            ->with(['user', 'taskTemplate'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Hämta task templates
        $taskTemplates = TaskTemplate::active()
            ->orderBy('name')
            ->get();

        // Hämta lagerstatus för stationen
        $lowStockItems = StationInventory::where('station_id', $this->station->id)
            ->with('inventoryItem')
            ->lowStock()
            ->get();

        $inventoryItems = StationInventory::where('station_id', $this->station->id)
            ->with('inventoryItem')
            ->orderBy('current_quantity', 'asc')
            ->take(10)
            ->get();

        return view('livewire.employee.station-details', [
            'taskData' => $taskData,
            'isLoggedIn' => $isLoggedIn,
            'recentTimeLogs' => $recentTimeLogs,
            'todayAdditionalTasks' => $todayAdditionalTasks,
            'taskTemplates' => $taskTemplates,
            'lowStockItems' => $lowStockItems,
            'inventoryItems' => $inventoryItems,
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

        // Kontrollera att användaren är inklockat på denna station (gäller inte admins)
        if (! $user->is_admin && ! $user->hasActiveTimeLog($this->station->id)) {
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

        // Kontrollera att användaren är inklockat på denna station (gäller inte admins)
        if (! $user->is_admin && ! $user->hasActiveTimeLog($this->station->id)) {
            session()->flash('error', 'Du måste vara inklockat på stationen för att avmarkera uppgifter.');

            return;
        }

        // Kontrollera att det är användaren som slutförde uppgiften (admins kan avmarkera alla)
        if (! $user->is_admin && $taskSchedule->completed_by !== Auth::id()) {
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

    public function toggleCommentForm($taskScheduleId)
    {
        $this->showCommentForm[$taskScheduleId] = ! ($this->showCommentForm[$taskScheduleId] ?? false);

        // Load existing comment if not already loaded
        if (! isset($this->taskComments[$taskScheduleId])) {
            $taskSchedule = TaskSchedule::find($taskScheduleId);
            $this->taskComments[$taskScheduleId] = $taskSchedule->notes ?? '';
        }
    }

    public function saveTaskComment($taskScheduleId)
    {
        $taskSchedule = TaskSchedule::findOrFail($taskScheduleId);
        $user = Auth::user();

        // Kontrollera att användaren har tillgång till stationen (admins har alltid tillgång)
        if (! $user->is_admin && ! $user->stations->contains($this->station)) {
            session()->flash('error', 'Du har inte behörighet att kommentera denna uppgift.');

            return;
        }

        $taskSchedule->update([
            'notes' => $this->taskComments[$taskScheduleId] ?? null,
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

    public function showAddAdditionalTaskForm()
    {
        $user = Auth::user();

        // Kontrollera att användaren är inklockat
        if (! $user->is_admin && ! $user->hasActiveTimeLog($this->station->id)) {
            session()->flash('error', 'Du måste vara inklockat på stationen för att lägga till extra uppgifter.');

            return;
        }

        $this->showAdditionalTaskForm = true;
        $this->selectedTemplateId = null;
        $this->customTaskName = '';
        $this->additionalTaskNotes = '';
    }

    public function hideAddAdditionalTaskForm()
    {
        $this->showAdditionalTaskForm = false;
        $this->selectedTemplateId = null;
        $this->customTaskName = '';
        $this->additionalTaskNotes = '';
    }

    public function saveAdditionalTask()
    {
        $user = Auth::user();

        // Kontrollera att användaren är inklockat
        if (! $user->is_admin && ! $user->hasActiveTimeLog($this->station->id)) {
            session()->flash('error', 'Du måste vara inklockat på stationen för att lägga till extra uppgifter.');

            return;
        }

        // Bestäm task name
        $taskName = '';
        if ($this->selectedTemplateId) {
            $template = TaskTemplate::find($this->selectedTemplateId);
            $taskName = $template ? $template->name : '';
        } else {
            $taskName = trim($this->customTaskName);
        }

        // Validera att vi har ett task name
        if (empty($taskName)) {
            session()->flash('error', 'Du måste välja en mall eller ange ett anpassat namn för uppgiften.');

            return;
        }

        // Spara additional task
        CompletedAdditionalTask::create([
            'station_id' => $this->station->id,
            'user_id' => $user->id,
            'task_template_id' => $this->selectedTemplateId ?: null,
            'task_name' => $taskName,
            'completed_date' => now()->format('Y-m-d'),
            'notes' => $this->additionalTaskNotes ?: null,
        ]);

        $this->hideAddAdditionalTaskForm();
        session()->flash('message', "Extra uppgift '{$taskName}' har lagts till!");
    }
}
