<?php

declare(strict_types=1);

namespace App\Actions\Nginx;

use App\Actions\BaseServerBondService;

class EnableSslService extends BaseServerBondService
{
    /**
     * Get Let's Encrypt SSL and set auto-renew
     */
    public function execute(string $domain, string $email, bool $redirectHttps = true): array
    {
        $this->validateParams(['domain', 'email'], ['domain', 'email']);
        
        return $this->executeScript($this->getScriptPath('nginx', 'enable_ssl'), [
            'domain' => $domain,
            'email' => $email,
            'redirect_https' => $redirectHttps
        ]);
    }
}