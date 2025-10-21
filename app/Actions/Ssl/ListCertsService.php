<?php

declare(strict_types=1);

namespace App\Actions\Ssl;

use App\Actions\BaseServerBondService;

class ListCertsService extends BaseServerBondService
{
    /**
     * List certificates
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('ssl', 'list_certs'));
    }
}