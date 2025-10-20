<?php

declare(strict_types=1);

namespace App\Enums;

enum SslCertificateStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Renewing = 'renewing';
    case Failed = 'failed';

    public function label(): string
    {
        return match($this) {
            self::Active => 'Aktif',
            self::Expired => 'Süresi Dolmuş',
            self::Renewing => 'Yenileniyor',
            self::Failed => 'Başarısız',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Active => 'success',
            self::Expired => 'danger',
            self::Renewing => 'warning',
            self::Failed => 'danger',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Active => 'heroicon-o-shield-check',
            self::Expired => 'heroicon-o-shield-exclamation',
            self::Renewing => 'heroicon-o-arrow-path',
            self::Failed => 'heroicon-o-x-circle',
        };
    }
}

