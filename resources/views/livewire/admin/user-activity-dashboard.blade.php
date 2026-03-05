<div class="p-8 max-w-full">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Användaraktivitet
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Översikt över alla användares tid och uppgifter
            </p>
        </div>

        <!-- Period selector -->
        <div class="flex gap-2">
            <flux:button.group>
                <flux:button
                    wire:click="setPeriod('day')"
                    :variant="$periodType === 'day' ? 'filled' : 'ghost'"
                    size="sm">
                    Dag
                </flux:button>
                <flux:button
                    wire:click="setPeriod('week')"
                    :variant="$periodType === 'week' ? 'filled' : 'ghost'"
                    size="sm">
                    Vecka
                </flux:button>
                <flux:button
                    wire:click="setPeriod('month')"
                    :variant="$periodType === 'month' ? 'filled' : 'ghost'"
                    size="sm">
                    Månad
                </flux:button>
                <flux:button
                    wire:click="setPeriod('year')"
                    :variant="$periodType === 'year' ? 'filled' : 'ghost'"
                    size="sm">
                    År
                </flux:button>
            </flux:button.group>
        </div>
    </div>

    <!-- Flash messages -->
    @if (session()->has('success'))
        <flux:callout variant="success" class="mb-4" icon="check-circle">
            {{ session('success') }}
        </flux:callout>
    @endif

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- User filter -->
            <flux:select wire:model.live="selectedUserId" placeholder="Alla användare">
                <option value="">Alla användare</option>
                @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </flux:select>

            <!-- Station filter -->
            <flux:select wire:model.live="selectedStationId" placeholder="Alla stationer">
                <option value="">Alla stationer</option>
                @foreach($stations as $station)
                <option value="{{ $station->id }}">{{ $station->name }}</option>
                @endforeach
            </flux:select>

            <!-- Work type filter -->
            <flux:select wire:model.live="workType">
                <option value="all">Alla typer</option>
                <option value="regular">Ordinarie</option>
                <option value="oncall">Jour</option>
            </flux:select>

            <!-- Date picker -->
            <flux:input type="date" wire:model.live="selectedDate" />
        </div>
    </div>

    <!-- Period navigation -->
    <div class="flex items-center justify-between mb-6 p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
        <flux:button wire:click="previousPeriod" variant="ghost" icon="chevron-left">
            Föregående
        </flux:button>

        <div class="text-center">
            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ $periodLabel }}
            </div>
            <flux:button wire:click="currentPeriod" variant="subtle" size="xs" class="mt-1">
                Gå till idag
            </flux:button>
        </div>

        <flux:button wire:click="nextPeriod" variant="ghost" icon-trailing="chevron-right">
            Nästa
        </flux:button>

        <!-- Export dropdown -->
        <flux:dropdown position="bottom" align="end">
            <flux:button variant="filled" icon="arrow-down-tray">
                Exportera
            </flux:button>
            <flux:menu>
                <flux:menu.item :href="$this->exportUrl('csv')" icon="document-arrow-down">
                    CSV
                </flux:menu.item>
                <flux:menu.item :href="$this->exportUrl('pdf')" icon="document-text">
                    PDF
                </flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </div>

    <!-- Tabs -->
    <div class="mb-6">
        <flux:button.group>
            <flux:button
                wire:click="setTab('time')"
                :variant="$activeTab === 'time' ? 'filled' : 'ghost'">
                Tidsloggar
            </flux:button>
            <flux:button
                wire:click="setTab('tasks')"
                :variant="$activeTab === 'tasks' ? 'filled' : 'ghost'">
                Uppgifter
            </flux:button>
        </flux:button.group>
    </div>

    <!-- Statistics Cards -->
    @include('livewire.admin.partials.user-activity-stats', ['stats' => $timeStats])

    <!-- Content based on active tab -->
    @if($activeTab === 'time')
        @include('livewire.admin.partials.user-time-table', [
            'userTimeBreakdown' => $userTimeBreakdown,
            'timeLogs' => $timeLogs
        ])
    @else
        @include('livewire.admin.partials.task-completion-table', [
            'taskCompletions' => $taskCompletions,
            'additionalTasks' => $additionalTasks
        ])
    @endif

    <!-- Time Log Edit/Create Modal -->
    <flux:modal wire:model.self="showTimeLogModal" @close="closeTimeLogModal" class="max-w-lg">
        <div class="space-y-6">
            <flux:heading size="lg">
                {{ $isCreating ? 'Ny tidslogg' : 'Redigera tidslogg' }}
            </flux:heading>

            <div class="space-y-4">
                <!-- User -->
                <flux:select wire:model="formUserId" label="Användare" :disabled="!$isCreating">
                    <option value="">Välj användare...</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </flux:select>
                @error('formUserId') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                <!-- Station -->
                <flux:select wire:model="formStationId" label="Station">
                    <option value="">Välj station...</option>
                    @foreach($stations as $station)
                        <option value="{{ $station->id }}">{{ $station->name }}</option>
                    @endforeach
                </flux:select>
                @error('formStationId') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                <!-- Date -->
                <flux:input type="date" wire:model="formDate" label="Datum" />
                @error('formDate') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                <!-- Clock in / Clock out side by side -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:input type="time" wire:model="formClockIn" label="Starttid" />
                        @error('formClockIn') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <flux:input type="time" wire:model="formClockOut" label="Sluttid" />
                        @error('formClockOut') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- On-call switch -->
                <flux:switch wire:model="formIsOncall" label="Jour" />

                <!-- Notes -->
                <flux:textarea wire:model="formNotes" label="Anteckningar" rows="2" placeholder="Valfria anteckningar..." />
                @error('formNotes') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-2">
                <div>
                    @if(!$isCreating)
                        <flux:button wire:click="deleteTimeLog" wire:confirm="Är du säker på att du vill ta bort denna tidslogg?" variant="danger" size="sm">
                            Ta bort
                        </flux:button>
                    @endif
                </div>
                <div class="flex gap-2">
                    <flux:button wire:click="closeTimeLogModal" variant="ghost">
                        Avbryt
                    </flux:button>
                    <flux:button wire:click="saveTimeLog" variant="primary">
                        {{ $isCreating ? 'Skapa' : 'Spara' }}
                    </flux:button>
                </div>
            </div>
        </div>
    </flux:modal>
</div>
