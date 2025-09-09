<div>
    {{-- Header med navigation --}}
    <div class="flex justify-between items-start mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                <x-heroicon-o-arrow-left class="w-5 h-5" />
                <span class="text-sm">Tillbaka till Dashboard</span>
            </a>
        </div>
    </div>

    <div class="flex items-center gap-4 mb-8">
        <div class="w-12 h-12 rounded-xl gradient-purple shadow-lg shadow-purple-500/25 flex items-center justify-center">
            <x-heroicon-o-building-storefront class="w-6 h-6 text-white" />
        </div>
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $station->name }}</h1>
            @if($station->description)
                <p class="text-gray-600 dark:text-gray-400">{{ $station->description }}</p>
            @endif
        </div>
        
        @if(!$isLoggedIn)
            <flux:button 
                wire:click="clockIn"
                variant="primary"
                icon="clock"
                class="ml-auto gradient-primary text-white"
            >
                Klocka in
            </flux:button>
        @else
            <div class="ml-auto status-badge bg-success-100 text-success-800 dark:bg-success-800 dark:text-success-200">
                <x-heroicon-o-check class="w-4 h-4" />
                Inklockat
            </div>
        @endif
    </div>

    {{-- Flash meddelanden --}}
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

    {{-- Uppgifter --}}
    <div class="card-modern-elevated p-6 mb-8 bg-white dark:bg-gray-800">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl gradient-orange shadow-lg shadow-orange-500/25 flex items-center justify-center">
                    <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Uppgifter</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $taskData->count() }} uppgifter kopplade till denna station</p>
                </div>
            </div>
            
            @if($isLoggedIn)
                <flux:button 
                    wire:click="showAddAdditionalTaskForm"
                    variant="primary"
                    icon="plus"
                    size="sm"
                >
                    Lägg till extra uppgift
                </flux:button>
            @endif
        </div>

        {{-- Additional Task Form --}}
        @if($showAdditionalTaskForm)
            <div class="mb-6 p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Lägg till extra utförd uppgift</h3>
                
                <div class="space-y-4">
                    {{-- Template Selection --}}
                    <div>
                        <flux:label>Välj från mall</flux:label>
                        <flux:select wire:model.live="selectedTemplateId" placeholder="Välj en uppgift från mallen...">
                            @foreach($taskTemplates as $template)
                                <option value="{{ $template->id }}">{{ $template->name }}</option>
                            @endforeach
                        </flux:select>
                    </div>

                    {{-- Custom Task Name --}}
                    @if(!$selectedTemplateId)
                        <div>
                            <flux:label>Eller ange anpassad uppgift</flux:label>
                            <flux:input 
                                wire:model="customTaskName" 
                                placeholder="T.ex. Gräsklippning, Rensning av mossa..."
                            />
                        </div>
                    @endif

                    {{-- Notes --}}
                    <div>
                        <flux:label>Anteckningar (valfritt)</flux:label>
                        <flux:textarea 
                            wire:model="additionalTaskNotes" 
                            placeholder="Lägg till detaljer om vad som utfördes..."
                            rows="2"
                        />
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-2">
                        <flux:button 
                            wire:click="saveAdditionalTask"
                            variant="primary"
                        >
                            Spara uppgift
                        </flux:button>
                        <flux:button 
                            wire:click="hideAddAdditionalTaskForm"
                            variant="subtle"
                        >
                            Avbryt
                        </flux:button>
                    </div>
                </div>
            </div>
        @endif

        @if($taskData->count() > 0)
            <div class="space-y-4">
                @foreach($taskData as $data)
                    <div class="card-modern p-6 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">
                                    {{ $data['task']->name }}
                                </h3>
                                @if($data['task']->description)
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ $data['task']->description }}</p>
                                @endif
                                <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                                    <span class="inline-flex items-center gap-1">
                                        <x-heroicon-s-clock class="w-3 h-3" />
                                        {{ $data['interval_text'] }}
                                    </span>
                                    @if($data['task']->default_due_time)
                                        <span class="inline-flex items-center gap-1">
                                            <x-heroicon-s-bell class="w-3 h-3" />
                                            Senast {{ $data['task']->default_due_time->format('H:i') }}
                                        </span>
                                    @endif
                                    <span class="inline-flex items-center gap-1">
                                        <x-heroicon-s-check-circle class="w-3 h-3" />
                                        {{ $data['completed_count'] }} gånger slutförd
                                    </span>
                                </div>
                            </div>

                            {{-- Dagens uppgift status --}}
                            @if($data['today_task'])
                                <div class="flex items-center gap-3">
                                    @if($data['today_task']->status === 'completed')
                                        <button 
                                            wire:click="uncompleteTask({{ $data['today_task']->id }})"
                                            class="w-8 h-8 rounded-full bg-success-500 hover:bg-success-600 flex items-center justify-center transition-colors"
                                            title="Klicka för att avmarkera"
                                        >
                                            <x-heroicon-s-check class="w-4 h-4 text-white" />
                                        </button>
                                        <div class="status-badge bg-success-100 text-success-800 dark:bg-success-800 dark:text-success-200">
                                            Klar idag
                                        </div>
                                    @elseif($data['today_task']->status === 'overdue')
                                        <input 
                                            type="checkbox" 
                                            class="w-6 h-6 text-primary-600 rounded focus:ring-primary-500"
                                            wire:click="completeTask({{ $data['today_task']->id }})"
                                        />
                                        <div class="status-badge bg-danger-100 text-danger-800 dark:bg-danger-800 dark:text-danger-200">
                                            Försenad
                                        </div>
                                    @else
                                        <input 
                                            type="checkbox" 
                                            class="w-6 h-6 text-primary-600 rounded focus:ring-primary-500"
                                            wire:click="completeTask({{ $data['today_task']->id }})"
                                        />
                                        <div class="status-badge bg-warning-100 text-warning-800 dark:bg-warning-800 dark:text-warning-200">
                                            Väntar
                                        </div>
                                    @endif
                                    
                                    {{-- Comment icon --}}
                                    <button 
                                        wire:click="toggleCommentForm({{ $data['today_task']->id }})"
                                        class="p-1 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors"
                                        title="Lägg till kommentar"
                                    >
                                        <x-heroicon-o-chat-bubble-left-ellipsis class="w-4 h-4" />
                                    </button>
                                </div>
                            @else
                                <div class="status-badge bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                    Ingen idag
                                </div>
                            @endif
                        </div>

                        {{-- Historik information --}}
                        <div class="grid md:grid-cols-2 gap-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Senast utförd</h4>
                                @if($data['last_completed'])
                                    <div class="text-sm">
                                        <div class="text-gray-900 dark:text-gray-100">
                                            {{ $data['last_completed']->completed_at->format('j M Y, H:i') }}
                                        </div>
                                        <div class="text-gray-600 dark:text-gray-400">
                                            av {{ $data['last_completed']->completedBy->name }}
                                        </div>
                                    </div>
                                @else
                                    <div class="text-sm text-gray-500 dark:text-gray-400 italic">
                                        Aldrig utförd
                                    </div>
                                @endif
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nästa schemalagd</h4>
                                @if($data['next_scheduled'])
                                    <div class="text-sm">
                                        <div class="text-gray-900 dark:text-gray-100">
                                            {{ $data['next_scheduled']->scheduled_date->format('j M Y') }}
                                        </div>
                                        @if($data['next_scheduled']->due_time)
                                            <div class="text-gray-600 dark:text-gray-400">
                                                senast {{ $data['next_scheduled']->due_time->format('H:i') }}
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-sm text-gray-500 dark:text-gray-400 italic">
                                        Inte schemalagd
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        {{-- Comment Form and Display --}}
                        @if($data['today_task'])
                            {{-- Comment Form --}}
                            @if(isset($showCommentForm[$data['today_task']->id]) && $showCommentForm[$data['today_task']->id])
                                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                                    <div class="flex flex-col gap-2">
                                        <flux:textarea 
                                            wire:model="taskComments.{{ $data['today_task']->id }}"
                                            placeholder="Lägg till en kommentar..."
                                            rows="2"
                                            class="text-sm"
                                        />
                                        <div class="flex gap-2">
                                            <flux:button 
                                                wire:click="saveTaskComment({{ $data['today_task']->id }})"
                                                variant="primary"
                                                size="sm"
                                            >
                                                Spara
                                            </flux:button>
                                            <flux:button 
                                                wire:click="cancelComment({{ $data['today_task']->id }})"
                                                variant="subtle"
                                                size="sm"
                                            >
                                                Avbryt
                                            </flux:button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            {{-- Display existing comment --}}
                            @if($data['today_task']->notes && (!isset($showCommentForm[$data['today_task']->id]) || !$showCommentForm[$data['today_task']->id]))
                                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                                    <div class="flex items-start gap-2">
                                        <x-heroicon-s-chat-bubble-left-ellipsis class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                        <p class="text-sm text-gray-600 dark:text-gray-400 italic">
                                            {{ $data['today_task']->notes }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <x-heroicon-o-clipboard-document-list class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                <p class="text-gray-600 dark:text-gray-400">Inga uppgifter kopplade till denna station.</p>
            </div>
        @endif

        {{-- Today's Additional Tasks --}}
        @if($todayAdditionalTasks->count() > 0)
            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                    <x-heroicon-o-plus-circle class="w-5 h-5 text-green-600" />
                    Extra uppgifter utförda idag
                </h3>
                
                <div class="space-y-3">
                    @foreach($todayAdditionalTasks as $additionalTask)
                        <div class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center">
                                    <x-heroicon-s-check class="w-4 h-4 text-white" />
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ $additionalTask->task_name }}
                                    </div>
                                    @if($additionalTask->notes)
                                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            {{ $additionalTask->notes }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right text-sm text-gray-500 dark:text-gray-400">
                                <div>{{ $additionalTask->user->name }}</div>
                                <div>{{ $additionalTask->created_at->format('H:i') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Lagerstatus --}}
    @if($lowStockItems->count() > 0 || $inventoryItems->count() > 0)
        <div class="card-modern-elevated p-6 bg-white dark:bg-gray-800">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl gradient-warning shadow-lg shadow-orange-500/25 flex items-center justify-center">
                    <x-heroicon-o-archive-box class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Lagerstatus</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Översikt av inventarie och artiklar</p>
                </div>
            </div>

            {{-- Lågt lager varning --}}
            @if($lowStockItems->count() > 0)
                <div class="mb-6 card-modern p-4 bg-warning-50 dark:bg-warning-900/20 border-warning-200 dark:border-warning-700">
                    <div class="flex items-center gap-3 mb-3">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-warning-600" />
                        <h4 class="font-semibold text-warning-800 dark:text-warning-200">Lågt lager varning</h4>
                    </div>
                    <div class="grid md:grid-cols-2 gap-2">
                        @foreach($lowStockItems as $item)
                            <div class="text-sm text-warning-700 dark:text-warning-300">
                                <strong>{{ $item->inventoryItem->name }}</strong>: 
                                {{ $item->current_quantity }} {{ $item->inventoryItem->formatted_unit }}
                                <span class="text-xs opacity-75">(min: {{ $item->minimum_quantity }})</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Lagersaldo översikt --}}
            @if($inventoryItems->count() > 0)
                <div class="space-y-3">
                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 text-sm">Aktuellt lagersaldo</h4>
                    <div class="grid md:grid-cols-2 gap-3">
                        @foreach($inventoryItems as $inventory)
                            <div class="flex items-center justify-between p-3 card-modern bg-gray-50 dark:bg-gray-800">
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $inventory->inventoryItem->name }}</div>
                                    @if($inventory->notes)
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $inventory->notes }}</div>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <div class="font-mono font-bold text-gray-700 dark:text-gray-300">
                                        {{ $inventory->current_quantity }} {{ $inventory->inventoryItem->formatted_unit }}
                                    </div>
                                    @if($inventory->is_low_stock)
                                        <div class="text-xs text-warning-600">Lågt lager</div>
                                    @elseif($inventory->current_quantity <= 0)
                                        <div class="text-xs text-danger-600">Slut</div>
                                    @else
                                        <div class="text-xs text-success-600">I lager</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @if($inventoryItems->count() >= 10)
                        <div class="text-center pt-3">
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                Visar 10 artiklar med lägst saldo. För fullständig oversikt, kontakta admin.
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif

    {{-- Senaste aktivitet --}}
    @if($recentTimeLogs->count() > 0)
        <div class="card-modern-elevated p-6 bg-white dark:bg-gray-800">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl gradient-info shadow-lg shadow-blue-500/25 flex items-center justify-center">
                    <x-heroicon-o-clock class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Senaste aktivitet</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Senaste {{ $recentTimeLogs->count() }} arbetspassen</p>
                </div>
            </div>

            <div class="space-y-3">
                @foreach($recentTimeLogs as $timeLog)
                    <div class="flex items-center justify-between p-4 card-modern bg-gray-50 dark:bg-gray-800">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full gradient-primary flex items-center justify-center">
                                <span class="text-white text-sm font-semibold">
                                    {{ $timeLog->user->initials() }}
                                </span>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $timeLog->user->name }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $timeLog->clock_in->format('j M, H:i') }} - {{ $timeLog->clock_out->format('H:i') }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-mono font-bold text-gray-700 dark:text-gray-300">
                                {{ number_format($timeLog->total_minutes / 60, 1) }}h
                            </div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">
                                {{ round($timeLog->total_minutes) }} min
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>