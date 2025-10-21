<?php

declare(strict_types=1);

namespace App\Actions\Laravel;

use App\Actions\BaseServerBondService;

class DeployPipelineService extends BaseServerBondService
{
    /**
     * Full deploy pipeline (git pull, composer, migrate, build)
     */
    public function execute(string $project, bool $runBuild = true): array
    {
        $this->validateParams(['project'], ['project']);
        
        return $this->executeScript($this->getScriptPath('laravel', 'deploy_pipeline'), [
            'project' => $project,
            'run_build' => $runBuild
        ]);
    }
}