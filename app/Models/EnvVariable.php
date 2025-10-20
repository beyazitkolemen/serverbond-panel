<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnvVariable extends Model
{
    protected $fillable = [
        'site_id',
        'key',
        'value',
        'is_secret',
        'description',
    ];

    protected $casts = [
        'is_secret' => 'boolean',
    ];

    protected $hidden = [
        'value',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function getDisplayValueAttribute(): string
    {
        if ($this->is_secret) {
            return '••••••••';
        }

        return $this->value ?? '';
    }
}
