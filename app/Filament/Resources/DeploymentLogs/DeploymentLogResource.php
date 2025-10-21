<?php

declare(strict_types=1);

namespace App\Filament\Resources\DeploymentLogs;

use App\Filament\Resources\DeploymentLogs\Pages\ListDeploymentLogs;
use App\Filament\Resources\DeploymentLogs\Tables\DeploymentLogsTable;
use App\Models\DeploymentLog;
use Filament\Resources\Resource;

class DeploymentLogResource extends Resource
{
    protected static ?string $model = DeploymentLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Deployment Logları';

    protected static ?string $modelLabel = 'Deployment Log';

    protected static ?string $pluralModelLabel = 'Deployment Logları';

    protected static ?string $navigationGroup = 'Site Yönetimi';

    protected static ?int $navigationSort = 4;

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return DeploymentLogsTable::make($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeploymentLogs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}

