<?php

declare(strict_types=1);

namespace App\Actions\Deploy;

use App\Actions\BaseServerBondService;

class CustomScriptRunService extends BaseServerBondService
{
    /**
     * Run deploy.sh in project
     */
    public function execute(string $project): array
    {
        $this->validateParams(['project'], ['project']);
        
        return $this->executeScript($this->getScriptPath('deploy', 'custom_script_run'), [
            'project' => $project
        ]);
    }
}