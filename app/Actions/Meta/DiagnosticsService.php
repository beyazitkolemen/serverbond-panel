<?php

declare(strict_types=1);

namespace App\Actions\Meta;

use App\Actions\BaseServerBondService;

class DiagnosticsService extends BaseServerBondService
{
    /**
     * Nginx/PHP/MySQL/Redis quick diagnostics
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('meta', 'diagnostics'));
    }
}