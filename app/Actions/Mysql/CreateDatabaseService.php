<?php

declare(strict_types=1);

namespace App\Actions\Mysql;

use App\Actions\BaseServerBondService;

class CreateDatabaseService extends BaseServerBondService
{
    /**
     * Create database
     */
    public function execute(
        string $name,
        string $charset = 'utf8mb4',
        string $collation = 'utf8mb4_unicode_ci'
    ): array {
        $params = [
            'name' => $name,
            'charset' => $charset,
            'collation' => $collation,
        ];

        $this->validateParams($params, ['name']);

        return $this->executeScript($this->getScriptPath('mysql', 'create_database'), $params);
    }
}