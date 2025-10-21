<?php

declare(strict_types=1);

namespace App\Actions\Node;

use App\Actions\BaseServerBondService;

class Pm2InstallService extends BaseServerBondService
{
    /**
     * Install PM2 and set startup
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('node', 'pm2_install'));
    }
}