<?php

declare(strict_types=1);

namespace App\Actions\Maintenance;

use App\Actions\BaseServerBondService;

class DisableModeService extends BaseServerBondService
{
    /**
     * Disable Laravel maintenance mode
     */
    public function execute(string $project): array
    {
        $this->validateParams(['project'], ['project']);
        
        return $this->executeScript($this->getScriptPath('maintenance', 'disable_mode'), [
            'project' => $project
        ]);
    }
}