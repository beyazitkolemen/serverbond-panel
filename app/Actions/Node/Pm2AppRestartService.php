<?php

declare(strict_types=1);

namespace App\Actions\Node;

use App\Actions\BaseServerBondService;

class Pm2AppRestartService extends BaseServerBondService
{
    /**
     * Restart PM2 application
     */
    public function execute(string $name): array
    {
        $this->validateParams(['name'], ['name']);
        
        return $this->executeScript($this->getScriptPath('node', 'pm2_app_restart'), [
            'name' => $name
        ]);
    }
}