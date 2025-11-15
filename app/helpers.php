<?php

use App\Services\SettingsService;

if (! function_exists('settings')) {
    /**
     * Get or set a setting value
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed|\App\Services\SettingsService
     */
    function settings(?string $key = null, mixed $default = null): mixed
    {
        $service = app(SettingsService::class);

        if ($key === null) {
            return $service;
        }

        return $service->get($key, $default);
    }
}
