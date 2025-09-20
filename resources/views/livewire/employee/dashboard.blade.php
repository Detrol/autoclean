<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Dashboard</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, j F Y') }}</p>
        </div>
        
        <div class="flex items-center gap-4">
            <flux:input 
                wire:model.live="selectedDate" 
                type="date" 
                class="w-40"
            />
        </div>
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

    {{-- Enhanced Statistics - Endast för admin --}}
    @can('admin')
    <div class="grid gap-6 md:grid-cols-4 mb-8">
        <div class="card-modern p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center gradient-success shadow-lg shadow-green-500/25">
                        <x-heroicon-o-check class="w-6 h-6 text-green-50" style="width: 24px; height: 24px;" />
                    </div>
                </div>
                <div class="ml-5">
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['completed_today'] }}</p>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Slutförda uppgifter</p>
                </div>
            </div>
        </div>

        <div class="card-modern p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center gradient-warning shadow-lg shadow-yellow-500/25">
                        <x-heroicon-o-clock class="w-6 h-6 text-yellow-50" style="width: 24px; height: 24px;" />
                    </div>
                </div>
                <div class="ml-5">
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['pending_today'] }}</p>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Väntande uppgifter</p>
                </div>
            </div>
        </div>

        <div class="card-modern p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center gradient-danger shadow-lg shadow-red-500/25">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-red-50" style="width: 24px; height: 24px;" />
                    </div>
                </div>
                <div class="ml-5">
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['overdue_today'] }}</p>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Försenade uppgifter</p>
                </div>
            </div>
        </div>

        <div class="card-modern p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center gradient-primary shadow-lg shadow-blue-500/25">
                        <x-heroicon-o-clock class="w-6 h-6 text-blue-50" style="width: 24px; height: 24px;" />
                    </div>
                </div>
                <div class="ml-5">
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($stats['total_hours_today'], 1) }}h</p>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Totala timmar</p>
                </div>
            </div>
        </div>
    </div>
    @endcan

    {{-- Lagervarningar --}}
    @if($criticalInventoryAlerts->count() > 0)
        <div class="mb-8 card-modern-elevated p-6 border-l-4 border-warning-500 bg-white dark:bg-gray-800">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center gradient-warning shadow-lg shadow-orange-500/25">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Lagervarningar</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $criticalInventoryAlerts->count() }} artikel{{ $criticalInventoryAlerts->count() > 1 ? 'ar' : '' }} behöver påfyllning</p>
                </div>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($criticalInventoryAlerts as $alert)
                    <div class="p-4 card-modern bg-warning-50 dark:bg-warning-900/20 border-warning-200 dark:border-warning-700">
                        <div class="flex items-center justify-between mb-2">
                            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $alert->inventoryItem->name }}</div>
                            <div class="text-xs bg-warning-100 text-warning-800 px-2 py-1 rounded-full">
                                {{ $alert->station->name }}
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                Aktuellt: <strong>{{ $alert->current_quantity }} {{ $alert->inventoryItem->formatted_unit }}</strong>
                            </div>
                            @if($alert->current_quantity <= 0)
                                <div class="text-xs text-danger-600 font-semibold">SLUT</div>
                            @else
                                <div class="text-xs text-warning-600 font-semibold">LÅGT</div>
                            @endif
                        </div>
                        @if($alert->minimum_quantity > 0)
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Minimum: {{ $alert->minimum_quantity }} {{ $alert->inventoryItem->formatted_unit }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Enhanced Active Time Logs --}}
    @if($activeTimeLogs->count() > 0)
        <div class="mb-8 card-modern-elevated p-6 border-l-4 border-primary-500 bg-white dark:bg-gray-800">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center gradient-primary shadow-lg shadow-blue-500/25">
                    <x-heroicon-o-clock class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Aktiva arbetspass</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Du är för närvarande inklockat på {{ $activeTimeLogs->count() }} station{{ $activeTimeLogs->count() > 1 ? 'er' : '' }}</p>
                </div>
            </div>
            <div class="space-y-4">
                @foreach($activeTimeLogs as $timeLog)
                    <div class="card-modern-elevated p-4 bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-900/30 dark:to-primary-800/30 border-primary-200 dark:border-primary-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                                <div>
                                    <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $timeLog->station->name }}</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">
                                            Startad: {{ $timeLog->clock_in->format('H:i') }}
                                        </span>
                                        @if($timeLog->is_oncall)
                                            <flux:badge variant="purple" size="sm">Jour</flux:badge>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <div class="text-lg font-mono font-bold text-primary-700 dark:text-primary-300">
                                        <span data-timer="{{ $timeLog->clock_in->timestamp }}" class="live-timer">
                                            {{ gmdate('H:i:s', $timeLog->clock_in->diffInSeconds(now())) }}
                                        </span>
                                    </div>
                                </div>
                                <flux:button 
                                    size="sm" 
                                    variant="danger"
                                    wire:click="clockOut({{ $timeLog->id }})"
                                    wire:confirm="Är du säker på att du vill klocka ut?"
                                    class="gradient-danger text-white cursor-pointer"
                                >
                                    Klocka ut
                                </flux:button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Dagens avslutade arbetspass --}}
    @if($completedTimeLogs->count() > 0)
        <div class="mb-8 card-modern-elevated p-6 bg-white dark:bg-gray-800">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center gradient-success shadow-lg shadow-green-500/25">
                    <x-heroicon-o-check-circle class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Dagens arbetspass</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $completedTimeLogs->count() }} avslutade pass</p>
                </div>
            </div>
            <div class="space-y-3">
                @foreach($completedTimeLogs as $timeLog)
                    <div class="card-modern p-4 bg-gray-50 dark:bg-gray-800">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                <div>
                                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $timeLog->station->name }}</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $timeLog->clock_in->format('H:i') }} - {{ $timeLog->clock_out->format('H:i') }}
                                        </span>
                                        @if($timeLog->is_oncall)
                                            <flux:badge variant="purple" size="sm">Jour</flux:badge>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-mono font-bold text-gray-700 dark:text-gray-300">
                                    {{ number_format($timeLog->total_minutes / 60, 1) }}h
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">
                                    {{ round($timeLog->total_minutes) }} min
                                </div>
                            </div>
                        </div>
                        @if($timeLog->notes)
                            <div class="mt-2 text-sm text-gray-600 dark:text-gray-400 italic">
                                Anteckning: {{ $timeLog->notes }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Enhanced Stations and Tasks --}}
    <div class="grid gap-8 lg:grid-cols-2">
        @forelse($userStations as $station)
            <div class="card-modern-elevated overflow-hidden bg-white dark:bg-gray-800">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg gradient-purple shadow-lg shadow-purple-500/25 flex items-center justify-center">
                                <x-heroicon-o-building-storefront class="w-4 h-4 text-white" />
                            </div>
                            <a href="{{ route('station.details', $station->id) }}" wire:navigate class="text-xl font-semibold text-gray-900 dark:text-gray-100 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                {{ $station->name }}
                            </a>
                        </div>
                        
                        @if(!auth()->user()->hasActiveTimeLog($station->id))
                            <div class="flex gap-2">
                                <flux:button
                                    size="sm"
                                    variant="primary"
                                    wire:click="clockIn({{ $station->id }})"
                                    icon="clock"
                                    class="gradient-primary text-white cursor-pointer"
                                >
                                    Klocka in
                                </flux:button>
                                <flux:button
                                    size="sm"
                                    variant="purple"
                                    wire:click="clockInOncall({{ $station->id }})"
                                    icon="phone"
                                    class="gradient-purple text-white cursor-pointer"
                                    title="Klocka in för jour"
                                >
                                    Jour
                                </flux:button>
                            </div>
                        @else
                            <div class="status-badge bg-success-100 text-success-800 dark:bg-success-800 dark:text-success-200">
                                <x-heroicon-o-check class="w-3 h-3" />
                                Inklockat
                            </div>
                        @endif
                    </div>
                    @if($station->description)
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $station->description }}</p>
                    @endif
                </div>

                <div class="p-6">
                    @php $stationTasks = $tasksByStation->get($station->id, collect()) @endphp
                    
                    @if($stationTasks->count() > 0)
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <div class="w-5 h-5 rounded gradient-orange shadow-lg shadow-orange-500/25 flex items-center justify-center">
                                    <x-heroicon-o-clipboard-document-list class="w-3 h-3 text-white" style="width: 12px; height: 12px;" />
                                </div>
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Dagens uppgifter ({{ $stationTasks->count() }})</h4>
                            </div>
                            <a href="{{ route('station.details', $station->id) }}" wire:navigate class="text-sm text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-200 transition-colors">
                                Visa alla uppgifter →
                            </a>
                        </div>
                        <div class="space-y-3">
                            @foreach($stationTasks as $taskSchedule)
                                <div class="card-modern-elevated p-4 
                                    {{ $taskSchedule->status === 'completed' ? 'bg-success-50 dark:bg-success-900/20 border-success-200 dark:border-success-700' : 
                                       ($taskSchedule->status === 'overdue' ? 'bg-danger-50 dark:bg-danger-900/20 border-danger-200 dark:border-danger-700' : 
                                        'bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700') }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            @if($taskSchedule->status === 'completed')
                                                <button 
                                                    wire:click="uncompleteTask({{ $taskSchedule->id }})"
                                                    class="w-6 h-6 rounded-full bg-success-500 hover:bg-success-600 flex items-center justify-center transition-colors cursor-pointer"
                                                    title="Klicka för att avmarkera uppgift"
                                                >
                                                    <x-heroicon-s-check class="w-3 h-3 text-white" />
                                                </button>
                                            @else
                                                <input 
                                                    type="checkbox" 
                                                    class="w-5 h-5 text-primary-600 rounded focus:ring-primary-500 cursor-pointer"
                                                    wire:click="completeTask({{ $taskSchedule->id }})"
                                                    style="cursor: pointer;"
                                                />
                                            @endif
                                            
                                            <div>
                                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100 {{ $taskSchedule->status === 'completed' ? 'line-through' : '' }}">
                                                    {{ $taskSchedule->task->name }}
                                                </span>
                                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                    @if($taskSchedule->status === 'completed' && $taskSchedule->completedBy)
                                                        <span>Slutförd av {{ $taskSchedule->completedBy->name }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center gap-2">
                                            <button 
                                                wire:click="toggleCommentForm({{ $taskSchedule->id }})"
                                                class="p-1 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors"
                                                title="Lägg till kommentar"
                                            >
                                                <x-heroicon-o-chat-bubble-left-ellipsis class="w-4 h-4" />
                                            </button>
                                            
                                            <div class="status-badge 
                                                {{ $taskSchedule->status === 'completed' ? 'bg-success-100 text-success-800 dark:bg-success-800 dark:text-success-200' : 
                                                   ($taskSchedule->status === 'overdue' ? 'bg-danger-100 text-danger-800 dark:bg-danger-800 dark:text-danger-200' : 
                                                    'bg-warning-100 text-warning-800 dark:bg-warning-800 dark:text-warning-200') }}">
                                                {{ $taskSchedule->status === 'completed' ? 'Klar' : 
                                                   ($taskSchedule->status === 'overdue' ? 'Försenad' : 'Väntande') }}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Comment Form --}}
                                    @if(isset($showCommentForm[$taskSchedule->id]) && $showCommentForm[$taskSchedule->id])
                                        <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600">
                                            <div class="flex flex-col gap-2">
                                                <flux:textarea 
                                                    wire:model="taskComments.{{ $taskSchedule->id }}"
                                                    placeholder="Lägg till en kommentar..."
                                                    rows="2"
                                                    class="text-sm"
                                                />
                                                <div class="flex gap-2">
                                                    <flux:button 
                                                        wire:click="saveTaskComment({{ $taskSchedule->id }})"
                                                        variant="primary"
                                                        size="sm"
                                                    >
                                                        Spara
                                                    </flux:button>
                                                    <flux:button 
                                                        wire:click="cancelComment({{ $taskSchedule->id }})"
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
                                    @if($taskSchedule->notes && (!isset($showCommentForm[$taskSchedule->id]) || !$showCommentForm[$taskSchedule->id]))
                                        <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600">
                                            <div class="flex items-start gap-2">
                                                <x-heroicon-s-chat-bubble-left-ellipsis class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                                <p class="text-sm text-gray-600 dark:text-gray-400 italic">
                                                    {{ $taskSchedule->notes }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-gray-400 mx-auto mb-4" style="width: 20px; height: 20px;" />
                            <p class="text-sm text-gray-600 dark:text-gray-400">Inga uppgifter för idag</p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Återkom senare för nya uppgifter</p>
                        </div>
                    @endif
                    
                    {{-- Additional Tasks Section --}}
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <div class="w-5 h-5 rounded gradient-purple shadow-lg shadow-purple-500/25 flex items-center justify-center">
                                    <x-heroicon-o-plus class="w-3 h-3 text-white" style="width: 12px; height: 12px;" />
                                </div>
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Extra uppgifter</h4>
                            </div>
                            
                            @if(auth()->user()->hasActiveTimeLog($station->id) || auth()->user()->is_admin)
                                @if(!isset($showAdditionalTaskForm[$station->id]) || !$showAdditionalTaskForm[$station->id])
                                    <flux:button 
                                        wire:click="showAddAdditionalTaskForm({{ $station->id }})"
                                        variant="primary"
                                        size="sm"
                                        icon="plus"
                                        class="gradient-purple text-white cursor-pointer"
                                    >
                                        Lägg till extra uppgift
                                    </flux:button>
                                @endif
                            @else
                                <span class="text-xs text-gray-500 dark:text-gray-400">Klocka in för att lägga till extra uppgifter</span>
                            @endif
                        </div>

                        {{-- Additional Task Form --}}
                        @if(isset($showAdditionalTaskForm[$station->id]) && $showAdditionalTaskForm[$station->id])
                            <div class="card-modern p-4 bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-700 mb-4">
                                <h5 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Lägg till extra uppgift</h5>
                                
                                <div class="space-y-3">
                                    {{-- Template Selection --}}
                                    <div>
                                        <flux:select 
                                            wire:model.live="selectedTemplateId.{{ $station->id }}"
                                            placeholder="Välj en mall eller ange anpassat namn"
                                        >
                                            <option value="">Anpassat namn (skriv nedan)</option>
                                            @foreach($taskTemplates as $template)
                                                <option value="{{ $template->id }}">{{ $template->name }}</option>
                                            @endforeach
                                        </flux:select>
                                    </div>

                                    {{-- Custom Task Name (only if no template selected) --}}
                                    @if(!($selectedTemplateId[$station->id] ?? null))
                                        <div>
                                            <flux:input 
                                                wire:model="customTaskName.{{ $station->id }}"
                                                placeholder="Ange namn på uppgiften (t.ex. gräsklippning, rensning av mossa...)"
                                            />
                                        </div>
                                    @endif

                                    {{-- Notes --}}
                                    <div>
                                        <flux:textarea 
                                            wire:model="additionalTaskNotes.{{ $station->id }}"
                                            placeholder="Anteckningar (valfritt)"
                                            rows="2"
                                        />
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="flex gap-2">
                                        <flux:button 
                                            wire:click="saveAdditionalTask({{ $station->id }})"
                                            variant="primary"
                                            size="sm"
                                        >
                                            Spara uppgift
                                        </flux:button>
                                        <flux:button 
                                            wire:click="hideAddAdditionalTaskForm({{ $station->id }})"
                                            variant="subtle"
                                            size="sm"
                                        >
                                            Avbryt
                                        </flux:button>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Display Today's Additional Tasks --}}
                        @php $stationAdditionalTasks = $todayAdditionalTasks->get($station->id, collect()) @endphp
                        @if($stationAdditionalTasks->count() > 0)
                            <div class="space-y-2">
                                @foreach($stationAdditionalTasks as $additionalTask)
                                    <div class="card-modern p-3 bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-700">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                                                <div>
                                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $additionalTask->task_name }}
                                                    </span>
                                                    <div class="text-xs text-gray-600 dark:text-gray-400">
                                                        Av {{ $additionalTask->user->name }} 
                                                        • {{ $additionalTask->created_at->format('H:i') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="status-badge bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-200">
                                                Extra
                                            </div>
                                        </div>
                                        @if($additionalTask->notes)
                                            <div class="mt-2 text-xs text-gray-600 dark:text-gray-400 italic">
                                                {{ $additionalTask->notes }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <p class="text-xs text-gray-500 dark:text-gray-500">Inga extra uppgifter tillagda än idag</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-2">
                <div class="text-center p-8 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                    <p class="text-gray-600 dark:text-gray-400">Du har inte tilldelats några stationer än.</p>
                    <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">Kontakta en admin för att få tillgång till stationer.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateTimers() {
        const timers = document.querySelectorAll('.live-timer');
        const now = Math.floor(Date.now() / 1000);
        
        timers.forEach(timer => {
            const startTime = parseInt(timer.dataset.timer);
            const elapsed = now - startTime;
            
            const hours = Math.floor(elapsed / 3600);
            const minutes = Math.floor((elapsed % 3600) / 60);
            const seconds = elapsed % 60;
            
            const timeString = [hours, minutes, seconds]
                .map(val => val.toString().padStart(2, '0'))
                .join(':');
            
            timer.textContent = timeString;
        });
    }
    
    // Uppdatera varje sekund
    setInterval(updateTimers, 1000);
    
    // Kör direkt också
    updateTimers();
});
</script>