<div>
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                <x-heroicon-o-arrow-left class="w-5 h-5" />
                <span class="text-sm">Tillbaka till Dashboard</span>
            </a>
        </div>

        <div class="flex items-center gap-4">
            <flux:input
                wire:model.live="selectedDate"
                type="date"
                class="w-40"
            />
        </div>
    </div>

    <div class="card-modern-elevated p-6 mb-8 bg-white dark:bg-gray-800">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-xl gradient-purple shadow-lg shadow-purple-500/25 flex items-center justify-center">
                <x-heroicon-o-building-storefront class="w-6 h-6 text-white" />
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $station->name }}</h1>
                <p class="text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, j F Y') }}</p>
            </div>
        </div>

        @if(!$isLoggedIn)
            <div class="flex flex-col sm:flex-row gap-2">
                <flux:button
                    wire:click="clockIn(false)"
                    wire:confirm="Är du säker på att du vill klocka in för ordinarie arbetstid?"
                    variant="primary"
                    icon="clock"
                    class="gradient-primary text-white cursor-pointer"
                >
                    Klocka in
                </flux:button>
                <flux:button
                    wire:click="clockInOncall"
                    wire:confirm="Är du säker på att du vill klocka in för jour?"
                    variant="filled"
                    icon="phone"
                    class="gradient-orange !text-white cursor-pointer"
                    title="Klocka in för jour"
                >
                    Jour
                </flux:button>
            </div>
        @else
            <div class="flex justify-start">
                <div class="status-badge bg-success-100 text-success-800 dark:bg-success-800 dark:text-success-200">
                    <x-heroicon-o-check class="w-4 h-4" />
                    Inklockat
                </div>
            </div>
        @endif
    </div>

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

    @if($activeTimeLogs->count() > 0)
        <div class="mb-8 card-modern-elevated p-6 border-l-4 border-primary-500 bg-white dark:bg-gray-800">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center gradient-primary shadow-lg shadow-blue-500/25">
                    <x-heroicon-o-clock class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Aktivt arbetspass</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Du är för närvarande inklockat</p>
                </div>
            </div>
            <div class="space-y-4">
                @foreach($activeTimeLogs as $timeLog)
                    <div class="card-modern-elevated p-4 bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-900/30 dark:to-primary-800/30 border-primary-200 dark:border-primary-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                                <div>
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

    <div class="card-modern-elevated p-6 mb-8 bg-white dark:bg-gray-800">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl gradient-orange shadow-lg shadow-orange-500/25 flex items-center justify-center">
                    <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Dagens uppgifter</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $todaysTasks->count() }} uppgifter för idag</p>
                </div>
            </div>
        </div>

        @if($todaysTasks->count() > 0)
            <div class="space-y-3">
                @foreach($todaysTasks as $taskSchedule)
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
                <x-heroicon-o-clipboard-document-list class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                <p class="text-gray-600 dark:text-gray-400">Inga uppgifter schemalagda för idag</p>
            </div>
        @endif
    </div>

    <div class="card-modern-elevated p-6 bg-white dark:bg-gray-800">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl gradient-purple shadow-lg shadow-purple-500/25 flex items-center justify-center">
                    <x-heroicon-o-plus class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Extra uppgifter</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Lägg till extra utförda uppgifter</p>
                </div>
            </div>

            @if($isLoggedIn || auth()->user()->is_admin)
                @if(!$showAdditionalTaskForm)
                    <flux:button
                        wire:click="showAddAdditionalTaskForm"
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

        @if($showAdditionalTaskForm)
            <div class="card-modern p-4 bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-700 mb-4">
                <h5 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Lägg till extra uppgift</h5>

                <div class="space-y-3">
                    <div>
                        <flux:select
                            wire:model.live="selectedTemplateId"
                            placeholder="Välj en mall eller ange anpassat namn"
                        >
                            <option value="">Anpassat namn (skriv nedan)</option>
                            @foreach($taskTemplates as $template)
                                <option value="{{ $template->id }}">{{ $template->name }}</option>
                            @endforeach
                        </flux:select>
                    </div>

                    @if(!$selectedTemplateId)
                        <div>
                            <flux:input
                                wire:model="customTaskName"
                                placeholder="Ange namn på uppgiften (t.ex. gräsklippning, rensning av mossa...)"
                            />
                        </div>
                    @endif

                    <div>
                        <flux:textarea
                            wire:model="additionalTaskNotes"
                            placeholder="Anteckningar (valfritt)"
                            rows="2"
                        />
                    </div>

                    <div class="flex gap-2">
                        <flux:button
                            wire:click="saveAdditionalTask"
                            variant="primary"
                            size="sm"
                        >
                            Spara uppgift
                        </flux:button>
                        <flux:button
                            wire:click="hideAddAdditionalTaskForm"
                            variant="subtle"
                            size="sm"
                        >
                            Avbryt
                        </flux:button>
                    </div>
                </div>
            </div>
        @endif

        @if($todayAdditionalTasks->count() > 0)
            <div class="space-y-2">
                @foreach($todayAdditionalTasks as $additionalTask)
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

    @if($completedTimeLogs->count() > 0)
        <div class="mt-8 card-modern-elevated p-6 bg-white dark:bg-gray-800">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl gradient-success shadow-lg shadow-green-500/25 flex items-center justify-center">
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

    setInterval(updateTimers, 1000);
    updateTimers();
});
</script>