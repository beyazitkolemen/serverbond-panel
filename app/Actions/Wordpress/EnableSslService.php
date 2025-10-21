<?php

declare(strict_types=1);

namespace App\Actions\Wordpress;

use App\Actions\BaseServerBondService;

class EnableSslService extends BaseServerBondService
{
    /**
     * Let's Encrypt SSL for WP site
     */
    public function execute(string $domain, string $email): array
    {
        $this->validateParams(['domain', 'email'], ['domain', 'email']);
        
        return $this->executeScript($this->getScriptPath('wp', 'enable_ssl'), [
            'domain' => $domain,
            'email' => $email
        ]);
    }
}