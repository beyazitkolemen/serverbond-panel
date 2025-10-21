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
        $this->validateParams(['database', 'dest'], ['database', 'dest']);
        
        return $this->executeScript($this->getScriptPath('maintenance', 'backup_db'), [
            'database' => $database,
            'dest' => $dest
        ]);
    }
}