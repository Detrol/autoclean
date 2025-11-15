<?php

namespace App\Livewire\Employee;

use App\Models\CompletedAdditionalTask;
use App\Models\Station;
use App\Models\TaskSchedule;
use App\Models\TaskTemplate;
use App\Models\TimeLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StationTasks extends Component
{
    public Station $station;

    public $selectedDate;

    public $taskComments = [];

    public $showCommentForm = [];

    public $showAdditionalTaskForm = false;

    public $selectedTemplateId = null;

    public $customTaskName = '';

    public $additionalTaskNotes = '';

    public function mount($id)
    {
        $user = Auth::user();

        $this->station = Station::findOrFail($id);

        if (! $user->stations->contains($this->station)) {
            abort(403, 'Du har inte åtkomst till denna station.');
        }

        $this->selectedDate = now()->format('Y-m-d');
    }

    public function render()
    {
        $user = Auth::user();
        $today = Carbon::parse($this->selectedDate);

        $todaysTasks = TaskSchedule::whereHas('task', function ($query) {
            $query->where('station_id', $this->station->id)
                ->where('is_active', true);
        })
            ->whereDate('scheduled_date', $today)
            ->with(['task', 'completedBy'])
            ->orderBy('due_time')
            ->get();

        $activeTimeLogs = $user->timeLogs()
            ->where('station_id', $this->station->id)
            ->active()
            ->with('station')
            ->get();

        $completedTimeLogs = $user->timeLogs()
            ->where('station_id', $this->station->id)
            ->whereDate('date', $today)
            ->completed()
            ->with('station')
            ->orderBy('clock_in', 'desc')
            ->get();

        $taskTemplates = TaskTemplate::active()
            ->orderBy('name')
            ->get();

        $todayAdditionalTasks = CompletedAdditionalTask::forStation($this->station->id)
            ->forDate($today)
            ->with(['user', 'taskTemplate'])
            ->orderBy('created_at', 'desc')
            ->get();

        $isLoggedIn = $user->hasActiveTimeLog($this->station->id);
        $adminRequiresClockIn = settings('admin_requires_clock_in', false);
        $requiresClockIn = ! $user->is_admin || $adminRequiresClockIn;

        return view('livewire.employee.station-tasks', [
            'todaysTasks' => $todaysTasks,
            'activeTimeLogs' => $activeTimeLogs,
            'completedTimeLogs' => $completedTimeLogs,
            'taskTemplates' => $taskTemplates,
            'todayAdditionalTasks' => $todayAdditionalTasks,
            'isLoggedIn' => $isLoggedIn,
            'requiresClockIn' => $requiresClockIn,
            'selectedDate' => $today,
        ]);
    }

    public function clockIn($isOncall = false)
    {
        $user = Auth::user();

        if ($user->hasActiveTimeLog($this->station->id)) {
            session()->flash('error', 'Du är redan inklockat på denna station.');

            return;
        }

        TimeLog::create([
            'user_id' => $user->id,
            'station_id' => $this->station->id,
            'is_oncall' => $isOncall,
            'clock_in' => now(),
            'date' => now()->format('Y-m-d'),
        ]);

        $type = $isOncall ? 'jour' : 'ordinarie arbetstid';
        session()->flash('message', "Inklockat framgångsrikt för {$type}!");
    }

    public function clockInOncall()
    {
        $this->clockIn(true);
    }

    public function clockOut($timeLogId)
    {
        $timeLog = TimeLog::where('id', $timeLogId)
            ->where('user_id', Auth::id())
            ->active()
            ->firstOrFail();

        $timeLog->clockOut();

        session()->flash('message', 'Utklockat framgångsrikt!');
    }

    public function completeTask($taskScheduleId)
    {
        $taskSchedule = TaskSchedule::findOrFail($taskScheduleId);
        $user = Auth::user();

        if (! $user->is_admin && ! $user->stations->contains($this->station->id)) {
            session()->flash('error', 'Du har inte behörighet att slutföra denna uppgift.');

            return;
        }

        // Kontrollera att användaren är inklockat på denna station
        // Admins kan bypassa detta krav om admin_requires_clock_in är false
        $adminRequiresClockIn = settings('admin_requires_clock_in', false);
        $requiresClockIn = ! $user->is_admin || $adminRequiresClockIn;

        if ($requiresClockIn && ! $user->hasActiveTimeLog($this->station->id)) {
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

        if (! $user->is_admin && ! $user->stations->contains($this->station->id)) {
            session()->flash('error', 'Du har inte behörighet att ändra denna uppgift.');

            return;
        }

        // Kontrollera att användaren är inklockat på denna station
        // Admins kan bypassa detta krav om admin_requires_clock_in är false
        $adminRequiresClockIn = settings('admin_requires_clock_in', false);
        $requiresClockIn = ! $user->is_admin || $adminRequiresClockIn;

        if ($requiresClockIn && ! $user->hasActiveTimeLog($this->station->id)) {
            session()->flash('error', 'Du måste vara inklockat på stationen för att ändra uppgifter.');

            return;
        }

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

    public function toggleCommentForm($taskScheduleId)
    {
        $this->showCommentForm[$taskScheduleId] = ! ($this->showCommentForm[$taskScheduleId] ?? false);

        if (! isset($this->taskComments[$taskScheduleId])) {
            $taskSchedule = TaskSchedule::find($taskScheduleId);
            $this->taskComments[$taskScheduleId] = $taskSchedule->notes ?? '';
        }
    }

    public function saveTaskComment($taskScheduleId)
    {
        $taskSchedule = TaskSchedule::findOrFail($taskScheduleId);
        $user = Auth::user();

        if (! $user->is_admin && ! $user->stations->contains($this->station->id)) {
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
        $taskSchedule = TaskSchedule::find($taskScheduleId);
        $this->taskComments[$taskScheduleId] = $taskSchedule->notes ?? '';
    }

    public function showAddAdditionalTaskForm()
    {
        $user = Auth::user();

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

        if (! $user->is_admin && ! $user->hasActiveTimeLog($this->station->id)) {
            session()->flash('error', 'Du måste vara inklockat på stationen för att lägga till extra uppgifter.');

            return;
        }

        $taskName = '';
        if ($this->selectedTemplateId) {
            $template = TaskTemplate::find($this->selectedTemplateId);
            $taskName = $template ? $template->name : '';
        } else {
            $taskName = trim($this->customTaskName);
        }

        if (empty($taskName)) {
            session()->flash('error', 'Du måste välja en mall eller ange ett anpassat namn för uppgiften.');

            return;
        }

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
