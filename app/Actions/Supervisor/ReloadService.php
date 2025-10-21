<?php

declare(strict_types=1);

namespace App\Actions\Supervisor;

use App\Actions\BaseServerBondService;

class ReloadService extends BaseServerBondService
{
    /**
     * Supervisor reload
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('supervisor', 'reload'));
    }
}