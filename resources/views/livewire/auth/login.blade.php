<div class="flex flex-col gap-8">
    <!-- Modern Brand Header -->
    <div class="text-center mb-8">
        <div class="mb-6">
            <!-- Enhanced Logo with Gradient -->
            <div class="w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-6 gradient-primary shadow-lg shadow-blue-500/25">
                <x-heroicon-o-building-storefront class="w-10 h-10 text-white" />
            </div>
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-3">WashNode</h1>
            <p class="text-lg text-gray-600 dark:text-gray-400">Stationshanteringssystem för biltvätt</p>
        </div>
    </div>

    <x-auth-header :title="__('Logga in på ditt konto')" :description="__('Ange din e-postadress och lösenord för att logga in')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" wire:submit="login" class="flex flex-col gap-6">
        <!-- Email Address with Enhanced Styling -->
        <div class="space-y-2">
            <flux:input
                wire:model="email"
                :label="__('E-postadress')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="din.email@autoclean.se"
                class="input-modern"
            />
        </div>

        <!-- Password with Enhanced Styling -->
        <div class="relative space-y-2">
            <flux:input
                wire:model="password"
                :label="__('Lösenord')"
                type="password"
                required
                autocomplete="current-password"
                placeholder="Ange ditt lösenord"
                viewable
                class="input-modern"
            />

            @if (Route::has('password.request'))
                <flux:link class="absolute end-0 top-0 text-sm text-blue-600 hover:text-blue-700 transition-colors" :href="route('password.request')" wire:navigate>
                    {{ __('Glömt lösenord?') }}
                </flux:link>
            @endif
        </div>

        <!-- Remember Me with Better Spacing -->
        <div class="pt-2">
            <flux:checkbox wire:model="remember" :label="__('Kom ihåg mig')" class="text-gray-700 dark:text-gray-300" />
        </div>

        <!-- Enhanced Login Button -->
        <div class="flex items-center justify-end pt-4">
            <flux:button variant="primary" type="submit" class="w-full btn-primary-modern py-3 text-base font-semibold">
                {{ __('Logga in') }}
            </flux:button>
        </div>
    </form>
</div>
