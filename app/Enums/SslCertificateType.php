<?php

declare(strict_types=1);

namespace App\Enums;

enum SslCertificateType: string
{
    case LetsEncrypt = 'letsencrypt';
    case Custom = 'custom';

    public function label(): string
    {
        return match($this) {
            self::LetsEncrypt => "Let's Encrypt",
            self::Custom => 'Özel Sertifika',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::LetsEncrypt => 'heroicon-o-lock-closed',
            self::Custom => 'heroicon-o-key',
        };
    }
}

