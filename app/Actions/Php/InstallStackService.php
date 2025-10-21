<?php

declare(strict_types=1);

namespace App\Actions\Php;

use App\Actions\BaseServerBondService;

class InstallStackService extends BaseServerBondService
{
    /**
     * Install PHP-FPM and common extensions (opcache, mbstring, intl, redis, gd)
     */
    public function execute(string $version = '8.3'): array
    {
        return $this->executeScript($this->getScriptPath('php', 'install_stack'), [
            'version' => $version
        ]);
    }
}