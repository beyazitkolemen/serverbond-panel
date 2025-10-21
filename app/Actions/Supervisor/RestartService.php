<?php

declare(strict_types=1);

namespace App\Actions\Supervisor;

use App\Actions\BaseServerBondService;

class RestartService extends BaseServerBondService
{
    /**
     * Supervisor restart
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('supervisor', 'restart'));
    }
}