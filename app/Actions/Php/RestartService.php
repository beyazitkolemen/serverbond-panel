<?php

declare(strict_types=1);

namespace App\Actions\Php;

use App\Actions\BaseServerBondService;

class RestartService extends BaseServerBondService
{
    /**
     * Restart PHP-FPM
     */
    public function execute(string $version = '8.3'): array
    {
        return $this->executeScript($this->getScriptPath('php', 'restart'), [
            'version' => $version
        ]);
    }
}