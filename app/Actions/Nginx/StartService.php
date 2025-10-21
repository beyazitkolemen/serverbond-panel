<?php

declare(strict_types=1);

namespace App\Actions\Nginx;

use App\Actions\BaseServerBondService;

class StartService extends BaseServerBondService
{
    /**
     * Start Nginx
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('nginx', 'start'));
    }
}