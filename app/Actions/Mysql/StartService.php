<?php

declare(strict_types=1);

namespace App\Actions\Mysql;

use App\Actions\BaseServerBondService;

class StartService extends BaseServerBondService
{
    /**
     * Start MySQL
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('mysql', 'start'));
    }
}