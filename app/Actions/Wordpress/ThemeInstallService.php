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
        $this->validateParams(['domain', 'slug'], ['domain', 'slug']);
        
        return $this->executeScript($this->getScriptPath('wp', 'theme_install'), [
            'domain' => $domain,
            'slug' => $slug,
            'activate' => $activate
        ]);
    }
}