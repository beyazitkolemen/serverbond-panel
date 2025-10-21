<?php

declare(strict_types=1);

namespace App\Actions\Nginx;

use App\Actions\BaseServerBondService;

class ListSitesService extends BaseServerBondService
{
    /**
     * Get active vhost list as JSON
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('nginx', 'list_sites'));
    }
}