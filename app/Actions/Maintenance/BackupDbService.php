<?php

declare(strict_types=1);

namespace App\Actions\Maintenance;

use App\Actions\BaseServerBondService;

class BackupDbService extends BaseServerBondService
{
    /**
     * Database backup (mysqldump)
     */
    public function execute(string $database, string $dest): array
    {
        $params = [
            'database' => $database,
            'dest' => $dest,
        ];

        $this->validateParams($params, ['database', 'dest']);

        return $this->executeScript($this->getScriptPath('maintenance', 'backup_db'), $params);
    }
}