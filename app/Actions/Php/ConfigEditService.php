<?php

declare(strict_types=1);

namespace App\Actions\Php;

use App\Actions\BaseServerBondService;

class ConfigEditService extends BaseServerBondService
{
    /**
     * Edit php.ini/pool.d parameters
     */
    public function execute(string $key, string $value, string $version = '8.3'): array
    {
        $params = [
            'version' => $version,
            'key' => $key,
            'value' => $value,
        ];

        $this->validateParams($params, ['key', 'value']);

        return $this->executeScript($this->getScriptPath('php', 'config_edit'), $params);
    }
}