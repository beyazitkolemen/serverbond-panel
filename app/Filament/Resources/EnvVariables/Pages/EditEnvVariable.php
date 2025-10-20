<?php

namespace App\Filament\Resources\EnvVariables\Pages;

use App\Filament\Resources\EnvVariables\EnvVariableResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEnvVariable extends EditRecord
{
    protected static string $resource = EnvVariableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
