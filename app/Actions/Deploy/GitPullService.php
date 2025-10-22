<?php

declare(strict_types=1);

namespace App\Actions\Deploy;

use App\Actions\BaseServerBondService;

class GitPullService extends BaseServerBondService
{
    /**
     * Git pull (ff-only)
     */
    public function execute(string $cwd, string $branch = 'main'): array
    {
        $params = [
            'cwd' => $cwd,
            'branch' => $branch,
        ];

        $this->validateParams($params, ['cwd']);

        return $this->executeScript($this->getScriptPath('deploy', 'git_pull'), $params);
    }
}