<div class="flex flex-col gap-8">
    <!-- Modern Brand Header -->
    <div class="text-center mb-8">
        <div class="mb-6">
            <!-- Enhanced Logo with Gradient -->
            <div class="w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-6 gradient-primary shadow-lg shadow-blue-500/25">
                <x-heroicon-o-building-storefront class="w-10 h-10 text-white" />
            </div>
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-3">AutoClean</h1>
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

    <!-- Enhanced Demo Accounts -->
    <div class="card-modern p-6">
        <div class="flex items-center gap-2 mb-4">
            <x-heroicon-o-bolt class="w-4 h-4 text-blue-600" />
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Demo-konton</h3>
        </div>
        <div class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-primary-50 dark:bg-primary-900/20 rounded-lg border border-primary-100 dark:border-primary-800/30">
                <div>
                    <div class="text-sm font-medium text-gray-900 dark:text-white">Administrator</div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">admin@autoclean.se / password</div>
                </div>
                <div class="status-badge bg-primary-100 text-primary-700 dark:bg-primary-800 dark:text-primary-200">
                    Admin
                </div>
            </div>
            <div class="flex items-center justify-between p-3 bg-success-50 dark:bg-success-900/20 rounded-lg border border-success-100 dark:border-success-800/30">
                <div>
                    <div class="text-sm font-medium text-gray-900 dark:text-white">Anställd</div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">employee@autoclean.se / password</div>
                </div>
                <div class="status-badge bg-success-100 text-success-700 dark:bg-success-800 dark:text-success-200">
                    Employee
                </div>
            </div>
        </div>
    </div>

    @if (Route::has('register'))
        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Don\'t have an account?') }}</span>
            <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
        </div>
    @endif
</div>
