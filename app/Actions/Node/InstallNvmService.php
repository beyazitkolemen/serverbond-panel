<?php

declare(strict_types=1);

namespace App\Actions\Node;

use App\Actions\BaseServerBondService;

class InstallNvmService extends BaseServerBondService
{
    /**
     * Install NVM and set default Node version
     */
    public function execute(string $version = '20'): array
    {
        return $this->executeScript($this->getScriptPath('node', 'install_nvm'), [
            'version' => $version
        ]);
    }
}