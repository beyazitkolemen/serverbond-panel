<?php

declare(strict_types=1);

namespace App\Enums;

enum SiteStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Deploying = 'deploying';
    case Error = 'error';

    public function label(): string
    {
        return match($this) {
            self::Active => 'Aktif',
            self::Inactive => 'İnaktif',
            self::Deploying => 'Deploy Ediliyor',
            self::Error => 'Hata',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Active => 'success',
            self::Inactive => 'gray',
            self::Deploying => 'warning',
            self::Error => 'danger',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Active => 'heroicon-o-check-circle',
            self::Inactive => 'heroicon-o-pause-circle',
            self::Deploying => 'heroicon-o-arrow-path',
            self::Error => 'heroicon-o-x-circle',
        };
    }
}

