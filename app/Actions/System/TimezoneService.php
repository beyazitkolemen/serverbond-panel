<?php

declare(strict_types=1);

namespace App\Actions\System;

use App\Actions\BaseServerBondService;

class TimezoneService extends BaseServerBondService
{
    /**
     * View current timezone
     */
    public function view(): array
    {
        return $this->executeScript($this->getScriptPath('system', 'timezone'), [
            'action' => 'view'
        ]);
    }

    /**
     * Set timezone
     */
    public function set(string $timezone): array
    {
        return $this->executeScript($this->getScriptPath('system', 'timezone'), [
            'action' => 'set',
            'tz' => $timezone
        ]);
    }
}