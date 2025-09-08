<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Användar-Station Tilldelningar</h1>
    </div>

    {{-- Flash meddelanden --}}
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
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
                            <div class="flex items-center justify-between p-3 rounded border border-gray-200 dark:border-gray-700 
                                {{ $selectedUserId == $user->id ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-700' : 'bg-gray-50 dark:bg-gray-700' }}">
                                <div>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</span>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $user->email }}
                                        <span class="ml-2">({{ $user->stations->count() }} stationer)</span>
                                    </div>
                                </div>
                                <flux:button 
                                    size="sm" 
                                    wire:click="selectUser({{ $user->id }})"
                                    variant="{{ $selectedUserId == $user->id ? 'primary' : 'ghost' }}"
                                >
                                    {{ $selectedUserId == $user->id ? 'Vald' : 'Välj' }}
                                </flux:button>
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