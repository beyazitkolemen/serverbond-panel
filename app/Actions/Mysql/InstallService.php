<?php

declare(strict_types=1);

namespace App\Actions\Mysql;

use App\Actions\BaseServerBondService;

class InstallService extends BaseServerBondService
{
    /**
     * Install MySQL/MariaDB and secure root
     */
    public function execute(string $rootPassword): array
    {
        $this->validateParams(['root_password'], ['root_password']);
        
        return $this->executeScript($this->getScriptPath('mysql', 'install'), [
            'root_password' => $rootPassword
        ]);
    }
}