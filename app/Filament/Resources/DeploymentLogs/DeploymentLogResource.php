<?php

declare(strict_types=1);

namespace App\Filament\Resources\DeploymentLogs;

use App\Filament\Resources\DeploymentLogs\Pages\ListDeploymentLogs;
use App\Filament\Resources\DeploymentLogs\Tables\DeploymentLogsTable;
use App\Models\DeploymentLog;
use Filament\Resources\Resource;
use UnitEnum;
use BackedEnum;
use Filament\Tables\Table;

class DeploymentLogResource extends Resource
{
    protected static ?string $model = DeploymentLog::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|UnitEnum|null $navigationGroup = 'Site Yönetimi';

    protected static ?string $navigationLabel = 'Deployment Logları';

    protected static ?string $modelLabel = 'Deployment Log';

    protected static ?string $pluralModelLabel = 'Deployment Logları';
    protected static ?int $navigationSort = 3;

    public static function table(Table $table): Table
    {
        return DeploymentLogsTable::configure($table);
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

