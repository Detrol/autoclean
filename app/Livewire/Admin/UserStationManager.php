<?php

namespace App\Livewire\Admin;

use App\Mail\EmployeeInvitation as EmployeeInvitationMail;
use App\Models\EmployeeInvitation;
use App\Models\Station;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithPagination;

class UserStationManager extends Component
{
    use WithPagination;

    public $selectedUserId = '';
    public $selectedStations = [];
    
    // Formulärfält för ny/redigerad användare
    public $showCreateForm = false;
    public $editingUserId = null;
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $is_admin = false;

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

    public function toggleCreateForm()
    {
        $this->showCreateForm = !$this->showCreateForm;
        $this->resetForm();
    }

    public function create()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|unique:employee_invitations,email',
            'is_admin' => 'boolean',
        ]);

        // Check if there's already a pending invitation for this email
        $existingInvitation = EmployeeInvitation::where('email', $this->email)
            ->valid()
            ->first();

        if ($existingInvitation) {
            session()->flash('error', "Det finns redan en pågående inbjudan för {$this->email}");
            return;
        }

        // Create the invitation with assigned stations
        $invitation = EmployeeInvitation::create([
            'email' => $this->email,
            'name' => $this->name,
            'token' => EmployeeInvitation::generateToken(),
            'invited_by' => auth()->id(),
            'expires_at' => now()->addDays(7),
            'is_admin' => $this->is_admin,
            'assigned_stations' => !empty($this->selectedStations) ? $this->selectedStations : null,
        ]);

        // Send the invitation email
        Mail::to($this->email)->send(new EmployeeInvitationMail($invitation));

        session()->flash('message', "Inbjudan skickad till {$this->name} ({$this->email})!");
        $this->resetForm();
        $this->showCreateForm = false;
    }

    public function edit($userId)
    {
        $this->editingUserId = $userId;
        $user = User::findOrFail($userId);
        
        $this->name = $user->name;
        $this->email = $user->email;
        $this->is_admin = $user->is_admin;
        $this->selectedStations = $user->stations->pluck('id')->toArray();
        $this->showCreateForm = true;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->editingUserId,
            'password' => 'nullable|string|min:8|confirmed',
            'is_admin' => 'boolean',
        ]);

        $user = User::findOrFail($this->editingUserId);
        
        $updateData = [
            'name' => $this->name,
            'email' => $this->email,
            'is_admin' => $this->is_admin,
        ];

        // Uppdatera lösenord bara om det är angivet
        if (!empty($this->password)) {
            $updateData['password'] = Hash::make($this->password);
        }

        $user->update($updateData);
        $user->stations()->sync($this->selectedStations);

        session()->flash('message', "Användare {$this->name} uppdaterad framgångsrikt!");
        $this->resetForm();
        $this->showCreateForm = false;
    }

    public function delete($userId)
    {
        $user = User::findOrFail($userId);
        $userName = $user->name;
        
        // Ta bort användarens stationstilldelningar
        $user->stations()->detach();
        
        // Ta bort användaren
        $user->delete();

        session()->flash('message', "Användare {$userName} har tagits bort.");
        
        // Rensa urval om den borttagna användaren var vald
        if ($this->selectedUserId == $userId) {
            $this->clearSelection();
        }
    }

    public function cancelEdit()
    {
        $this->resetForm();
        $this->showCreateForm = false;
    }

    private function resetForm()
    {
        $this->editingUserId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->is_admin = false;
        $this->selectedStations = [];
    }
}
