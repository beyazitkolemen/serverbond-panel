<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Actions\BaseServerBondService;

class DeleteUserService extends BaseServerBondService
{
    /**
     * Delete system user
     */
    public function execute(string $username): array
    {
        $params = [
            'username' => $username,
        ];

        $this->validateParams($params, ['username']);

        return $this->executeScript($this->getScriptPath('user', 'delete_user'), $params);
    }
}