<?php

declare(strict_types=1);

namespace App\Actions\Deploy;

use App\Actions\BaseServerBondService;

class CustomScriptWriteService extends BaseServerBondService
{
    /**
     * Write custom deploy.sh to project
     */
    public function execute(string $project, string $contentBase64): array
    {
        $this->validateParams(['project', 'content_base64'], ['project', 'content_base64']);
        
        return $this->executeScript($this->getScriptPath('deploy', 'custom_script_write'), [
            'project' => $project,
            'content_base64' => $contentBase64
        ]);
    }
}