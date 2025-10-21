<?php

declare(strict_types=1);

namespace App\Actions\Static;

use App\Actions\BaseServerBondService;

class CreateSiteService extends BaseServerBondService
{
    /**
     * Create static site (nginx + root path + SSL optional)
     */
    public function execute(string $domain, string $root, bool $ssl = false, ?string $email = null): array
    {
        $this->validateParams(['domain', 'root'], ['domain', 'root']);
        
        $params = [
            'domain' => $domain,
            'root' => $root,
            'ssl' => $ssl
        ];

        if ($email) {
            $params['email'] = $email;
        }

        return $this->executeScript($this->getScriptPath('static', 'create_site'), $params);
    }
}