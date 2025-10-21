<?php

declare(strict_types=1);

namespace App\Actions\Deploy;

use App\Actions\BaseServerBondService;

class CloneRepoService extends BaseServerBondService
{
    /**
     * Initial repo clone
     */
    public function execute(string $repo, string $branch = 'main', string $dest): array
    {
        $this->validateParams(['repo', 'dest'], ['repo', 'dest']);
        
        return $this->executeScript($this->getScriptPath('deploy', 'clone_repo'), [
            'repo' => $repo,
            'branch' => $branch,
            'dest' => $dest
        ]);
    }
}