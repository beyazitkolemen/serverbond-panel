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
        $this->validateParams(['project', 'env_json'], ['project', 'env_json']);
        
        return $this->executeScript($this->getScriptPath('laravel', 'env_write'), [
            'project' => $project,
            'env_json' => json_encode($envJson)
        ]);
    }
}