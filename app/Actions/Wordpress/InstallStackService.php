<?php

declare(strict_types=1);

namespace App\Actions\Wordpress;

use App\Actions\BaseServerBondService;

class InstallStackService extends BaseServerBondService
{
    /**
     * WordPress PHP extensions + Nginx + MySQL settings
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('wp', 'install_stack'));
    }
}