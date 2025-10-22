<?php

declare(strict_types=1);

namespace App\Actions\Nginx;

use App\Actions\BaseServerBondService;

class RebuildConfService extends BaseServerBondService
{
    /**
     * Rebuild config from templates
     */
    public function execute(string $domain, string $type): array
    {
        $params = [
            'domain' => $domain,
            'type' => $type,
        ];

        $this->validateParams($params, ['domain', 'type']);

        return $this->executeScript($this->getScriptPath('nginx', 'rebuild_conf'), $params);
    }
}