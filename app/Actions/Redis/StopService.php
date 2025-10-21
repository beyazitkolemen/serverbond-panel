<?php

declare(strict_types=1);

namespace App\Actions\Redis;

use App\Actions\BaseServerBondService;

class StopService extends BaseServerBondService
{
    /**
     * Stop Redis
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('redis', 'stop'));
    }
}