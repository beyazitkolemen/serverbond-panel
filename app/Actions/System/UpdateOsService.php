<?php

declare(strict_types=1);

namespace App\Actions\System;

use App\Actions\BaseServerBondService;

class UpdateOsService extends BaseServerBondService
{
    /**
     * Update and upgrade apt packages
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('system', 'update_os'));
    }
}