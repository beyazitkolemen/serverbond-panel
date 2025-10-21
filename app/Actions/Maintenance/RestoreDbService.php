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
        $this->validateParams(['database', 'src'], ['database', 'src']);
        
        return $this->executeScript($this->getScriptPath('maintenance', 'restore_db'), [
            'database' => $database,
            'src' => $src
        ]);
    }
}