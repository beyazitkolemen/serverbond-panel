<?php

namespace App\Filament\Resources\Deployments;

use App\Filament\Resources\Deployments\Pages\CreateDeployment;
use App\Filament\Resources\Deployments\Pages\EditDeployment;
use App\Filament\Resources\Deployments\Pages\ListDeployments;
use App\Filament\Resources\Deployments\Schemas\DeploymentForm;
use App\Filament\Resources\Deployments\Tables\DeploymentsTable;
use App\Models\Deployment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DeploymentResource extends Resource
{
    protected static ?string $model = Deployment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationGroup = 'Site Yönetimi';

    protected static ?string $navigationLabel = 'Deployments';

    protected static ?string $modelLabel = 'Deployment';

    protected static ?string $pluralModelLabel = 'Deployments';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return DeploymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeploymentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeployments::route('/'),
            // Create ve Edit'i devre dışı bırakıyoruz - deploymentlar otomatik oluşturulacak
            // 'create' => CreateDeployment::route('/create'),
            // 'edit' => EditDeployment::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Deploymentlar manuel oluşturulamaz
    }
}
