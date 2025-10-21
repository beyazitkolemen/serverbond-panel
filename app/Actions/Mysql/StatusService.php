<?php

declare(strict_types=1);

namespace App\Actions\Mysql;

use App\Actions\BaseServerBondService;

class StatusService extends BaseServerBondService
{
    /**
     * Get MySQL status and version
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('mysql', 'status'));
    }
}