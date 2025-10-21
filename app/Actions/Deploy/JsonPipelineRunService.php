<?php

declare(strict_types=1);

namespace App\Actions\Deploy;

use App\Actions\BaseServerBondService;

class JsonPipelineRunService extends BaseServerBondService
{
    /**
     * Execute pipeline from JSON steps
     */
    public function execute(array $stepsJson, ?string $cwd = null): array
    {
        $this->validateParams(['steps_json'], ['steps_json']);
        
        $params = [
            'steps_json' => json_encode($stepsJson)
        ];

        if ($cwd) {
            $params['cwd'] = $cwd;
        }

        return $this->executeScript($this->getScriptPath('deploy', 'json_pipeline_run'), $params);
    }
}