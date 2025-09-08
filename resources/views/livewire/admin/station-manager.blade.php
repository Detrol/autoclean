<div>
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Stationshantering</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Hantera stationer för biltvätten</p>
        </div>
        
        @if($showCreateForm)
            <button 
                wire:click="toggleCreateForm"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600"
            >
                <x-heroicon-o-x-mark class="w-4 h-4" />
                Avbryt
            </button>
        @else
            <button 
                wire:click="toggleCreateForm"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
                <x-heroicon-o-plus class="w-4 h-4" />
                Skapa ny station
            </button>
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

    {{-- Enhanced Create/Edit Form --}}
    @if($showCreateForm || $editingStationId)
        <div class="mb-8 card-modern p-8 border-l-4 border-primary-500">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center gradient-primary shadow-lg shadow-primary-500/25">
                    @if($editingStationId)
                        <x-heroicon-o-pencil class="w-5 h-5 text-white" />
                    @else
                        <x-heroicon-o-plus class="w-5 h-5 text-white" />
                    @endif
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                        {{ $editingStationId ? 'Redigera Station' : 'Skapa Ny Station' }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $editingStationId ? 'Uppdatera stationsinformation' : 'Lägg till en ny station i systemet' }}
                    </p>
                </div>
            </div>

            <form wire:submit="{{ $editingStationId ? 'update' : 'create' }}" class="space-y-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <flux:label for="name" class="text-sm font-medium text-gray-900 dark:text-gray-100">Stationsnamn</flux:label>
                        <flux:input 
                            wire:model="name" 
                            name="name" 
                            type="text" 
                            placeholder="T.ex. Tvättstation 1"
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
                        <flux:label for="is_active" class="text-sm font-medium text-gray-900 dark:text-gray-100">Status</flux:label>
                        <flux:select wire:model="is_active" name="is_active" class="input-modern">
                            <option value="1">🟢 Aktiv</option>
                            <option value="0">🔴 Inaktiv</option>
                        </flux:select>
                    </div>
                </div>

                <div class="space-y-2">
                    <flux:label for="description" class="text-sm font-medium text-gray-900 dark:text-gray-100">Beskrivning</flux:label>
                    <flux:textarea 
                        wire:model="description" 
                        name="description" 
                        placeholder="Beskriv stationens funktion och placering..."
                        rows="4"
                        class="input-modern resize-none"
                    />
                    @error('description') 
                        <div class="flex items-center gap-2 text-danger-600 text-sm">
                            <x-heroicon-o-exclamation-circle class="w-4 h-4" />
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="flex gap-4 pt-4">
                    @if($editingStationId)
                        <flux:button type="submit" variant="primary" icon="check">
                            Uppdatera Station
                        </flux:button>
                    @else
                        <flux:button type="submit" variant="primary" icon="check">
                            Skapa Station
                        </flux:button>
                    @endif
                    
                    @if($editingStationId)
                        <flux:button wire:click="cancelEdit" variant="ghost" icon="x-mark">
                            Avbryt
                        </flux:button>
                    @endif
                </div>
            </form>
        </div>
    @endif

    {{-- Stationslista --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        @if($stations->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Namn
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Beskrivning
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Skapad
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Åtgärder
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($stations as $station)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $station->name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $station->description ?: '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $station->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                        {{ $station->is_active ? 'Aktiv' : 'Inaktiv' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                    {{ $station->created_at->format('Y-m-d') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <div class="flex justify-end gap-2">
                                        <flux:button 
                                            size="sm" 
                                            wire:click="edit({{ $station->id }})"
                                            variant="ghost"
                                        >
                                            Redigera
                                        </flux:button>
                                        
                                        <flux:button 
                                            size="sm" 
                                            variant="danger" 
                                            wire:click="delete({{ $station->id }})"
                                            onclick="return confirm('Är du säker på att du vill ta bort denna station?')"
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
                {{ $stations->links() }}
            </div>
        @else
            <div class="p-6 text-center">
                <p class="text-gray-600 dark:text-gray-400">Inga stationer finns än.</p>
            </div>
        @endif
    </div>
</div>
