<?php

namespace App\Filament\Resources\EnvVariables\Pages;

use App\Filament\Resources\EnvVariables\EnvVariableResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEnvVariable extends CreateRecord
{
    protected static string $resource = EnvVariableResource::class;
}
