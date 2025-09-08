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

    {{-- Enhanced Statistics --}}
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

    {{-- Enhanced Active Time Logs --}}
    @if($activeTimeLogs->count() > 0)
        <div class="mb-8 card-modern p-6 border-l-4 border-primary-500">
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
                    <div class="card-modern p-4 bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-900/30 dark:to-primary-800/30 border-primary-200 dark:border-primary-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                                <div>
                                    <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $timeLog->station->name }}</span>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        Startad: {{ $timeLog->clock_in->format('H:i') }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <div class="text-lg font-mono font-bold text-primary-700 dark:text-primary-300">
                                        {{ $timeLog->clock_in->diffForHumans() }}
                                    </div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">
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
                                    class="btn-primary-modern"
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

    {{-- Enhanced Stations and Tasks --}}
    <div class="grid gap-8 lg:grid-cols-2">
        @forelse($userStations as $station)
            <div class="card-modern overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                <x-heroicon-o-building-storefront class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $station->name }}</h3>
                        </div>
                        
                        @if(!auth()->user()->hasActiveTimeLog($station->id))
                            <flux:button 
                                size="sm" 
                                variant="primary"
                                wire:click="clockIn({{ $station->id }})"
                                icon="clock"
                            >
                                Klocka in
                            </flux:button>
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
                        <div class="flex items-center gap-2 mb-4">
                            <x-heroicon-o-clipboard-document-list class="w-4 h-4 text-gray-600" style="width: 16px; height: 16px;" />
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Dagens uppgifter ({{ $stationTasks->count() }})</h4>
                        </div>
                        <div class="space-y-3">
                            @foreach($stationTasks as $taskSchedule)
                                <div class="card-modern p-4 
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
                                                    class="w-5 h-5 text-primary-600 rounded focus:ring-primary-500"
                                                    wire:click="completeTask({{ $taskSchedule->id }})"
                                                />
                                            @endif
                                            
                                            <div>
                                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100 {{ $taskSchedule->status === 'completed' ? 'line-through' : '' }}">
                                                    {{ $taskSchedule->task->name }}
                                                </span>
                                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                    @if($taskSchedule->due_time)
                                                        <span class="inline-flex items-center gap-1">
                                                            <x-heroicon-s-clock class="w-3 h-3" />
                                                            Senast: {{ \Carbon\Carbon::parse($taskSchedule->due_time)->format('H:i') }}
                                                        </span>
                                                    @endif
                                                    @if($taskSchedule->status === 'completed' && $taskSchedule->completedBy)
                                                        <span class="ml-2">• Slutförd av {{ $taskSchedule->completedBy->name }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="status-badge 
                                            {{ $taskSchedule->status === 'completed' ? 'bg-success-100 text-success-800 dark:bg-success-800 dark:text-success-200' : 
                                               ($taskSchedule->status === 'overdue' ? 'bg-danger-100 text-danger-800 dark:bg-danger-800 dark:text-danger-200' : 
                                                'bg-warning-100 text-warning-800 dark:bg-warning-800 dark:text-warning-200') }}">
                                            {{ $taskSchedule->status === 'completed' ? 'Klar' : 
                                               ($taskSchedule->status === 'overdue' ? 'Försenad' : 'Väntande') }}
                                        </div>
                                    </div>
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