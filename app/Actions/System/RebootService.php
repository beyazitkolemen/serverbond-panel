<?php

declare(strict_types=1);

namespace App\Actions\System;

use App\Actions\BaseServerBondService;

class RebootService extends BaseServerBondService
{
    /**
     * Reboot the server
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('system', 'reboot'));
    }
}