<?php

declare(strict_types=1);

namespace App\Actions\Supervisor;

use App\Actions\BaseServerBondService;

class InstallService extends BaseServerBondService
{
    /**
     * Install Supervisor and default settings
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('supervisor', 'install'));
    }
}