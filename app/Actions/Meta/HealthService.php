<?php

declare(strict_types=1);

namespace App\Actions\Meta;

use App\Actions\BaseServerBondService;

class HealthService extends BaseServerBondService
{
    /**
     * Agent health check
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('meta', 'health'));
    }
}