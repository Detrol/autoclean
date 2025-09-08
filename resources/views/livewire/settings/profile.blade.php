<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profil')" :subheading="__('Uppdatera ditt namn och e-postadress')">
        <div class="card-modern p-6">
            <form wire:submit="updateProfileInformation" class="space-y-6">
                <div class="space-y-2">
                    <flux:input 
                        wire:model="name" 
                        label="Namn" 
                        type="text" 
                        required 
                        autofocus 
                        autocomplete="name"
                        class="input-modern"
                        placeholder="Ditt fullständiga namn"
                    />
                </div>

                <div class="space-y-2">
                    <flux:input 
                        wire:model="email" 
                        label="E-postadress" 
                        type="email" 
                        required 
                        autocomplete="email"
                        class="input-modern"
                        placeholder="din.email@autoclean.se"
                    />

                    @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                        <div class="card-modern p-4 bg-warning-50 dark:bg-warning-900/20 border-warning-200 dark:border-warning-700 mt-4">
                            <div class="flex items-start gap-3">
                                <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-warning-600 flex-shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-sm text-warning-800 dark:text-warning-200">
                                        Din e-postadress är inte verifierad.
                                    </p>
                                    <flux:link class="text-sm text-primary-600 hover:text-primary-700 underline cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                        Klicka här för att skicka verifieringsmailet igen.
                                    </flux:link>
                                </div>
                            </div>

                            @if (session('status') === 'verification-link-sent')
                                <div class="mt-3 card-modern p-3 bg-success-50 dark:bg-success-900/20 border-success-200 dark:border-success-700">
                                    <div class="flex items-center gap-2">
                                        <x-heroicon-o-check class="w-4 h-4 text-success-600" />
                                        <span class="text-sm font-medium text-success-800 dark:text-success-200">
                                            En ny verifieringslänk har skickats till din e-postadress.
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                    <x-action-message on="profile-updated" class="flex items-center gap-2">
                        <x-heroicon-o-check class="w-4 h-4 text-success-600" />
                        <span class="text-sm font-medium text-success-700 dark:text-success-300">Sparat!</span>
                    </x-action-message>

                    <flux:button variant="primary" type="submit" icon="check">
                        Spara Ändringar
                    </flux:button>
                </div>
            </form>
        </div>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
