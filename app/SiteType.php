<?php

declare(strict_types=1);

namespace App\Enums;

enum SiteType: string
{
    case Laravel = 'laravel';
    case PHP = 'php';
    case Static = 'static';
    case Python = 'python';
    case NodeJS = 'nodejs';

    public function label(): string
    {
        return match($this) {
            self::Laravel => 'Laravel',
            self::PHP => 'PHP',
            self::Static => 'Static HTML',
            self::Python => 'Python',
            self::NodeJS => 'Node.js',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Laravel => 'heroicon-o-code-bracket',
            self::PHP => 'heroicon-o-code-bracket-square',
            self::Static => 'heroicon-o-document-text',
            self::Python => 'heroicon-o-command-line',
            self::NodeJS => 'heroicon-o-cpu-chip',
        };
    }
}
