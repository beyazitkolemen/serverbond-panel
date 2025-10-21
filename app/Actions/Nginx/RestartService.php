<?php

declare(strict_types=1);

namespace App\Actions\Nginx;

use App\Actions\BaseServerBondService;

class RestartService extends BaseServerBondService
{
    /**
     * Restart Nginx
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('nginx', 'restart'));
    }
}