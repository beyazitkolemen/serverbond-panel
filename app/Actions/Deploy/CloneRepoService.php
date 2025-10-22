<?php

declare(strict_types=1);

namespace App\Actions\Deploy;

use App\Actions\BaseServerBondService;

class CloneRepoService extends BaseServerBondService
{
    /**
     * Initial repo clone
     */
    public function execute(string $repo, string $dest, string $branch = 'main'): array
    {
        $params = [
            'repo' => $repo,
            'dest' => $dest,
            'branch' => $branch,
        ];

        $this->validateParams($params, ['repo', 'dest']);

        return $this->executeScript($this->getScriptPath('deploy', 'clone_repo'), $params);
    }
}