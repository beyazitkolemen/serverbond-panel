<?php

declare(strict_types=1);

namespace App\Actions\Nginx;

use App\Actions\BaseServerBondService;

class ConfigTestService extends BaseServerBondService
{
    /**
     * Test Nginx configuration
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('nginx', 'config_test'));
    }
}