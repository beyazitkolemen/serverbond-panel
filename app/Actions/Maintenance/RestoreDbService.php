<?php

declare(strict_types=1);

namespace App\Actions\Maintenance;

use App\Actions\BaseServerBondService;

class RestoreDbService extends BaseServerBondService
{
    /**
     * Database restore
     */
    public function execute(string $database, string $src): array
    {
        $params = [
            'database' => $database,
            'src' => $src,
        ];

        $this->validateParams($params, ['database', 'src']);

        return $this->executeScript($this->getScriptPath('maintenance', 'restore_db'), $params);
    }
}