<?php

declare(strict_types=1);

namespace App\Actions\Nginx;

use App\Actions\BaseServerBondService;

class DisableSslService extends BaseServerBondService
{
    /**
     * Disable SSL (revert to HTTP-only)
     */
    public function execute(string $domain): array
    {
        $params = [
            'domain' => $domain,
        ];

        $this->validateParams($params, ['domain']);

        return $this->executeScript($this->getScriptPath('nginx', 'disable_ssl'), $params);
    }
}