<?php

declare(strict_types=1);

namespace App\Actions\Static;

use App\Actions\BaseServerBondService;

class DeployArtifactService extends BaseServerBondService
{
    /**
     * Extract build artifact (zip/tar) to site root
     */
    public function execute(string $domain, string $artifact): array
    {
        $params = [
            'domain' => $domain,
            'artifact' => $artifact,
        ];

        $this->validateParams($params, ['domain', 'artifact']);

        return $this->executeScript($this->getScriptPath('static', 'deploy_artifact'), $params);
    }
}