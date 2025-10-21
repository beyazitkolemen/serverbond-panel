<?php

declare(strict_types=1);

namespace App\Actions\Redis;

use App\Actions\BaseServerBondService;

class RestartService extends BaseServerBondService
{
    /**
     * Restart Redis
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('redis', 'restart'));
    }
}