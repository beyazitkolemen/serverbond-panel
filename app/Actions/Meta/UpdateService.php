<?php

declare(strict_types=1);

namespace App\Actions\Meta;

use App\Actions\BaseServerBondService;

class UpdateService extends BaseServerBondService
{
    /**
     * Agent self-update (git pull + restart service)
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('meta', 'update'));
    }
}