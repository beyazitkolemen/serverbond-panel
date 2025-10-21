<?php

declare(strict_types=1);

namespace App\Actions\System;

use App\Actions\BaseServerBondService;

class Fail2banInstallService extends BaseServerBondService
{
    /**
     * Install Fail2ban and enable ssh/nginx jails
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('system', 'fail2ban_install'));
    }
}