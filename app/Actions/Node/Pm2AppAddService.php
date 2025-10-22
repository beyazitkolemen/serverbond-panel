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
        $params = [
            'name' => $name,
            'cwd' => $cwd,
            'script' => $script,
        ];

        $this->validateParams($params, ['name', 'cwd', 'script']);

        if ($env !== null) {
            $encodedEnv = json_encode($env);

            if ($encodedEnv === false) {
                throw new \RuntimeException('Failed to encode PM2 environment configuration to JSON');
            }

            $params['env'] = $encodedEnv;
        }

        return $this->executeScript($this->getScriptPath('node', 'pm2_app_add'), $params);
    }
}