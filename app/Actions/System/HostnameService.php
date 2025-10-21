<?php

declare(strict_types=1);

namespace App\Actions\System;

use App\Actions\BaseServerBondService;

class HostnameService extends BaseServerBondService
{
    /**
     * View or set hostname
     */
    public function view(): array
    {
        return $this->executeScript($this->getScriptPath('system', 'hostname'), [
            'action' => 'view'
        ]);
    }

    /**
     * Set hostname
     */
    public function set(string $hostname): array
    {
        $this->validateParams(['hostname'], ['hostname']);
        
        return $this->executeScript($this->getScriptPath('system', 'hostname'), [
            'action' => 'set',
            'hostname' => $hostname
        ]);
    }
}