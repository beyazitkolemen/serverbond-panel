<?php

declare(strict_types=1);

namespace App\Actions\Meta;

use App\Actions\BaseServerBondService;

class CapabilitiesService extends BaseServerBondService
{
    /**
     * Get available script list as JSON
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('meta', 'capabilities'));
    }
}