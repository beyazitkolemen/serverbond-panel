<?php

declare(strict_types=1);

namespace App\Actions\Mysql;

use App\Actions\BaseServerBondService;

class DeleteUserService extends BaseServerBondService
{
    /**
     * Delete user
     */
    public function execute(string $username, string $host = '%'): array
    {
        $params = [
            'username' => $username,
            'host' => $host,
        ];

        $this->validateParams($params, ['username']);

        return $this->executeScript($this->getScriptPath('mysql', 'delete_user'), $params);
    }
}