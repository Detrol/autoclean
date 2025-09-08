<?php

namespace App\Livewire\Admin;

use App\Models\Station;
use Livewire\Component;
use Livewire\WithPagination;

class StationManager extends Component
{
    use WithPagination;

    public $name = '';
    public $description = '';
    public $is_active = true;
    public $editingStationId = null;
    public $showCreateForm = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    public function render()
    {
        return view('livewire.admin.station-manager', [
            'stations' => Station::paginate(10)
        ]);
    }

    public function create()
    {
        $this->validate();

        Station::create([
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ]);

        $this->reset(['name', 'description']);
        $this->is_active = true;
        $this->showCreateForm = false;

        session()->flash('message', 'Station skapad framgångsrikt!');
    }

    public function edit($stationId)
    {
        $station = Station::findOrFail($stationId);
        
        $this->editingStationId = $stationId;
        $this->name = $station->name;
        $this->description = $station->description;
        $this->is_active = $station->is_active;
    }

    public function update()
    {
        $this->validate();

        $station = Station::findOrFail($this->editingStationId);
        
        $station->update([
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ]);

        $this->reset(['name', 'description', 'editingStationId']);
        $this->is_active = true;

        session()->flash('message', 'Station uppdaterad framgångsrikt!');
    }

    public function delete($stationId)
    {
        $station = Station::findOrFail($stationId);
        
        // Kontrollera om stationen har aktiva uppgifter eller tidslogs
        if ($station->tasks()->active()->exists()) {
            session()->flash('error', 'Kan inte ta bort station som har aktiva uppgifter.');
            return;
        }

        $station->delete();
        
        session()->flash('message', 'Station borttagen framgångsrikt!');
    }

    public function cancelEdit()
    {
        $this->reset(['name', 'description', 'editingStationId']);
        $this->is_active = true;
    }

    public function toggleCreateForm()
    {
        $this->showCreateForm = !$this->showCreateForm;
        if (!$this->showCreateForm) {
            $this->reset(['name', 'description']);
            $this->is_active = true;
        }
    }
}
