<div class="flex flex-col gap-8">
    <!-- Modern Brand Header -->
    <div class="text-center mb-8">
        <div class="mb-6">
            <!-- Enhanced Logo with Gradient -->
            <div class="w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-6 gradient-primary shadow-lg shadow-primary-500/25">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h4M9 7h1m-1 4h1m4-4h1m-1 4h1"/>
                </svg>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-3">Skapa konto</h1>
            <p class="text-lg text-gray-600 dark:text-gray-400">Fyll i dina uppgifter för att skapa ett nytt konto</p>
        </div>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" wire:submit="register" class="flex flex-col gap-6">
        <!-- Name with Enhanced Styling -->
        <div class="space-y-2">
            <flux:input
                wire:model="name"
                label="Namn"
                type="text"
                required
                autofocus
                autocomplete="name"
                placeholder="Ditt fullständiga namn"
                class="input-modern"
            />
        </div>

        <!-- Email Address with Enhanced Styling -->
        <div class="space-y-2">
            <flux:input
                wire:model="email"
                label="E-postadress"
                type="email"
                required
                autocomplete="email"
                placeholder="din.email@autoclean.se"
                class="input-modern"
            />
        </div>

        <!-- Password with Enhanced Styling -->
        <div class="space-y-2">
            <flux:input
                wire:model="password"
                label="Lösenord"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Välj ett säkert lösenord"
                viewable
                class="input-modern"
            />
        </div>

        <!-- Confirm Password with Enhanced Styling -->
        <div class="space-y-2">
            <flux:input
                wire:model="password_confirmation"
                label="Bekräfta lösenord"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Bekräfta ditt lösenord"
                viewable
                class="input-modern"
            />
        </div>

        <!-- Enhanced Create Account Button -->
        <div class="flex items-center justify-end pt-4">
            <flux:button type="submit" variant="primary" class="w-full btn-primary-modern py-3 text-base font-semibold">
                {{ __('Skapa konto') }}
            </flux:button>
        </div>
    </form>

    <div class="text-center text-sm text-gray-600 dark:text-gray-400">
        <span>Har du redan ett konto? </span>
        <flux:link :href="route('login')" wire:navigate class="text-primary-600 hover:text-primary-700 font-medium transition-colors">
            Logga in
        </flux:link>
    </div>
</div>
