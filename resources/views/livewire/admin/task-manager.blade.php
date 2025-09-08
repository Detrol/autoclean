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

                <div class="grid gap-4 md:grid-cols-3 mt-4">
                    <div>
                        <flux:label for="interval_type">Intervall typ</flux:label>
                        <flux:select wire:model="interval_type" name="interval_type">
                            <option value="daily">Dagligen</option>
                            <option value="weekly">Veckovis</option>
                            <option value="monthly">Månadsvis</option>
                            <option value="custom">Anpassat</option>
                        </flux:select>
                        @error('interval_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <flux:label for="interval_value">Intervall värde</flux:label>
                        <flux:input 
                            wire:model="interval_value" 
                            name="interval_value" 
                            type="number" 
                            min="1"
                            placeholder="1"
                        />
                        <div class="text-xs text-gray-500 mt-1">
                            @if($interval_type === 'custom')
                                Antal dagar
                            @else
                                Varje X {{ $interval_type === 'daily' ? 'dag' : ($interval_type === 'weekly' ? 'vecka' : 'månad') }}
                            @endif
                        </div>
                        @error('interval_value') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <flux:label for="default_due_time">Standard färdig tid</flux:label>
                        <flux:input 
                            wire:model="default_due_time" 
                            name="default_due_time" 
                            type="time"
                        />
                        @error('default_due_time') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

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
                                Standard tid
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
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        @switch($task->interval_type)
                                            @case('daily')
                                                Varje {{ $task->interval_value == 1 ? 'dag' : $task->interval_value . ' dagar' }}
                                                @break
                                            @case('weekly')
                                                Varje {{ $task->interval_value == 1 ? 'vecka' : $task->interval_value . ' veckor' }}
                                                @break
                                            @case('monthly')
                                                Varje {{ $task->interval_value == 1 ? 'månad' : $task->interval_value . ' månader' }}
                                                @break
                                            @case('custom')
                                                Varje {{ $task->interval_value }} dagar
                                                @break
                                        @endswitch
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                    {{ $task->default_due_time ? $task->default_due_time->format('H:i') : '-' }}
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