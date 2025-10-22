<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Actions\BaseServerBondService;

class SshKeyAddService extends BaseServerBondService
{
    /**
     * Add SSH public key to user
     */
    public function execute(string $username, string $publicKey): array
    {
        $params = [
            'username' => $username,
            'public_key' => $publicKey,
        ];

        $this->validateParams($params, ['username', 'public_key']);

        return $this->executeScript($this->getScriptPath('user', 'ssh_key_add'), $params);
    }
}