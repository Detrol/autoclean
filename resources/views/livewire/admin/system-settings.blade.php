<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Systeminställningar</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Hantera applikationens inställningar</p>
    </div>

    @if (session()->has('message'))
        <div class="mb-6 card-modern p-4 bg-success-50 dark:bg-success-900/20 border-success-200 dark:border-success-700">
            <div class="flex items-center gap-3">
                <x-heroicon-o-check class="w-5 h-5 text-success-600" />
                <span class="text-success-800 dark:text-success-200">{{ session('message') }}</span>
            </div>
        </div>
    @endif

    <form wire:submit="save">
        @foreach($groupedSettings as $group => $groupSettings)
            <div class="card-modern-elevated p-6 mb-6 bg-white dark:bg-gray-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 capitalize">
                    {{ ucfirst($group) }}
                </h2>

                <div class="space-y-4">
                    @foreach($groupSettings as $setting)
                        <div class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-700 last:border-0">
                            <div class="flex-1">
                                <label class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $setting->label }}
                                </label>
                                @if($setting->description)
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                        {{ $setting->description }}
                                    </p>
                                @endif
                            </div>

                            <div class="ml-4">
                                @if($setting->type === 'boolean')
                                    <flux:switch wire:model="settings.{{ $setting->key }}" />
                                @elseif($setting->type === 'integer')
                                    <flux:input type="number" wire:model="settings.{{ $setting->key }}" class="w-32" />
                                @else
                                    <flux:input wire:model="settings.{{ $setting->key }}" class="w-64" />
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="flex justify-end">
            <flux:button type="submit" variant="primary">
                Spara inställningar
            </flux:button>
        </div>
    </form>
</div>
