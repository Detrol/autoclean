<?php

namespace App\Livewire\Admin;

use App\Models\Station;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserStationManager extends Component
{
    use WithPagination;

    public $selectedUserId = '';
    public $selectedStations = [];

    public function render()
    {
        return view('livewire.admin.user-station-manager', [
            'users' => User::where('is_admin', false)->paginate(10),
            'stations' => Station::active()->get(),
        ]);
    }

    public function selectUser($userId)
    {
        $this->selectedUserId = $userId;
        $user = User::findOrFail($userId);
        $this->selectedStations = $user->stations->pluck('id')->toArray();
    }

    public function updateStations()
    {
        if (!$this->selectedUserId) {
            session()->flash('error', 'Välj en användare först.');
            return;
        }

        $user = User::findOrFail($this->selectedUserId);
        $user->stations()->sync($this->selectedStations);

        session()->flash('message', "Stationer uppdaterade för {$user->name}!");
    }

    public function clearSelection()
    {
        $this->selectedUserId = '';
        $this->selectedStations = [];
    }
}
