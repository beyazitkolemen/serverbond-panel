<?php

declare(strict_types=1);

namespace App\Actions\Ssl;

use App\Actions\BaseServerBondService;

class RemoveSslService extends BaseServerBondService
{
    /**
     * Remove certificate and revert to HTTP
     */
    public function execute(string $domain): array
    {
        $this->validateParams(['domain'], ['domain']);
        
        return $this->executeScript($this->getScriptPath('ssl', 'remove_ssl'), [
            'domain' => $domain
        ]);
    }
}