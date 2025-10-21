<?php

declare(strict_types=1);

namespace App\Actions\Ssl;

use App\Actions\BaseServerBondService;

class RenewSslService extends BaseServerBondService
{
    /**
     * Renew certificates
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('ssl', 'renew_ssl'));
    }
}