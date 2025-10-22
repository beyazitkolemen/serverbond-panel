<?php

declare(strict_types=1);

namespace App\Actions\Wordpress;

use App\Actions\BaseServerBondService;

class ThemeInstallService extends BaseServerBondService
{
    /**
     * Install/activate WP theme
     */
    public function execute(string $domain, string $slug, bool $activate = true): array
    {
        $params = [
            'domain' => $domain,
            'slug' => $slug,
            'activate' => $activate,
        ];

        $this->validateParams($params, ['domain', 'slug']);

        return $this->executeScript($this->getScriptPath('wp', 'theme_install'), $params);
    }
}