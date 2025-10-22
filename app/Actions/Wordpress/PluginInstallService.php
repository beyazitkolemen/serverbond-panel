<?php

declare(strict_types=1);

namespace App\Actions\Wordpress;

use App\Actions\BaseServerBondService;

class PluginInstallService extends BaseServerBondService
{
    /**
     * Install/activate WP plugin
     */
    public function execute(string $domain, string $slug, bool $activate = true): array
    {
        $params = [
            'domain' => $domain,
            'slug' => $slug,
            'activate' => $activate,
        ];

        $this->validateParams($params, ['domain', 'slug']);

        return $this->executeScript($this->getScriptPath('wp', 'plugin_install'), $params);
    }
}