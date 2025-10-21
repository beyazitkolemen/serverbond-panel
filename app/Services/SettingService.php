<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    protected const CACHE_KEY = 'app_settings';
    protected const CACHE_TTL = 3600; // 1 saat

    /**
     * Tüm ayarları cache'den veya veritabanından getir
     */
    public function all(): Collection
    {
        return Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL,
            fn () => Setting::ordered()->get()
        );
    }

    /**
     * Belirli bir ayarı getir
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $setting = $this->all()->firstWhere('key', $key);

        if (!$setting) {
            return $default;
        }

        return $setting->formatted_value;
    }

    /**
     * Bir group'taki tüm ayarları getir
     */
    public function getGroup(string $group): Collection
    {
        return $this->all()->where('group', $group);
    }

    /**
     * Bir group'taki tüm ayarları key-value array olarak getir
     */
    public function getGroupAsArray(string $group): array
    {
        return $this->getGroup($group)
            ->mapWithKeys(fn (Setting $setting) => [$setting->key => $setting->formatted_value])
            ->all();
    }

    /**
     * Ayar değerini güncelle veya oluştur
     */
    public function set(string $key, mixed $value, ?string $group = 'general', ?string $type = null): Setting
    {
        $setting = Setting::firstOrNew(['key' => $key]);

        if (!$setting->exists) {
            $setting->group = $group;
            $setting->type = $type ?? $this->detectType($value);
        }

        $setting->setFormattedValue($value);
        $setting->save();

        $this->clearCache();

        return $setting->fresh();
    }

    /**
     * Bir group için toplu güncelleme
     */
    public function setGroup(string $group, array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->set($key, $value, $group);
        }

        $this->clearCache();
    }

    /**
     * Ayarı sil
     */
    public function delete(string $key): bool
    {
        $deleted = Setting::where('key', $key)->delete() > 0;

        if ($deleted) {
            $this->clearCache();
        }

        return $deleted;
    }

    /**
     * Public ayarları getir (frontend için)
     */
    public function getPublicSettings(): Collection
    {
        return $this->all()->where('is_public', true);
    }

    /**
     * Public ayarları key-value array olarak getir
     */
    public function getPublicSettingsAsArray(): array
    {
        return $this->getPublicSettings()
            ->mapWithKeys(fn (Setting $setting) => [$setting->key => $setting->formatted_value])
            ->all();
    }

    /**
     * Cache'i temizle
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Değerin tipini otomatik tespit et
     */
    protected function detectType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_array($value) => 'array',
            default => 'string',
        };
    }

    /**
     * Ayarın var olup olmadığını kontrol et
     */
    public function has(string $key): bool
    {
        return $this->all()->contains('key', $key);
    }

    /**
     * Tüm ayarları yenile (cache'i temizle ve yeniden yükle)
     */
    public function refresh(): Collection
    {
        $this->clearCache();
        return $this->all();
    }
}

