<?php

use App\Models\Setting;

if (!function_exists('getSetting')) {
    function getSetting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (!function_exists('setSetting')) {
    function setSetting(string $key, mixed $value): void
    {
        Setting::set($key, $value);
    }
}
