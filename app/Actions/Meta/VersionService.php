<?php

declare(strict_types=1);

namespace App\Actions\Meta;

use App\Actions\BaseServerBondService;

class VersionService extends BaseServerBondService
{
    /**
     * Get agent version information
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('meta', 'version'));
    }
}