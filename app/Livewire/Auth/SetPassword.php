<?php

namespace App\Livewire\Auth;

use App\Models\EmployeeInvitation;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class SetPassword extends Component
{
    public ?EmployeeInvitation $invitation = null;

    public string $token = '';

    #[Validate('required|string|min:8|confirmed')]
    public string $password = '';

    #[Validate('required|string')]
    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;

        // Find the invitation by token
        $this->invitation = EmployeeInvitation::where('token', $token)->first();

        // Check if invitation is valid
        if (! $this->invitation) {
            session()->flash('error', 'Ogiltig inbjudningslänk.');
            $this->redirect(route('login'), navigate: true);

            return;
        }

        // Check if invitation has expired
        if ($this->invitation->hasExpired()) {
            session()->flash('error', 'Denna inbjudan har utgått. Kontakta din administratör för en ny inbjudan.');
            $this->redirect(route('login'), navigate: true);

            return;
        }

        // Check if invitation has already been accepted
        if ($this->invitation->hasBeenAccepted()) {
            session()->flash('error', 'Denna inbjudan har redan använts.');
            $this->redirect(route('login'), navigate: true);

            return;
        }
    }

    public function setPassword(): void
    {
        $this->validate();

        // Create the user account
        $user = User::create([
            'name' => $this->invitation->name,
            'email' => $this->invitation->email,
            'password' => Hash::make($this->password),
            'is_admin' => $this->invitation->is_admin,
            'email_verified_at' => now(),
        ]);

        // Assign stations from the invitation
        if ($this->invitation->assigned_stations) {
            $user->stations()->sync($this->invitation->assigned_stations);
        }

        // Mark invitation as accepted
        $this->invitation->markAsAccepted();

        // Fire the registered event
        event(new Registered($user));

        // Log the user in
        Auth::login($user);

        // Redirect to dashboard with success message
        session()->flash('message', 'Ditt konto har skapats framgångsrikt!');
        $this->redirect(route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.set-password');
    }
}
