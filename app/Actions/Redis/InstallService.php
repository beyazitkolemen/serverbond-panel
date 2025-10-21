<?php

declare(strict_types=1);

namespace App\Actions\Redis;

use App\Actions\BaseServerBondService;

class InstallService extends BaseServerBondService
{
    /**
     * Install Redis and service settings
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('redis', 'install'));
    }
}