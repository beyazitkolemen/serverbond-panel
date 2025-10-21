<?php

declare(strict_types=1);

namespace App\Actions\Mysql;

use App\Actions\BaseServerBondService;

class ImportSqlService extends BaseServerBondService
{
    /**
     * Import SQL dump
     */
    public function execute(string $database, string $file): array
    {
        $this->validateParams(['database', 'file'], ['database', 'file']);
        
        return $this->executeScript($this->getScriptPath('mysql', 'import_sql'), [
            'database' => $database,
            'file' => $file
        ]);
    }
}