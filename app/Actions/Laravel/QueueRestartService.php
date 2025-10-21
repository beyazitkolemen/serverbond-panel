<?php

declare(strict_types=1);

namespace App\Actions\Laravel;

use App\Actions\BaseServerBondService;

class QueueRestartService extends BaseServerBondService
{
    /**
     * Laravel queue restart
     */
    public function execute(string $project): array
    {
        $this->validateParams(['project'], ['project']);
        
        return $this->executeScript($this->getScriptPath('laravel', 'queue_restart'), [
            'project' => $project
        ]);
    }
}