<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Actions\BaseServerBondService;

class ListUsersService extends BaseServerBondService
{
    /**
     * List users
     */
    public function execute(): array
    {
        return $this->executeScript($this->getScriptPath('user', 'list_users'));
    }
}