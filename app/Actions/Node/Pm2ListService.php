<?php

declare(strict_types=1);

namespace App\Actions\Node;

use App\Actions\BaseServerBondService;

class Pm2ListService extends BaseServerBondService
{
    /**
     * Get PM2 process list as JSON
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('node', 'pm2_list'));
    }
}