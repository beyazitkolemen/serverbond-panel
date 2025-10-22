<?php

declare(strict_types=1);

namespace App\Actions\Mysql;

use App\Actions\BaseServerBondService;

class CreateUserService extends BaseServerBondService
{
    /**
     * Create user + grant privileges
     */
    public function execute(
        string $username,
        string $password,
        string $host = '%',
        ?string $database = null,
        string $privileges = 'ALL PRIVILEGES'
    ): array {
        $params = [
            'username' => $username,
            'password' => $password,
            'host' => $host,
            'privileges' => $privileges,
        ];

        $this->validateParams($params, ['username', 'password']);

        if ($database) {
            $params['database'] = $database;
        }

        return $this->executeScript($this->getScriptPath('mysql', 'create_user'), $params);
    }
}