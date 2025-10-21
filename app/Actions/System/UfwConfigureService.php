<?php

declare(strict_types=1);

namespace App\Actions\System;

use App\Actions\BaseServerBondService;

class UfwConfigureService extends BaseServerBondService
{
    /**
     * Install and configure UFW firewall with basic rules (80/443/22)
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('system', 'ufw_configure'));
    }
}