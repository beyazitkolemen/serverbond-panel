<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'label',
        'description',
        'is_public',
        'is_encrypted',
        'order',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_encrypted' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Value değerini type'a göre formatlar
     */
    public function getFormattedValueAttribute(): mixed
    {
        $value = $this->getDecryptedValue();

        return match($this->type) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json', 'array' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Şifrelenmiş değeri çözer
     */
    protected function getDecryptedValue(): mixed
    {
        if ($this->is_encrypted && $this->value) {
            try {
                return Crypt::decryptString($this->value);
            } catch (\Exception $e) {
                return $this->value;
            }
        }

        return $this->value;
    }

    /**
     * Value'yu tipine göre set eder
     */
    public function setFormattedValue(mixed $value): void
    {
        $formattedValue = match($this->type) {
            'integer' => (string) (int) $value,
            'boolean' => $value ? '1' : '0',
            'json', 'array' => json_encode($value),
            default => (string) $value,
        };

        if ($this->is_encrypted) {
            $formattedValue = Crypt::encryptString($formattedValue);
        }

        $this->value = $formattedValue;
    }

    /**
     * Scope: Group'a göre filtrele
     */
    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope: Public ayarları getir
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope: Sıralı
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('key');
    }
}

