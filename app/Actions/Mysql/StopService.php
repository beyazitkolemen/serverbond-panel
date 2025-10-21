<?php

declare(strict_types=1);

namespace App\Actions\Mysql;

use App\Actions\BaseServerBondService;

class StopService extends BaseServerBondService
{
    /**
     * Stop MySQL
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('mysql', 'stop'));
    }
}