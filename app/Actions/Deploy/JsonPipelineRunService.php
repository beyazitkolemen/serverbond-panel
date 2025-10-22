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
        $encodedSteps = json_encode($stepsJson);

        if ($encodedSteps === false) {
            throw new \RuntimeException('Failed to encode deployment steps to JSON');
        }

        $params = [
            'steps_json' => $encodedSteps,
        ];

        $this->validateParams($params, ['steps_json']);

        if ($cwd !== null) {
            $params['cwd'] = $cwd;
        }

        return $this->executeScript($this->getScriptPath('deploy', 'json_pipeline_run'), $params);
    }
}