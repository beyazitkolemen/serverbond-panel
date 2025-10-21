<?php

declare(strict_types=1);

namespace App\Actions\Laravel;

use App\Actions\BaseServerBondService;

class ScheduleRunService extends BaseServerBondService
{
    /**
     * Trigger Laravel schedule:run
     */
    public function execute(string $project): array
    {
        $this->validateParams(['project'], ['project']);
        
        return $this->executeScript($this->getScriptPath('laravel', 'schedule_run'), [
            'project' => $project
        ]);
    }
}