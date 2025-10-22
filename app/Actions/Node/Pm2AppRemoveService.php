<?php

declare(strict_types=1);

namespace App\Actions\Node;

use App\Actions\BaseServerBondService;

class Pm2AppRemoveService extends BaseServerBondService
{
    /**
     * Remove PM2 application
     */
    public function execute(string $name): array
    {
        $params = [
            'name' => $name,
        ];

        $this->validateParams($params, ['name']);

        return $this->executeScript($this->getScriptPath('node', 'pm2_app_remove'), $params);
    }
}