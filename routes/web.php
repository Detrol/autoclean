<?php

use App\Livewire\Admin\InventoryManager;
use App\Livewire\Admin\StationManager;
use App\Livewire\Admin\TaskManager;
use App\Livewire\Admin\TaskTemplates;
use App\Livewire\Admin\UserStationManager;
use App\Livewire\Employee\StationDetails;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    // Employee routes
    Route::get('station/{id}', StationDetails::class)->name('station.details');

    // Admin routes
    Route::middleware('can:admin')->group(function () {
        Route::get('admin/stations', StationManager::class)->name('admin.stations');
        Route::get('admin/tasks', TaskManager::class)->name('admin.tasks');
        Route::get('admin/templates', TaskTemplates::class)->name('admin.templates');
        Route::get('admin/inventory', InventoryManager::class)->name('admin.inventory');
        Route::get('admin/users', UserStationManager::class)->name('admin.users');
    });
});

require __DIR__.'/auth.php';
