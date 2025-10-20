<?php

declare(strict_types=1);

namespace App\Enums;

enum DeploymentTrigger: string
{
    case Manual = 'manual';
    case Auto = 'auto';
    case Webhook = 'webhook';

    public function label(): string
    {
        return match($this) {
            self::Manual => 'Manuel',
            self::Auto => 'Otomatik',
            self::Webhook => 'Webhook',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Manual => 'heroicon-o-hand-raised',
            self::Auto => 'heroicon-o-bolt',
            self::Webhook => 'heroicon-o-link',
        };
    }
}

