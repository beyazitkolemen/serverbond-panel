<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Actions\BaseServerBondService;

class AddUserService extends BaseServerBondService
{
    /**
     * Add system user
     */
    public function execute(string $username, bool $withSudo = false): array
    {
        $params = [
            'username' => $username,
            'with_sudo' => $withSudo,
        ];

        $this->validateParams($params, ['username']);

        return $this->executeScript($this->getScriptPath('user', 'add_user'), $params);
    }
}