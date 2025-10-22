<?php

declare(strict_types=1);

namespace App\Actions\Mysql;

use App\Actions\BaseServerBondService;

class DeleteDatabaseService extends BaseServerBondService
{
    /**
     * Delete database
     */
    public function execute(string $name): array
    {
        $params = [
            'name' => $name,
        ];

        $this->validateParams($params, ['name']);

        return $this->executeScript($this->getScriptPath('mysql', 'delete_database'), $params);
    }
}