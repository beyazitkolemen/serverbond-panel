<?php

declare(strict_types=1);

namespace App\Actions\Mysql;

use App\Actions\BaseServerBondService;

class ExportSqlService extends BaseServerBondService
{
    /**
     * Export database dump
     */
    public function execute(string $database, string $file): array
    {
        $this->validateParams(['database', 'file'], ['database', 'file']);
        
        return $this->executeScript($this->getScriptPath('mysql', 'export_sql'), [
            'database' => $database,
            'file' => $file
        ]);
    }
}