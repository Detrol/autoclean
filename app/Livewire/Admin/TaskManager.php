<?php

namespace App\Livewire\Admin;

use App\Models\Station;
use App\Models\Task;
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
    public $default_due_time = '';
    public $is_active = true;
    public $editingTaskId = null;
    public $showCreateForm = false;
    public $selectedStationFilter = '';

    protected $rules = [
        'station_id' => 'required|exists:stations,id',
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'interval_type' => 'required|in:daily,weekly,monthly,custom',
        'interval_value' => 'required|integer|min:1',
        'default_due_time' => 'nullable|date_format:H:i',
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
            'default_due_time' => $this->default_due_time ?: null,
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
        $this->default_due_time = $task->default_due_time ? $task->default_due_time->format('H:i') : '';
        $this->is_active = $task->is_active;
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
            'default_due_time' => $this->default_due_time ?: null,
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
            'default_due_time', 'editingTaskId'
        ]);
        $this->is_active = true;
        $this->interval_type = 'daily';
        $this->interval_value = 1;
    }
}
