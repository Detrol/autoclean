<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Användarhantering</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Hantera användare och deras stationstilldelningar</p>
        </div>
        
        @if($showCreateForm)
            <flux:button 
                wire:click="toggleCreateForm"
                variant="ghost"
                icon="x-mark"
            >
                Avbryt
            </flux:button>
        @else
            <flux:button 
                wire:click="toggleCreateForm"
                variant="primary"
                icon="plus"
            >
                Bjud in användare
            </flux:button>
        @endif
    </div>

    {{-- Enhanced Flash Messages --}}
    @if (session()->has('message'))
        <div class="mb-6 card-modern p-4 bg-success-50 dark:bg-success-900/20 border-success-200 dark:border-success-700">
            <div class="flex items-center gap-3">
                <x-heroicon-o-check class="w-5 h-5 text-success-600" />
                <span class="text-success-800 dark:text-success-200">{{ session('message') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 card-modern p-4 bg-danger-50 dark:bg-danger-900/20 border-danger-200 dark:border-danger-700">
            <div class="flex items-center gap-3">
                <x-heroicon-o-exclamation-circle class="w-5 h-5 text-danger-600" />
                <span class="text-danger-800 dark:text-danger-200">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- User Create/Edit Form --}}
    @if($showCreateForm)
        <div class="mb-8 card-modern p-6 border-l-4 border-primary-500">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl gradient-primary shadow-lg shadow-primary-500/25 flex items-center justify-center">
                    @if($editingUserId)
                        <x-heroicon-o-pencil class="w-5 h-5 text-white" />
                    @else
                        <x-heroicon-o-plus class="w-5 h-5 text-white" />
                    @endif
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                        {{ $editingUserId ? 'Redigera användare' : 'Bjud in användare' }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $editingUserId ? 'Uppdatera användarinformation och stationstilldelningar' : 'Bjud in en ny anställd till systemet' }}
                    </p>
                </div>
            </div>

            <form wire:submit="{{ $editingUserId ? 'update' : 'create' }}" class="space-y-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <flux:label for="name">Namn</flux:label>
                        <flux:input 
                            wire:model="name" 
                            name="name" 
                            type="text" 
                            placeholder="Ange användarens fullständiga namn"
                            class="input-modern"
                        />
                        @error('name') 
                            <div class="flex items-center gap-2 text-danger-600 text-sm">
                                <x-heroicon-o-exclamation-circle class="w-4 h-4" />
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <flux:label for="email">E-postadress</flux:label>
                        <flux:input 
                            wire:model="email" 
                            name="email" 
                            type="email" 
                            placeholder="anvandare@autoclean.se"
                            class="input-modern"
                        />
                        @error('email') 
                            <div class="flex items-center gap-2 text-danger-600 text-sm">
                                <x-heroicon-o-exclamation-circle class="w-4 h-4" />
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                @if($editingUserId)
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <flux:label for="password">Nytt lösenord (lämna tomt för att behålla)</flux:label>
                        <flux:input
                            wire:model="password"
                            name="password"
                            type="password"
                            placeholder="Minst 8 tecken"
                            class="input-modern"
                        />
                        @error('password')
                            <div class="flex items-center gap-2 text-danger-600 text-sm">
                                <x-heroicon-o-exclamation-circle class="w-4 h-4" />
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <flux:label for="password_confirmation">Bekräfta lösenord</flux:label>
                        <flux:input
                            wire:model="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            placeholder="Upprepa lösenordet"
                            class="input-modern"
                        />
                    </div>
                </div>
                @else
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700">
                    <div class="flex items-start gap-3">
                        <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 mt-0.5" />
                        <div>
                            <p class="text-sm text-blue-800 dark:text-blue-200 font-medium">Inbjudningssystem</p>
                            <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                                Användaren kommer att få ett mail med en länk där de kan sätta sitt eget lösenord.
                                Inbjudan är giltig i 7 dagar.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                <div class="space-y-2">
                    <flux:label>Användartyp</flux:label>
                    <div class="flex items-center gap-6">
                        <label class="flex items-center gap-2">
                            <input type="radio" wire:model="is_admin" value="0" name="user_type" class="w-4 h-4 text-primary-600">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Anställd</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" wire:model="is_admin" value="1" name="user_type" class="w-4 h-4 text-primary-600">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Administrator</span>
                        </label>
                    </div>
                </div>

                @if(!$is_admin)
                    <div class="space-y-2">
                        <flux:label>Stationstilldelningar</flux:label>
                        <div class="grid gap-3 md:grid-cols-2">
                            @foreach($stations as $station)
                                <div class="flex items-center p-3 card-modern bg-gray-50 dark:bg-gray-800">
                                    <input 
                                        type="checkbox" 
                                        id="create_station_{{ $station->id }}"
                                        wire:model="selectedStations" 
                                        value="{{ $station->id }}"
                                        class="w-4 h-4 text-primary-600 rounded focus:ring-primary-500"
                                    />
                                    <label for="create_station_{{ $station->id }}" class="ml-3 flex-1">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $station->name }}</div>
                                        @if($station->description)
                                            <div class="text-xs text-gray-600 dark:text-gray-400">{{ $station->description }}</div>
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex gap-4 pt-4">
                    <flux:button type="submit" variant="primary" icon="check">
                        {{ $editingUserId ? 'Uppdatera användare' : 'Skicka inbjudan' }}
                    </flux:button>
                    
                    <flux:button wire:click="toggleCreateForm" variant="ghost" icon="x-mark">
                        Avbryt
                    </flux:button>
                </div>
            </form>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Användarlista --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Anställda</h3>
            </div>
            
            <div class="p-4">
                @if($users->count() > 0)
                    <div class="space-y-2">
                        @foreach($users as $user)
                            <div class="card-modern p-4 
                                {{ $selectedUserId == $user->id ? 'bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-700' : '' }}">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $user->name }}</span>
                                            @if($user->is_admin)
                                                <span class="status-badge bg-primary-100 text-primary-700 dark:bg-primary-800 dark:text-primary-200">
                                                    Admin
                                                </span>
                                            @else
                                                <span class="status-badge bg-success-100 text-success-700 dark:bg-success-800 dark:text-success-200">
                                                    Anställd
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $user->email }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                            {{ $user->stations->count() }} stationer tilldelade
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        <flux:button 
                                            size="sm" 
                                            wire:click="selectUser({{ $user->id }})"
                                            variant="{{ $selectedUserId == $user->id ? 'primary' : 'ghost' }}"
                                            title="Välj för stationstilldelning"
                                        >
                                            {{ $selectedUserId == $user->id ? 'Vald' : 'Välj' }}
                                        </flux:button>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                                    <flux:button 
                                        size="sm" 
                                        variant="ghost"
                                        wire:click="edit({{ $user->id }})"
                                        icon="pencil"
                                    >
                                        Redigera
                                    </flux:button>
                                    
                                    <flux:button 
                                        size="sm" 
                                        variant="danger"
                                        wire:click="delete({{ $user->id }})"
                                        wire:confirm="Är du säker på att du vill ta bort användaren '{{ $user->name }}'? Detta kan inte ångras."
                                        icon="trash"
                                    >
                                        Ta bort
                                    </flux:button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                @else
                    <p class="text-gray-600 dark:text-gray-400">Inga anställda finns än.</p>
                @endif
            </div>
        </div>

        {{-- Stationstilldelningar --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Stationstilldelningar
                        @if($selectedUserId)
                            - {{ \App\Models\User::find($selectedUserId)->name }}
                        @endif
                    </h3>
                    @if($selectedUserId)
                        <flux:button 
                            size="sm" 
                            variant="ghost"
                            wire:click="clearSelection"
                        >
                            Rensa
                        </flux:button>
                    @endif
                </div>
            </div>
            
            <div class="p-4">
                @if($selectedUserId)
                    <form wire:submit="updateStations">
                        <div class="space-y-3">
                            @foreach($stations as $station)
                                <div class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        id="station_{{ $station->id }}"
                                        wire:model="selectedStations" 
                                        value="{{ $station->id }}"
                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
                                    />
                                    <label 
                                        for="station_{{ $station->id }}" 
                                        class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-100"
                                    >
                                        {{ $station->name }}
                                    </label>
                                    @if($station->description)
                                        <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">
                                            - {{ $station->description }}
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            <flux:button type="submit" variant="primary">
                                Uppdatera Tilldelningar
                            </flux:button>
                        </div>
                    </form>
                @else
                    <div class="text-center p-8">
                        <p class="text-gray-600 dark:text-gray-400">Välj en anställd för att hantera stationstilldelningar.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>