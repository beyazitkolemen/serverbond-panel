<?php

declare(strict_types=1);

namespace App\Enums;

enum PHPVersion: string
{
    case PHP80 = '8.0';
    case PHP81 = '8.1';
    case PHP82 = '8.2';
    case PHP83 = '8.3';
    case PHP84 = '8.4';

    public function label(): string
    {
        return match($this) {
            self::PHP80 => 'PHP 8.0',
            self::PHP81 => 'PHP 8.1',
            self::PHP82 => 'PHP 8.2',
            self::PHP83 => 'PHP 8.3',
            self::PHP84 => 'PHP 8.4',
        };
    }


}
