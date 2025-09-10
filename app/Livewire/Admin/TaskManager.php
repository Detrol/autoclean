<?php

namespace App\Livewire\Admin;

use App\Models\Station;
use App\Models\Task;
use App\Services\RecurrenceCalculator;
use Livewire\Component;
use Livewire\WithPagination;

class TaskManager extends Component
{
    use WithPagination;

    public $station_id = '';
    public $name = '';
    public $description = '';
    public $interval_type = 'daily';
    public $interval_value = 1;
    public $start_date = '';
    public $end_date = '';
    public $occurrences = null;
    public $is_active = true;
    public $editingTaskId = null;
    public $showCreateForm = false;
    public $selectedStationFilter = '';
    
    // Intervallspecifika fält
    public $weekdays_only = false;
    public $selected_weekdays = [];
    public $monthly_type = 'date'; // 'date' eller 'weekday'
    public $monthly_date = 1;
    public $monthly_weekday_ordinal = 1; // 1-5
    public $monthly_weekday = 'monday';
    public $end_type = 'never'; // 'never', 'date', 'occurrences'
    
    // För förhandsvisning
    public $preview_dates = [];

    protected $rules = [
        'station_id' => 'required|exists:stations,id',
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'interval_type' => 'required|in:daily,weekly,monthly,yearly,custom',
        'interval_value' => 'required|integer|min:1',
        'start_date' => 'nullable|date|after_or_equal:today',
        'end_date' => 'nullable|date|after:start_date',
        'occurrences' => 'nullable|integer|min:1',
        'is_active' => 'boolean',
    ];

    public function render()
    {
        $query = Task::with('station');
        
        if ($this->selectedStationFilter) {
            $query->where('station_id', $this->selectedStationFilter);
        }

        return view('livewire.admin.task-manager', [
            'tasks' => $query->paginate(10),
            'stations' => Station::active()->get(),
        ]);
    }

    public function create()
    {
        $this->validate();

        Task::create([
            'station_id' => $this->station_id,
            'name' => $this->name,
            'description' => $this->description,
            'interval_type' => $this->interval_type,
            'interval_value' => $this->interval_value,
            'start_date' => $this->start_date ?: null,
            'recurrence_pattern' => $this->buildRecurrencePattern(),
            'end_date' => $this->end_type === 'date' ? $this->end_date : null,
            'occurrences' => $this->end_type === 'occurrences' ? $this->occurrences : null,
            'default_due_time' => '23:59',
            'is_active' => $this->is_active,
        ]);

        $this->resetForm();
        $this->showCreateForm = false;

        session()->flash('message', 'Uppgift skapad framgångsrikt!');
    }

    public function edit($taskId)
    {
        $task = Task::findOrFail($taskId);
        
        $this->editingTaskId = $taskId;
        $this->station_id = $task->station_id;
        $this->name = $task->name;
        $this->description = $task->description;
        $this->interval_type = $task->interval_type;
        $this->interval_value = $task->interval_value;
        $this->start_date = $task->start_date ? $task->start_date->format('Y-m-d') : '';
        $this->end_date = $task->end_date ? $task->end_date->format('Y-m-d') : '';
        $this->occurrences = $task->occurrences;
        $this->is_active = $task->is_active;
        
        // Sätt end_type baserat på befintliga värden
        if ($task->end_date) {
            $this->end_type = 'date';
        } elseif ($task->occurrences) {
            $this->end_type = 'occurrences';
        } else {
            $this->end_type = 'never';
        }
        
        // Ladda återkommande mönster
        $this->loadRecurrencePattern($task->recurrence_pattern);
    }

    public function update()
    {
        $this->validate();

        $task = Task::findOrFail($this->editingTaskId);
        
        $task->update([
            'station_id' => $this->station_id,
            'name' => $this->name,
            'description' => $this->description,
            'interval_type' => $this->interval_type,
            'interval_value' => $this->interval_value,
            'start_date' => $this->start_date ?: null,
            'recurrence_pattern' => $this->buildRecurrencePattern(),
            'end_date' => $this->end_type === 'date' ? $this->end_date : null,
            'occurrences' => $this->end_type === 'occurrences' ? $this->occurrences : null,
            'default_due_time' => '23:59',
            'is_active' => $this->is_active,
        ]);

        $this->resetForm();

        session()->flash('message', 'Uppgift uppdaterad framgångsrikt!');
    }

