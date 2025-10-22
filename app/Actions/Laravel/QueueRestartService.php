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
        $params = [
            'project' => $project,
        ];

        $this->validateParams($params, ['project']);

        return $this->executeScript($this->getScriptPath('laravel', 'queue_restart'), $params);
    }
}