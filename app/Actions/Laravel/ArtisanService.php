<?php

declare(strict_types=1);

namespace App\Actions\Laravel;

use App\Actions\BaseServerBondService;

class ArtisanService extends BaseServerBondService
{
    /**
     * Arbitrary artisan call (whitelist restricted)
     */
    public function execute(string $project, string $cmd): array
    {
        $this->validateParams(['project', 'cmd'], ['project', 'cmd']);
        
        return $this->executeScript($this->getScriptPath('laravel', 'artisan'), [
            'project' => $project,
            'cmd' => $cmd
        ]);
    }
}