    public function delete($taskId)
    {
        $task = Task::findOrFail($taskId);
        
        // Kontrollera om uppgiften har schemalagda instanser
        if ($task->schedules()->exists()) {
            session()->flash('error', 'Kan inte ta bort uppgift som har schemalagda instanser.');
            return;
        }

        $task->delete();
        
        session()->flash('message', 'Uppgift borttagen framgångsrikt!');
    }

    public function cancelEdit()
    {
        $this->resetForm();
    }

    public function toggleCreateForm()
    {
        $this->showCreateForm = !$this->showCreateForm;
        if (!$this->showCreateForm) {
            $this->resetForm();
        }
    }

    public function updatedSelectedStationFilter()
    {
        $this->resetPage();
    }

    private function resetForm()
    {
        $this->reset([
            'station_id', 'name', 'description', 'interval_type', 'interval_value', 
            'start_date', 'end_date', 'occurrences', 'editingTaskId',
            'weekdays_only', 'selected_weekdays', 'monthly_type', 'monthly_date',
            'monthly_weekday_ordinal', 'monthly_weekday', 'end_type', 'preview_dates'
        ]);
        $this->is_active = true;
        $this->interval_type = 'daily';
        $this->interval_value = 1;
        $this->monthly_date = 1;
        $this->monthly_weekday_ordinal = 1;
        $this->monthly_weekday = 'monday';
        $this->end_type = 'never';
    }
    
    /**
     * Bygg återkommande mönster baserat på formulärdata
     */
    private function buildRecurrencePattern(): ?array
    {
        switch ($this->interval_type) {
            case 'daily':
                if ($this->weekdays_only) {
                    return ['weekdaysOnly' => true];
                }
                return null;
                
            case 'weekly':
                if (!empty($this->selected_weekdays)) {
                    return ['daysOfWeek' => $this->selected_weekdays];
                }
                return null;
                
            case 'monthly':
                if ($this->monthly_type === 'date') {
                    return ['dayOfMonth' => $this->monthly_date];
                } else {
                    return [
                        'weekdayOfMonth' => [
                            'ordinal' => $this->monthly_weekday_ordinal,
                            'day' => $this->monthly_weekday
                        ]
                    ];
                }
                
            case 'yearly':
                return null; // Använder startdatum
                
            case 'custom':
                // För framtida utökning
                return null;
                
            default:
                return null;
        }
    }
    
    /**
     * Ladda återkommande mönster till formulärfält
     */
    private function loadRecurrencePattern(?array $pattern): void
    {
        if (!$pattern) {
            return;
        }
        
        switch ($this->interval_type) {
            case 'daily':
                $this->weekdays_only = $pattern['weekdaysOnly'] ?? false;
                break;
                
            case 'weekly':
                $this->selected_weekdays = $pattern['daysOfWeek'] ?? [];
                break;
                
            case 'monthly':
                if (isset($pattern['dayOfMonth'])) {
                    $this->monthly_type = 'date';
                    $this->monthly_date = $pattern['dayOfMonth'];
                } elseif (isset($pattern['weekdayOfMonth'])) {
                    $this->monthly_type = 'weekday';
                    $this->monthly_weekday_ordinal = $pattern['weekdayOfMonth']['ordinal'];
                    $this->monthly_weekday = $pattern['weekdayOfMonth']['day'];
                }
                break;
        }
    }
    
    /**
     * Uppdatera förhandsvisning när intervalldata ändras
     */
    public function updatedIntervalType()
    {
        $this->updatePreview();
    }
    
    public function updatedIntervalValue()
    {
        $this->updatePreview();
    }
    
    public function updatedStartDate()
    {
        $this->updatePreview();
    }
    
    public function updatedSelectedWeekdays()
    {
        $this->updatePreview();
    }
    
    public function updatedMonthlyType()
    {
        $this->updatePreview();
    }
    
    public function updatedMonthlyDate()
    {
        $this->updatePreview();
    }
    
    public function updatedWeekdaysOnly()
    {
        $this->updatePreview();
    }
    
    /**
     * Uppdatera förhandsvisning av nästa datum
     */
    private function updatePreview()
    {
        // Skapa temporär task för förhandsvisning
        $tempTask = new Task();
        $tempTask->interval_type = $this->interval_type;
        $tempTask->interval_value = $this->interval_value;
        $tempTask->start_date = $this->start_date ? \Carbon\Carbon::parse($this->start_date) : null;
        $tempTask->recurrence_pattern = $this->buildRecurrencePattern();
        
        if ($tempTask->start_date) {
            $calculator = new RecurrenceCalculator();
            $this->preview_dates = $calculator->getNextOccurrences($tempTask, 5);
        } else {
            $this->preview_dates = [];
        }
    }
}
