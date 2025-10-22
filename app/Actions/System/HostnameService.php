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
        $params = [
            'action' => 'set',
            'hostname' => $hostname,
        ];

        $this->validateParams($params, ['hostname']);

        return $this->executeScript($this->getScriptPath('system', 'hostname'), $params);
    }
}