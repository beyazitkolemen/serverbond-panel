<?php

declare(strict_types=1);

namespace App\Actions\Supervisor;

use App\Actions\BaseServerBondService;

class RemoveProgramService extends BaseServerBondService
{
    /**
     * Remove program
     */
    public function execute(string $name): array
    {
        $params = [
            'name' => $name,
        ];

        $this->validateParams($params, ['name']);

        return $this->executeScript($this->getScriptPath('supervisor', 'remove_program'), $params);
    }
}