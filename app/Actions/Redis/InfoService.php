<?php

declare(strict_types=1);

namespace App\Actions\Redis;

use App\Actions\BaseServerBondService;

class InfoService extends BaseServerBondService
{
    /**
     * Get Redis INFO (uptime/memory/keys)
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('redis', 'info'));
    }
}