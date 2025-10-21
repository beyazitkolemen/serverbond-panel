<?php

declare(strict_types=1);

namespace App\Actions\Php;

use App\Actions\BaseServerBondService;

class InfoService extends BaseServerBondService
{
    /**
     * Get PHP version and extension information
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('php', 'info'));
    }
}