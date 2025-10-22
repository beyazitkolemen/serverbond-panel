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
        $params = [
            'project' => $project,
        ];

        $this->validateParams($params, ['project']);

        return $this->executeScript($this->getScriptPath('deploy', 'custom_script_run'), $params);
    }
}