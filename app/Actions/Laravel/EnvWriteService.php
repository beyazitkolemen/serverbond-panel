<?php

declare(strict_types=1);

namespace App\Actions\Laravel;

use App\Actions\BaseServerBondService;

class EnvWriteService extends BaseServerBondService
{
    /**
     * Write .env to project (JSON → KEY=VAL)
     */
    public function execute(string $project, array $envJson): array
    {
        $encodedEnv = json_encode($envJson);

        if ($encodedEnv === false) {
            throw new \RuntimeException('Failed to encode environment variables to JSON');
        }

        $params = [
            'project' => $project,
            'env_json' => $encodedEnv,
        ];

        $this->validateParams($params, ['project', 'env_json']);

        return $this->executeScript($this->getScriptPath('laravel', 'env_write'), $params);
    }
}