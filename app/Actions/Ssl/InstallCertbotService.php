<?php

declare(strict_types=1);

namespace App\Actions\Ssl;

use App\Actions\BaseServerBondService;

class InstallCertbotService extends BaseServerBondService
{
    /**
     * Install Certbot
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('ssl', 'install_certbot'));
    }
}