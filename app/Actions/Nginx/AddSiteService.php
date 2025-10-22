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
        string $root,
        ?string $template = null
    ): array {
        $params = [
            'domain' => $domain,
            'root' => $root,
        ];

        $this->validateParams($params, ['domain', 'root']);

        if ($template) {
            $params['template'] = $template;
        }

        return $this->executeScript($this->getScriptPath('nginx', 'add_site'), $params);
    }
}
