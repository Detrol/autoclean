<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Uppgiftshantering</h1>
        
        <flux:button 
            size="sm" 
            variant="primary" 
            wire:click="toggleCreateForm"
        >
            {{ $showCreateForm ? 'Avbryt' : 'Skapa ny uppgift' }}
        </flux:button>
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

    {{-- Filter --}}
    <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-4">
            <div>
                <flux:label for="selectedStationFilter">Filtrera efter station</flux:label>
                <flux:select wire:model.live="selectedStationFilter" name="selectedStationFilter" class="w-48">
                    <option value="">Alla stationer</option>
                    @foreach($stations as $station)
                        <option value="{{ $station->id }}">{{ $station->name }}</option>
                    @endforeach
                </flux:select>
            </div>
        </div>
    </div>

    {{-- Skapa/Redigera form --}}
    @if($showCreateForm || $editingTaskId)
        <div class="mb-6 p-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                {{ $editingTaskId ? 'Redigera Uppgift' : 'Skapa Ny Uppgift' }}
            </h3>

            <form wire:submit="{{ $editingTaskId ? 'update' : 'create' }}">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <flux:label for="station_id">Station</flux:label>
                        <flux:select wire:model="station_id" name="station_id">
                            <option value="">Välj station</option>
                            @foreach($stations as $station)
                                <option value="{{ $station->id }}">{{ $station->name }}</option>
                            @endforeach
                        </flux:select>
                        @error('station_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <flux:label for="name">Uppgiftsnamn</flux:label>
                        <flux:input 
                            wire:model="name" 
                            name="name" 
                            type="text" 
                            placeholder="Namn på uppgiften"
                        />
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Startdatum och intervalltyp --}}
                <div class="grid gap-4 md:grid-cols-3 mt-4">
                    <div>
                        <flux:label for="start_date">Startdatum</flux:label>
                        <flux:input 
                            wire:model.blur="start_date" 
                            name="start_date" 
                            type="date"
                            min="{{ now()->format('Y-m-d') }}"
                        />
                        @error('start_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <flux:label for="interval_type">Intervall typ</flux:label>
                        <flux:select wire:model.live="interval_type" name="interval_type">
                            <option value="daily">Dagligen</option>
                            <option value="weekly">Veckovis</option>
                            <option value="monthly">Månadsvis</option>
                            <option value="yearly">Årligen</option>
                            <option value="custom">Anpassat</option>
                        </flux:select>
                        @error('interval_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                </div>

                {{-- Dynamiska intervall-inställningar --}}
                <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                    <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-4">Intervallkonfiguration</h4>
                    
                    @if($interval_type === 'daily')
                        <div class="space-y-4">
                            <div>
                                <label class="flex items-center gap-2">
                                    <input type="radio" wire:model.live="weekdays_only" value="0" name="daily_type" class="w-4 h-4 text-primary-600">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Varje 
                                        <input type="number" wire:model.live="interval_value" min="1" max="365" class="w-16 px-2 py-1 text-sm border rounded mx-1">
                                        dag(ar)
                                    </span>
                                </label>
                            </div>
                            <div>
                                <label class="flex items-center gap-2">
                                    <input type="radio" wire:model.live="weekdays_only" value="1" name="daily_type" class="w-4 h-4 text-primary-600">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Endast vardagar (måndag-fredag)</span>
                                </label>
                            </div>
                        </div>
                    @endif

                    @if($interval_type === 'weekly')
                        <div class="space-y-4">
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Upprepa var</span>
                                <input type="number" wire:model.live="interval_value" min="1" max="52" class="w-16 px-2 py-1 text-sm border rounded">
                                <span class="text-sm text-gray-700 dark:text-gray-300">vecka/veckor</span>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">På dessa dagar:</span>
                                <div class="grid grid-cols-7 gap-2">
                                    @php
                                        $weekdays = [
                                            'monday' => 'Mån',
                                            'tuesday' => 'Tis', 
                                            'wednesday' => 'Ons',
                                            'thursday' => 'Tor',
                                            'friday' => 'Fre',
                                            'saturday' => 'Lör',
                                            'sunday' => 'Sön'
                                        ];
                                    @endphp
                                    @foreach($weekdays as $value => $label)
                                        <label class="flex flex-col items-center gap-1">
                                            <input type="checkbox" wire:model.live="selected_weekdays" value="{{ $value }}" class="w-4 h-4 text-primary-600">
                                            <span class="text-xs text-gray-600 dark:text-gray-400">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($interval_type === 'monthly')
                        <div class="space-y-4">
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Upprepa var</span>
                                <input type="number" wire:model.live="interval_value" min="1" max="12" class="w-16 px-2 py-1 text-sm border rounded">
                                <span class="text-sm text-gray-700 dark:text-gray-300">månad(er)</span>
                            </div>
                            
                            <div class="space-y-3">
                                <label class="flex items-center gap-2">
                                    <input type="radio" wire:model.live="monthly_type" value="date" name="monthly_type" class="w-4 h-4 text-primary-600">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">På dag 
                                        <input type="number" wire:model.live="monthly_date" min="1" max="31" class="w-16 px-2 py-1 text-sm border rounded mx-1">
                                        i månaden
                                    </span>
                                </label>
                                
                                <label class="flex items-start gap-2">
                                    <input type="radio" wire:model.live="monthly_type" value="weekday" name="monthly_type" class="w-4 h-4 text-primary-600 mt-1">
                                    <div class="space-y-2">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">På specifik veckodag:</span>
                                        <div class="flex items-center gap-2">
                                            <select wire:model.live="monthly_weekday_ordinal" class="px-2 py-1 text-sm border rounded">
                                                <option value="1">Första</option>
                                                <option value="2">Andra</option>
                                                <option value="3">Tredje</option>
                                                <option value="4">Fjärde</option>
                                                <option value="5">Sista</option>
                                            </select>
                                            <select wire:model.live="monthly_weekday" class="px-2 py-1 text-sm border rounded">
                                                <option value="monday">Måndag</option>
                                                <option value="tuesday">Tisdag</option>
                                                <option value="wednesday">Onsdag</option>
                                                <option value="thursday">Torsdag</option>
                                                <option value="friday">Fredag</option>
                                                <option value="saturday">Lördag</option>
                                                <option value="sunday">Söndag</option>
                                            </select>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    @endif

                    @if($interval_type === 'yearly')
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Uppgiften kommer att upprepas varje år på samma datum som startdatumet.
                        </div>
                    @endif

                    @if($interval_type === 'custom')
                        <div class="space-y-4">
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Varje</span>
                                <input type="number" wire:model.live="interval_value" min="1" max="365" class="w-16 px-2 py-1 text-sm border rounded">
                                <span class="text-sm text-gray-700 dark:text-gray-300">dagar</span>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                Mer avancerade anpassningar kommer i framtida uppdateringar.
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Slutvillkor --}}
                <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                    <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-4">Slutvillkor</h4>
                    
                    <div class="space-y-3">
                        <label class="flex items-center gap-2">
                            <input type="radio" wire:model.live="end_type" value="never" name="end_type" class="w-4 h-4 text-primary-600">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Aldrig (fortsätt för alltid)</span>
                        </label>
                        
                        <label class="flex items-center gap-2">
                            <input type="radio" wire:model.live="end_type" value="date" name="end_type" class="w-4 h-4 text-primary-600">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Sluta efter 
                                <input type="date" wire:model="end_date" class="px-2 py-1 text-sm border rounded mx-1">
                            </span>
                        </label>
                        @error('end_date') <span class="text-red-500 text-sm block ml-6">{{ $message }}</span> @enderror
                        
                        <label class="flex items-center gap-2">
                            <input type="radio" wire:model.live="end_type" value="occurrences" name="end_type" class="w-4 h-4 text-primary-600">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Sluta efter 
                                <input type="number" wire:model="occurrences" min="1" class="w-20 px-2 py-1 text-sm border rounded mx-1">
                                gånger
                            </span>
                        </label>
                        @error('occurrences') <span class="text-red-500 text-sm block ml-6">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Förhandsvisning --}}
                @if(!empty($preview_dates))
                    <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-2">Förhandsvisning - Nästa 5 datum:</h4>
                        <div class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                            @foreach($preview_dates as $date)
                                <div>{{ $date->translatedFormat('l j F Y') }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="grid gap-4 md:grid-cols-2 mt-4">
                    <div>
                        <flux:label for="description">Beskrivning</flux:label>
                        <flux:textarea 
                            wire:model="description" 
                            name="description" 
                            placeholder="Beskrivning av uppgiften"
                            rows="3"
                        />
                        @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <flux:label for="is_active">Status</flux:label>
                        <flux:select wire:model="is_active" name="is_active">
                            <option value="1">Aktiv</option>
                            <option value="0">Inaktiv</option>
                        </flux:select>
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <flux:button type="submit" variant="primary">
                        {{ $editingTaskId ? 'Uppdatera' : 'Skapa' }}
                    </flux:button>
                    
                    @if($editingTaskId)
                        <flux:button wire:click="cancelEdit" variant="ghost">
                            Avbryt
                        </flux:button>
                    @endif
                </div>
            </form>
        </div>
    @endif

    {{-- Uppgiftslista --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        @if($tasks->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Uppgift
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Station
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Intervall
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Åtgärder
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($tasks as $task)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $task->name }}
                                        </div>
                                        @if($task->description)
                                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ Str::limit($task->description, 50) }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ $task->station->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $task->getIntervalDescription() }}
                                    </span>
                                    @if($task->start_date)
                                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                            Startar: {{ $task->start_date->translatedFormat('j M Y') }}
                                        </div>
                                    @endif
                                    @if($task->end_date)
                                        <div class="text-xs text-gray-500 dark:text-gray-500">
                                            Slutar: {{ $task->end_date->translatedFormat('j M Y') }}
                                        </div>
                                    @elseif($task->occurrences)
                                        <div class="text-xs text-gray-500 dark:text-gray-500">
                                            {{ $task->occurrences }} gånger
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $task->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                        {{ $task->is_active ? 'Aktiv' : 'Inaktiv' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <div class="flex justify-end gap-2">
                                        <flux:button 
                                            size="sm" 
                                            wire:click="edit({{ $task->id }})"
                                            variant="ghost"
                                        >
                                            Redigera
                                        </flux:button>
                                        
                                        <flux:button 
                                            size="sm" 
                                            variant="danger" 
                                            wire:click="delete({{ $task->id }})"
                                            onclick="return confirm('Är du säker på att du vill ta bort denna uppgift?')"
                                        >
                                            Ta bort
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $tasks->links() }}
            </div>
        @else
            <div class="p-6 text-center">
                <p class="text-gray-600 dark:text-gray-400">Inga uppgifter finns än.</p>
            </div>
        @endif
    </div>
</div>