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
                                            <span class="px-2 py-0.5 text-xs rounded-full bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-200">Jour</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-mono font-bold text-primary-700 dark:text-primary-300">
                                    <span data-timer="{{ $timeLog->clock_in->timestamp }}" class="live-timer">
                                        {{ gmdate('H:i:s', $timeLog->clock_in->diffInSeconds(now())) }}
                                    </span>
                                </div>
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
                                            <span class="px-2 py-0.5 text-xs rounded-full bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-200">Jour</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-mono font-bold text-gray-700 dark:text-gray-300">
                                    {{ app(App\Support\TimeFormatter::class)->formatMinutesSv($timeLog->total_minutes ?? 0) }}
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
            @php
                $stationTasks = $tasksByStation->get($station->id, collect());
                $totalTasks = $stationTasks->count();
                $completedTasks = $stationTasks->where('status', 'completed')->count();
            @endphp
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
                        @if($totalTasks > 0)
                            <div class="status-badge {{ $completedTasks === $totalTasks ? 'bg-success-100 text-success-800 dark:bg-success-800 dark:text-success-200' : 'bg-primary-100 text-primary-800 dark:bg-primary-800 dark:text-primary-200' }}">
                                <x-heroicon-o-clipboard-document-check class="w-4 h-4" />
                                {{ $completedTasks }}/{{ $totalTasks }}
                            </div>
                        @endif
                    </div>
                    @if($station->description)
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $station->description }}</p>
                    @endif
                </div>

                <div class="p-6">
                    <a href="{{ route('station.tasks', $station->id) }}" wire:navigate class="flex items-center justify-between p-4 card-modern hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg gradient-orange shadow-lg shadow-orange-500/25 flex items-center justify-center">
                                <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-white" />
                            </div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">Visa dagens uppgifter</span>
                        </div>
                        <x-heroicon-o-arrow-right class="w-5 h-5 text-gray-400" />
                    </a>
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