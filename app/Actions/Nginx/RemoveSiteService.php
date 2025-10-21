<?php

declare(strict_types=1);

namespace App\Actions\Nginx;

use App\Actions\BaseServerBondService;

class RemoveSiteService extends BaseServerBondService
{
    /**
     * Remove site (disable + delete conf + reload)
     */
    public function execute(string $domain): array
    {
        $this->validateParams(['domain'], ['domain']);
        
        return $this->executeScript($this->getScriptPath('nginx', 'remove_site'), [
            'domain' => $domain
        ]);
    }
}