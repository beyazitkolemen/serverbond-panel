<?php

declare(strict_types=1);

namespace App\Actions\Deploy;

use App\Actions\BaseServerBondService;

class NpmBuildService extends BaseServerBondService
{
    /**
     * npm install + build (Vite/Nuxt/Next)
     */
    public function execute(string $cwd, string $cmd = 'build'): array
    {
        $this->validateParams(['cwd'], ['cwd']);
        
        return $this->executeScript($this->getScriptPath('deploy', 'npm_build'), [
            'cwd' => $cwd,
            'cmd' => $cmd
        ]);
    }
}