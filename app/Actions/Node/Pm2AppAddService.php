<?php

declare(strict_types=1);

namespace App\Actions\Node;

use App\Actions\BaseServerBondService;

class Pm2AppAddService extends BaseServerBondService
{
    /**
     * Add PM2 application (ecosystem or entrypoint)
     */
    public function execute(string $name, string $cwd, string $script, ?array $env = null): array
    {
        $this->validateParams(['name', 'cwd', 'script'], ['name', 'cwd', 'script']);
        
        $params = [
            'name' => $name,
            'cwd' => $cwd,
            'script' => $script
        ];

        if ($env) {
            $params['env'] = json_encode($env);
        }

        return $this->executeScript($this->getScriptPath('node', 'pm2_app_add'), $params);
    }
}