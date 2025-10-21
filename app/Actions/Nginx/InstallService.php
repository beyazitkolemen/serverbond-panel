<?php

declare(strict_types=1);

namespace App\Actions\Nginx;

use App\Actions\BaseServerBondService;

class InstallService extends BaseServerBondService
{
    /**
     * Install Nginx with basic optimization
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('nginx', 'install'));
    }
}