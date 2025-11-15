<?php

namespace App\Livewire\Admin;

use App\Services\SettingsService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SystemSettings extends Component
{
    public $settings = [];

    public function mount(SettingsService $settingsService)
    {
        $allSettings = $settingsService->all();

        // Transform settings into an array keyed by setting key for easy binding
        foreach ($allSettings as $setting) {
            $this->settings[$setting->key] = $setting->value;
        }
    }

    public function save()
    {
        $settingsService = app(SettingsService::class);

        foreach ($this->settings as $key => $value) {
            $settingsService->set($key, $value);
        }

        session()->flash('message', 'Inställningar har sparats!');
    }

    public function render(SettingsService $settingsService)
    {
        $groupedSettings = $settingsService->all()->groupBy('group');

        return view('livewire.admin.system-settings', [
            'groupedSettings' => $groupedSettings,
        ]);
    }
}
