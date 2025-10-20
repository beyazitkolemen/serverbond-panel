<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SslCertificateStatus;
use App\Enums\SslCertificateType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SslCertificate extends Model
{
    protected $fillable = [
        'site_id',
        'type',
        'domain',
        'status',
        'certificate',
        'private_key',
        'chain',
        'issued_at',
        'expires_at',
        'auto_renew',
        'error',
    ];

    protected $casts = [
        'type' => SslCertificateType::class,
        'status' => SslCertificateStatus::class,
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'auto_renew' => 'boolean',
    ];

    protected $hidden = [
        'private_key',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function isActive(): bool
    {
        return $this->status === SslCertificateStatus::Active;
    }

    public function isExpired(): bool
    {
        return $this->status === SslCertificateStatus::Expired || ($this->expires_at && $this->expires_at->isPast());
    }

    public function isExpiringSoon(): bool
    {
        return $this->expires_at && $this->expires_at->diffInDays() <= 30;
    }
}
