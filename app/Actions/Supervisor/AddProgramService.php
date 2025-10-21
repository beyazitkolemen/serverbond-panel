<?php

declare(strict_types=1);

namespace App\Actions\Supervisor;

use App\Actions\BaseServerBondService;

class AddProgramService extends BaseServerBondService
{
    /**
     * Add new program (queue:work etc.)
     */
    public function execute(string $name, string $configBase64): array
    {
        $this->validateParams(['name', 'config_base64'], ['name', 'config_base64']);
        
        return $this->executeScript($this->getScriptPath('supervisor', 'add_program'), [
            'name' => $name,
            'config_base64' => $configBase64
        ]);
    }
}