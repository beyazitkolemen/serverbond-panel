<?php

declare(strict_types=1);

use App\Services\SettingService;

if (!function_exists('setting')) {
    /**
     * Ayar değerini getir veya ayarla
     *
     * @param string|null $key Ayar anahtarı (null ise service döndürür)
     * @param mixed $default Varsayılan değer
     * @return mixed
     */
    function setting(?string $key = null, mixed $default = null): mixed
    {
        $service = app(SettingService::class);

        if ($key === null) {
            return $service;
        }

        return $service->get($key, $default);
    }
}

if (!function_exists('setting_group')) {
    /**
     * Grup ayarlarını al
     *
     * @param string $group
     * @return array
     */
    function setting_group(string $group): array
    {
        return app(SettingService::class)->getGroupAsArray($group);
    }
}

if (!function_exists('setting_set')) {
    /**
     * Ayar değerini güncelle veya oluştur
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $group
     * @param string|null $type
     * @return \App\Models\Setting
     */
    function setting_set(string $key, mixed $value, ?string $group = 'general', ?string $type = null): \App\Models\Setting
    {
        return app(SettingService::class)->set($key, $value, $group, $type);
    }
}

if (!function_exists('public_settings')) {
    /**
     * Public ayarları getir
     *
     * @return array
     */
    function public_settings(): array
    {
        return app(SettingService::class)->getPublicSettingsAsArray();
    }
}

