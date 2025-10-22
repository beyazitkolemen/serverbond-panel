<?php

declare(strict_types=1);

namespace App\Actions\Ssl;

use App\Actions\BaseServerBondService;

class CreateSslService extends BaseServerBondService
{
    /**
     * Create Let's Encrypt certificate
     */
    public function execute(string $domain, string $email): array
    {
        $params = [
            'domain' => $domain,
            'email' => $email,
        ];

        $this->validateParams($params, ['domain', 'email']);

        return $this->executeScript($this->getScriptPath('ssl', 'create_ssl'), $params);
    }
}