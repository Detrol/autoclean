<div>
    {{-- Header --}}
    <div class="flex justify-between items-start mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Lagerhantering</h1>
            <p class="text-gray-600 dark:text-gray-400">Hantera artiklar och lager per station</p>
        </div>
        @if($selectedTab === 'items')
            <flux:button 
                wire:click="openItemForm"
                variant="primary"
                icon="plus"
            >
                Ny artikel
            </flux:button>
        @endif
    </div>

    {{-- Item Form --}}
    @if($showItemForm)
        <div class="card-modern-elevated p-6 mb-6 bg-white dark:bg-gray-800 border-l-4 {{ $editingItemId ? 'border-l-orange-500' : 'border-l-blue-500' }}">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                {{ $editingItemId ? 'Redigera artikel' : 'Skapa ny artikel' }}
            </h3>
            
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <flux:label>Namn *</flux:label>
                    <flux:input wire:model="itemName" placeholder="T.ex. Vattenslang" />
                    @error('itemName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <flux:label>Enhet</flux:label>
                    <flux:select wire:model="itemUnit">
                        @foreach($unitOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                </div>
                
                <div>
                    <flux:label>Beställningsnivå</flux:label>
                    <flux:input wire:model="itemReorderLevel" type="number" min="0" placeholder="0" />
                    @error('itemReorderLevel') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:label>Status</flux:label>
                    <flux:checkbox wire:model="itemActive" label="Aktiv artikel" />
                </div>
                
                <div class="md:col-span-2">
                    <flux:label>Beskrivning</flux:label>
                    <flux:textarea wire:model="itemDescription" placeholder="Beskrivning av artikeln..." rows="2" />
                </div>
            </div>
            
            <div class="flex gap-2 mt-4">
                <flux:button wire:click="saveItem" variant="primary">
                    {{ $editingItemId ? 'Uppdatera artikel' : 'Skapa artikel' }}
                </flux:button>
                <flux:button wire:click="closeItemForm" variant="subtle">
                    Avbryt
                </flux:button>
            </div>
        </div>
    @endif

    {{-- Flash messages --}}
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

    {{-- Tabs Navigation --}}
    <div class="card-modern-elevated p-6 mb-6 bg-white dark:bg-gray-800">
        <div class="flex flex-wrap gap-2 mb-4">
            <flux:button 
                wire:click="selectTab('items')"
                variant="{{ $selectedTab === 'items' ? 'primary' : 'subtle' }}"
                size="sm"
            >
                Artiklar
            </flux:button>
            <flux:button 
                wire:click="selectTab('inventory')"
                variant="{{ $selectedTab === 'inventory' ? 'primary' : 'subtle' }}"
                size="sm"
                :disabled="!$selectedStationId"
            >
                Lager
            </flux:button>
            <flux:button 
                wire:click="selectTab('transactions')"
                variant="{{ $selectedTab === 'transactions' ? 'primary' : 'subtle' }}"
                size="sm"
                :disabled="!$selectedStationId"
            >
                Transaktioner
            </flux:button>
        </div>

        @if(in_array($selectedTab, ['inventory', 'transactions']))
            <div>
                <flux:label>Välj station</flux:label>
                <flux:select wire:model.live="selectedStationId" placeholder="Välj en station...">
                    @foreach($stations as $station)
                        <option value="{{ $station->id }}">{{ $station->name }}</option>
                    @endforeach
                </flux:select>
            </div>
        @endif
    </div>

    {{-- Items Tab --}}
    @if($selectedTab === 'items')
        <div class="card-modern-elevated bg-white dark:bg-gray-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Artikel
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Enhet
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Beställningsnivå
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
                        @forelse($items as $item)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $item->name }}</div>
                                    @if($item->description)
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $item->description }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $item->formatted_unit }}
                                </td>
                                <td class="px-6 py-4 text-sm font-mono text-gray-700 dark:text-gray-300">
                                    {{ $item->default_reorder_level }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($item->is_active)
                                        <span class="status-badge bg-success-100 text-success-800 dark:bg-success-800 dark:text-success-200">
                                            <x-heroicon-s-check class="w-3 h-3" />
                                            Aktiv
                                        </span>
                                    @else
                                        <span class="status-badge bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                            <x-heroicon-s-minus class="w-3 h-3" />
                                            Inaktiv
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <flux:button 
                                            wire:click="editItem({{ $item->id }})"
                                            variant="subtle"
                                            size="sm"
                                            icon="pencil"
                                        >
                                            Redigera
                                        </flux:button>
                                        <flux:button 
                                            wire:click="deleteItem({{ $item->id }})"
                                            wire:confirm="Är du säker på att du vill ta bort denna artikel?"
                                            variant="danger"
                                            size="sm"
                                            icon="trash"
                                        >
                                            Ta bort
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center">
                                    <div class="text-gray-500 dark:text-gray-400">
                                        <x-heroicon-o-inbox class="w-12 h-12 mx-auto mb-4" />
                                        <p>Inga artiklar hittades</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Inventory Tab --}}
    @if($selectedTab === 'inventory' && $selectedStationId)
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                    Lager för {{ $stations->where('id', $selectedStationId)->first()?->name }}
                </h2>
                @if($availableItems->count() > 0)
                    <flux:button 
                        wire:click="openAddItemsForm"
                        variant="primary"
                        size="sm"
                    >
                        Lägg till artiklar ({{ $availableItems->count() }} tillgängliga)
                    </flux:button>
                @else
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        Alla artiklar är redan tillagda
                    </span>
                @endif
            </div>

            {{-- Add Items Form --}}
            @if($showAddItemsForm)
                <div class="card-modern-elevated p-6 mb-6 bg-white dark:bg-gray-800 border-l-4 border-l-blue-500">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Välj artiklar att lägga till
                    </h3>
                    
                    <div class="space-y-4">
                        @foreach($availableItems as $item)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <flux:checkbox 
                                        wire:model="selectedItems" 
                                        value="{{ $item->id }}"
                                        id="item_{{ $item->id }}" 
                                    />
                                    <label for="item_{{ $item->id }}" class="cursor-pointer">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $item->name }}</div>
                                        @if($item->description)
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $item->description }}</div>
                                        @endif
                                    </label>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <flux:input 
                                        wire:model="itemQuantities.{{ $item->id }}"
                                        type="number" 
                                        min="0" 
                                        step="0.01"
                                        placeholder="0"
                                        size="sm"
                                        class="w-20"
                                    />
                                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $item->formatted_unit }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="flex gap-2 mt-4">
                        <flux:button wire:click="saveSelectedItems" variant="primary">
                            Lägg till valda artiklar
                        </flux:button>
                        <flux:button wire:click="closeAddItemsForm" variant="subtle">
                            Avbryt
                        </flux:button>
                    </div>
                </div>
            @endif

            {{-- Inventory Table --}}
            <div class="card-modern-elevated bg-white dark:bg-gray-800">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="border-b border-gray-200 dark:border-gray-700">
                                <tr>
                                    <th class="text-left py-3 px-2 text-sm font-medium text-gray-900 dark:text-gray-100">Artikel</th>
                                    <th class="text-right py-3 px-2 text-sm font-medium text-gray-900 dark:text-gray-100">Aktuellt saldo</th>
                                    <th class="text-right py-3 px-2 text-sm font-medium text-gray-900 dark:text-gray-100">Minimum</th>
                                    <th class="text-left py-3 px-2 text-sm font-medium text-gray-900 dark:text-gray-100">Status</th>
                                    <th class="text-left py-3 px-2 text-sm font-medium text-gray-900 dark:text-gray-100">Senaste koll</th>
                                    <th class="text-right py-3 px-2 text-sm font-medium text-gray-900 dark:text-gray-100">Åtgärder</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($stationInventory as $inventory)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="py-3 px-2">
                                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $inventory->inventoryItem->name }}</div>
                                            @if($inventory->notes)
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $inventory->notes }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-2 text-right font-mono">
                                            <span class="text-gray-900 dark:text-gray-100">{{ $inventory->current_quantity }}</span>
                                            <span class="text-sm text-gray-500 dark:text-gray-400 ml-1">{{ $inventory->inventoryItem->formatted_unit }}</span>
                                        </td>
                                        <td class="py-3 px-2 text-right font-mono text-gray-700 dark:text-gray-300">
                                            {{ $inventory->minimum_quantity }} {{ $inventory->inventoryItem->formatted_unit }}
                                        </td>
                                        <td class="py-3 px-2">
                                            @if($inventory->stock_status === 'out_of_stock')
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-100">
                                                    Slut i lager
                                                </span>
                                            @elseif($inventory->stock_status === 'low_stock')
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-100">
                                                    Lågt lager
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-100">
                                                    I lager
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-2 text-sm text-gray-700 dark:text-gray-300">
                                            {{ $inventory->last_checked ? $inventory->last_checked->format('Y-m-d H:i') : 'Aldrig' }}
                                        </td>
                                        <td class="py-3 px-2 text-right">
                                            <flux:button 
                                                wire:click="openStationInventoryForm({{ $inventory->id }})"
                                                variant="ghost"
                                                size="sm"
                                                icon="pencil-square"
                                            >
                                                Justera
                                            </flux:button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-gray-500 dark:text-gray-400">
                                            Inga lagerartiklar hittades. Klicka på "Lägg till saknade artiklar" för att initiera lagret.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Transactions Tab --}}
    @if($selectedTab === 'transactions' && $selectedStationId)
        <div class="card-modern-elevated bg-white dark:bg-gray-800">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    Senaste transaktioner för {{ $stations->where('id', $selectedStationId)->first()?->name }}
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="text-left py-3 px-2 text-sm font-medium text-gray-900 dark:text-gray-100">Datum</th>
                                <th class="text-left py-3 px-2 text-sm font-medium text-gray-900 dark:text-gray-100">Artikel</th>
                                <th class="text-left py-3 px-2 text-sm font-medium text-gray-900 dark:text-gray-100">Typ</th>
                                <th class="text-right py-3 px-2 text-sm font-medium text-gray-900 dark:text-gray-100">Förändring</th>
                                <th class="text-right py-3 px-2 text-sm font-medium text-gray-900 dark:text-gray-100">Saldo efter</th>
                                <th class="text-left py-3 px-2 text-sm font-medium text-gray-900 dark:text-gray-100">Användare</th>
                                <th class="text-left py-3 px-2 text-sm font-medium text-gray-900 dark:text-gray-100">Anledning</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($recentTransactions as $transaction)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="py-3 px-2 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $transaction->created_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="py-3 px-2">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $transaction->inventoryItem->name }}</div>
                                    </td>
                                    <td class="py-3 px-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                            @if($transaction->type === 'add') bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-100
                                            @elseif($transaction->type === 'remove') bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-100
                                            @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100 @endif">
                                            {{ $transaction->formatted_type }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-2 text-right font-mono">
                                        <span class="@if($transaction->type === 'add') text-success-600 @elseif($transaction->type === 'remove') text-danger-600 @else text-blue-600 @endif">
                                            {{ $transaction->signed_quantity }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-2 text-right font-mono text-gray-900 dark:text-gray-100">
                                        {{ $transaction->balance_after }} {{ $transaction->inventoryItem->formatted_unit }}
                                    </td>
                                    <td class="py-3 px-2 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $transaction->user->name }}
                                    </td>
                                    <td class="py-3 px-2 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $transaction->reason }}
                                        @if($transaction->notes)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $transaction->notes }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-500 dark:text-gray-400">
                                        Inga transaktioner hittades.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Station Inventory Form --}}
    @if($showStationInventoryForm && $selectedStationInventory)
        <div class="card-modern-elevated p-6 mb-6 bg-white dark:bg-gray-800 border-l-4 border-l-green-500">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                Justera lager: {{ $selectedStationInventory->inventoryItem->name }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Station: {{ $selectedStationInventory->station->name }}
            </p>
            
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <flux:label>Aktuellt saldo *</flux:label>
                    <div class="relative">
                        <flux:input wire:model="currentQuantity" type="number" step="0.01" min="0" />
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <span class="text-gray-500 text-sm">{{ $selectedStationInventory->inventoryItem->formatted_unit }}</span>
                        </div>
                    </div>
                    @error('currentQuantity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <flux:label>Minimum saldo *</flux:label>
                    <div class="relative">
                        <flux:input wire:model="minimumQuantity" type="number" step="0.01" min="0" />
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <span class="text-gray-500 text-sm">{{ $selectedStationInventory->inventoryItem->formatted_unit }}</span>
                        </div>
                    </div>
                    @error('minimumQuantity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div class="md:col-span-1">
                    <flux:label>Anteckningar</flux:label>
                    <flux:textarea wire:model="notes" placeholder="Valfria anteckningar..." rows="1" />
                </div>
            </div>
            
            <div class="flex gap-2 mt-4">
                <flux:button wire:click="saveStationInventory" variant="primary">
                    Uppdatera lager
                </flux:button>
                <flux:button wire:click="closeStationInventoryForm" variant="subtle">
                    Avbryt
                </flux:button>
            </div>
        </div>
    @endif
</div>