<?php

declare(strict_types=1);

namespace App\Actions\Nginx;

use App\Actions\BaseServerBondService;

class ReloadService extends BaseServerBondService
{
    /**
     * Reload Nginx config (with nginx -t validation)
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('nginx', 'reload'));
    }
}