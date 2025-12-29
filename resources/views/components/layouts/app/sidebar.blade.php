<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-gray-50 dark:bg-gray-900 antialiased">
        <flux:sidebar sticky stashable class="border-e border-gray-200/60 dark:border-gray-700/60 bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-3 rtl:space-x-reverse p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" wire:navigate>
                <div class="w-8 h-8 rounded-lg gradient-primary shadow-md shadow-blue-500/25 flex items-center justify-center">
                    <x-heroicon-o-building-storefront class="w-5 h-5 text-white" />
                </div>
                <span class="text-lg font-bold text-gray-900 dark:text-white">WashNode</span>
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Platform')" class="grid">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                    <flux:navlist.item icon="clock" :href="route('time-reports')" :current="request()->routeIs('time-reports')" wire:navigate>Tidsrapporter</flux:navlist.item>
                </flux:navlist.group>

                @can('admin')
                <flux:navlist.group heading="Administration" class="grid">
                    <flux:navlist.item icon="building-storefront" :href="route('admin.stations')" :current="request()->routeIs('admin.stations')" wire:navigate>Stationer</flux:navlist.item>
                    <flux:navlist.item icon="clipboard-document-list" :href="route('admin.tasks')" :current="request()->routeIs('admin.tasks')" wire:navigate>Uppgifter</flux:navlist.item>
                    <flux:navlist.item icon="squares-plus" :href="route('admin.templates')" :current="request()->routeIs('admin.templates')" wire:navigate>Uppgiftsmallar</flux:navlist.item>
                    <flux:navlist.item icon="archive-box" :href="route('admin.inventory')" :current="request()->routeIs('admin.inventory')" wire:navigate>Lagerhantering</flux:navlist.item>
                    <flux:navlist.item icon="users" :href="route('admin.users')" :current="request()->routeIs('admin.users')" wire:navigate>Användare</flux:navlist.item>
                    <flux:navlist.item icon="chart-bar" :href="route('admin.user-activity')" :current="request()->routeIs('admin.user-activity*')" wire:navigate>Användaraktivitet</flux:navlist.item>
                    <flux:navlist.item icon="cog-6-tooth" :href="route('admin.settings')" :current="request()->routeIs('admin.settings')" wire:navigate>Inställningar</flux:navlist.item>
                    <flux:navlist.item icon="document-text" href="{{ url('log-viewer') }}" target="_blank">Systemloggar</flux:navlist.item>
                </flux:navlist.group>
                @endcan
            </flux:navlist>

            <flux:spacer />

            <!-- Enhanced Desktop User Menu -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                <div class="card-modern p-3 hover:shadow-md transition-all cursor-pointer">
                    <flux:profile
                        :name="auth()->user()->name"
                        :initials="auth()->user()->initials()"
                        icon:trailing="chevrons-up-down"
                    />
                </div>

                <flux:menu class="w-[260px] card-modern border-0 mt-2">
                    <flux:menu.radio.group>
                        <div class="p-4 border-b border-gray-100 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <div class="relative flex h-10 w-10 shrink-0 overflow-hidden rounded-xl">
                                    <span class="flex h-full w-full items-center justify-center rounded-xl gradient-primary text-white font-semibold">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </div>

                                <div class="grid flex-1 text-start leading-tight">
                                    <span class="truncate font-semibold text-gray-900 dark:text-white">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-sm text-gray-600 dark:text-gray-400">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
