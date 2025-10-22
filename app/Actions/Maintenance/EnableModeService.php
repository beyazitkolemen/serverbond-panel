<?php

declare(strict_types=1);

namespace App\Actions\Maintenance;

use App\Actions\BaseServerBondService;

class EnableModeService extends BaseServerBondService
{
    /**
     * Enable Laravel maintenance mode
     */
    public function execute(string $project): array
    {
        $params = [
            'project' => $project,
        ];

        $this->validateParams($params, ['project']);

        return $this->executeScript($this->getScriptPath('maintenance', 'enable_mode'), $params);
    }
}