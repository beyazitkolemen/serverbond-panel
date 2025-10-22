<?php

declare(strict_types=1);

namespace App\Actions\Deploy;

use App\Actions\BaseServerBondService;

class ComposerInstallService extends BaseServerBondService
{
    /**
     * Composer install (no-dev, prefer-dist)
     */
    public function execute(string $cwd): array
    {
        $params = [
            'cwd' => $cwd,
        ];

        $this->validateParams($params, ['cwd']);

        return $this->executeScript($this->getScriptPath('deploy', 'composer_install'), $params);
    }
}