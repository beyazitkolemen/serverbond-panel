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
        $this->validateParams(['username'], ['username']);
        
        return $this->executeScript($this->getScriptPath('user', 'add_user'), [
            'username' => $username,
            'with_sudo' => $withSudo
        ]);
    }
}