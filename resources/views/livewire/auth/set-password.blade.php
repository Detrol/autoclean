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

    <x-auth-header :title="__('Välkommen ' . $invitation->name . '!')" :description="__('Sätt ditt lösenord för att slutföra registreringen')" />

    <!-- Invitation Info -->
    <div class="card-modern p-4 bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-700">
        <div class="flex items-start gap-3">
            <x-heroicon-o-envelope class="w-5 h-5 text-blue-600 mt-0.5" />
            <div>
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    Du har blivit inbjuden av <strong>{{ $invitation->inviter->name }}</strong> att gå med i AutoClean.
                </p>
                <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                    E-post: <strong>{{ $invitation->email }}</strong>
                </p>
                <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                    Roll: <strong>{{ $invitation->is_admin ? 'Administrator' : 'Anställd' }}</strong>
                </p>
            </div>
        </div>
    </div>

    <form method="POST" wire:submit="setPassword" class="flex flex-col gap-6">
        <!-- Password -->
        <div class="space-y-2">
            <flux:input
                wire:model="password"
                :label="__('Lösenord')"
                type="password"
                required
                autofocus
                placeholder="Minst 8 tecken"
                viewable
                class="input-modern"
            />
            @error('password')
                <div class="flex items-center gap-2 text-danger-600 text-sm">
                    <x-heroicon-o-exclamation-circle class="w-4 h-4" />
                    {{ $message }}
                </div>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="space-y-2">
            <flux:input
                wire:model="password_confirmation"
                :label="__('Bekräfta lösenord')"
                type="password"
                required
                placeholder="Upprepa ditt lösenord"
                viewable
                class="input-modern"
            />
            @error('password_confirmation')
                <div class="flex items-center gap-2 text-danger-600 text-sm">
                    <x-heroicon-o-exclamation-circle class="w-4 h-4" />
                    {{ $message }}
                </div>
            @enderror
        </div>

        <!-- Password Requirements -->
        <div class="card-modern p-4 bg-gray-50 dark:bg-gray-800">
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Lösenordskrav:</p>
            <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                <li class="flex items-center gap-2">
                    <x-heroicon-o-check-circle class="w-4 h-4 text-gray-400" />
                    Minst 8 tecken
                </li>
                <li class="flex items-center gap-2">
                    <x-heroicon-o-check-circle class="w-4 h-4 text-gray-400" />
                    Blanda stora och små bokstäver rekommenderas
                </li>
                <li class="flex items-center gap-2">
                    <x-heroicon-o-check-circle class="w-4 h-4 text-gray-400" />
                    Inkludera siffror och specialtecken för ökad säkerhet
                </li>
            </ul>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end pt-4">
            <flux:button variant="primary" type="submit" class="w-full btn-primary-modern py-3 text-base font-semibold">
                {{ __('Skapa konto') }}
            </flux:button>
        </div>
    </form>

    <!-- Login Link -->
    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
        <span>{{ __('Har du redan ett konto?') }}</span>
        <flux:link :href="route('login')" wire:navigate>{{ __('Logga in') }}</flux:link>
    </div>
</div>