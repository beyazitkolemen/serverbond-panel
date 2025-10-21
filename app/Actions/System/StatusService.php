<?php

declare(strict_types=1);

namespace App\Actions\System;

use App\Actions\BaseServerBondService;

class StatusService extends BaseServerBondService
{
    /**
     * Get system status (CPU/RAM/Disk/Load/Service summary) as JSON
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('system', 'status'));
    }
}