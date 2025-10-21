<?php

declare(strict_types=1);

namespace App\Actions\Maintenance;

use App\Actions\BaseServerBondService;

class BackupFilesService extends BaseServerBondService
{
    /**
     * Archive project files
     */
    public function execute(string $path, string $dest): array
    {
        $this->validateParams(['path', 'dest'], ['path', 'dest']);
        
        return $this->executeScript($this->getScriptPath('maintenance', 'backup_files'), [
            'path' => $path,
            'dest' => $dest
        ]);
    }
}