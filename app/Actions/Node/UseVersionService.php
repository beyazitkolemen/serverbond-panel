<?php

declare(strict_types=1);

namespace App\Actions\Node;

use App\Actions\BaseServerBondService;

class UseVersionService extends BaseServerBondService
{
    /**
     * Select Node version for project
     */
    public function execute(string $version): array
    {
        $params = [
            'version' => $version,
        ];

        $this->validateParams($params, ['version']);

        return $this->executeScript($this->getScriptPath('node', 'use_version'), $params);
    }
}