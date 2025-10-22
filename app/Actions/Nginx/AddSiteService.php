<?php

declare(strict_types=1);

namespace App\Actions\Nginx;

use App\Actions\BaseServerBondService;

class AddSiteService extends BaseServerBondService
{
    /**
     * Add new site (create vhost + enable + reload)
     */
    public function execute(
        string $domain,
        string $type,
        string $root,
        ?string $phpVersion = null,
        ?int $upstreamPort = null,
        ?string $serverAlias = null
    ): array {
        $params = [
            'domain' => $domain,
            'type' => $type,
            'root' => $root,
        ];

        $this->validateParams($params, ['domain', 'type', 'root']);

        if ($phpVersion) {
            $params['php_version'] = $phpVersion;
        }

        if ($upstreamPort) {
            $params['upstream_port'] = $upstreamPort;
        }

        if ($serverAlias) {
            $params['server_alias'] = $serverAlias;
        }

        return $this->executeScript($this->getScriptPath('nginx', 'add_site'), $params);
    }
}