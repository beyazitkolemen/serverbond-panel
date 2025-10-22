<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Actions\BaseServerBondService;

class SshKeyRemoveService extends BaseServerBondService
{
    /**
     * Remove SSH key from user
     */
    public function execute(string $username, string $publicKeyFingerprint): array
    {
        $params = [
            'username' => $username,
            'public_key_fingerprint' => $publicKeyFingerprint,
        ];

        $this->validateParams($params, ['username', 'public_key_fingerprint']);

        return $this->executeScript($this->getScriptPath('user', 'ssh_key_remove'), $params);
    }
}