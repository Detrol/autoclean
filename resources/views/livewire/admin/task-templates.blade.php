<div>
    {{-- Header --}}
    <div class="flex justify-between items-start mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Uppgiftsmallar</h1>
            <p class="text-gray-600 dark:text-gray-400">Hantera mallar för extra uppgifter</p>
        </div>
        <flux:button 
            wire:click="openCreateForm"
            variant="primary"
            icon="plus"
        >
            Ny mall
        </flux:button>
    </div>

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

    {{-- Filters --}}
    <div class="card-modern-elevated p-6 mb-6 bg-white dark:bg-gray-800">
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <flux:label>Sök</flux:label>
                <flux:input 
                    wire:model.live="searchTerm"
                    placeholder="Sök efter namn eller beskrivning..."
                    icon="magnifying-glass"
                />
            </div>
            <div class="flex items-end">
                <flux:button 
                    wire:click="$set('searchTerm', '')"
                    variant="subtle"
                    size="sm"
                >
                    Rensa filter
                </flux:button>
            </div>
        </div>
    </div>

    {{-- Create Form --}}
    @if($showCreateForm)
        <div class="card-modern-elevated p-6 mb-6 bg-white dark:bg-gray-800 border-l-4 border-l-blue-500">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Skapa ny mall</h3>
            
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <flux:label>Namn *</flux:label>
                    <flux:input wire:model="name" placeholder="T.ex. Gräsklippning" />
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <flux:label>Status</flux:label>
                    <flux:checkbox wire:model="is_active" label="Aktiv" />
                </div>
                
                <div class="md:col-span-2">
                    <flux:label>Beskrivning</flux:label>
                    <flux:textarea wire:model="description" placeholder="Beskriv vad uppgiften innebär..." rows="2" />
                    @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
            
            <div class="flex gap-2 mt-4">
                <flux:button wire:click="createTemplate" variant="primary">
                    Skapa mall
                </flux:button>
                <flux:button wire:click="hideCreateForm" variant="subtle">
                    Avbryt
                </flux:button>
            </div>
        </div>
    @endif

    {{-- Edit Form --}}
    @if($showEditForm)
        <div class="card-modern-elevated p-6 mb-6 bg-white dark:bg-gray-800 border-l-4 border-l-orange-500">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Redigera mall</h3>
            
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <flux:label>Namn *</flux:label>
                    <flux:input wire:model="name" />
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <flux:label>Status</flux:label>
                    <flux:checkbox wire:model="is_active" label="Aktiv" />
                </div>
                
                <div class="md:col-span-2">
                    <flux:label>Beskrivning</flux:label>
                    <flux:textarea wire:model="description" rows="2" />
                    @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
            
            <div class="flex gap-2 mt-4">
                <flux:button wire:click="updateTemplate" variant="primary">
                    Uppdatera mall
                </flux:button>
                <flux:button wire:click="hideEditForm" variant="subtle">
                    Avbryt
                </flux:button>
            </div>
        </div>
    @endif

    {{-- Templates Table --}}
    <div class="card-modern-elevated bg-white dark:bg-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Namn
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Beskrivning
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Användning
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
                    @forelse($templates as $template)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ $template->name }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate">
                                    {{ $template->description ?: 'Ingen beskrivning' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-mono font-bold text-gray-700 dark:text-gray-300">
                                    {{ $templateUsage[$template->id] ?? 0 }} gånger
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($template->is_active)
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
                                        wire:click="openEditForm({{ $template->id }})"
                                        variant="subtle"
                                        size="sm"
                                        icon="pencil"
                                    >
                                        Redigera
                                    </flux:button>
                                    
                                    <flux:button 
                                        wire:click="toggleActive({{ $template->id }})"
                                        variant="{{ $template->is_active ? 'danger' : 'success' }}"
                                        size="sm"
                                    >
                                        {{ $template->is_active ? 'Inaktivera' : 'Aktivera' }}
                                    </flux:button>
                                    
                                    @if(($templateUsage[$template->id] ?? 0) == 0)
                                        <flux:button 
                                            wire:click="deleteTemplate({{ $template->id }})"
                                            wire:confirm="Är du säker på att du vill ta bort denna mall?"
                                            variant="danger"
                                            size="sm"
                                            icon="trash"
                                        >
                                            Ta bort
                                        </flux:button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center">
                                <div class="text-gray-500 dark:text-gray-400">
                                    <x-heroicon-o-inbox class="w-12 h-12 mx-auto mb-4" />
                                    <p>Inga mallar hittades</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($templates->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $templates->links() }}
            </div>
        @endif
    </div>
</div>