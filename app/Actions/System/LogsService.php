<?php

declare(strict_types=1);

namespace App\Actions\System;

use App\Actions\BaseServerBondService;

class LogsService extends BaseServerBondService
{
    /**
     * Get recent system logs
     */
    public function execute(int $lines = 200): array
    {
        return $this->executeScript($this->getScriptPath('system', 'logs'), [
            'lines' => $lines
        ]);
    }
}