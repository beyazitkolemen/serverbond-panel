<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnvVariables;

use App\Filament\Resources\EnvVariables\Pages\CreateEnvVariable;
use App\Filament\Resources\EnvVariables\Pages\EditEnvVariable;
use App\Filament\Resources\EnvVariables\Pages\ListEnvVariables;
use App\Filament\Resources\EnvVariables\Schemas\EnvVariableForm;
use App\Filament\Resources\EnvVariables\Tables\EnvVariablesTable;
use App\Models\EnvVariable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class EnvVariableResource extends Resource
{
    protected static ?string $model = EnvVariable::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|UnitEnum|null $navigationGroup = 'Yapılandırma';

    protected static ?string $navigationLabel = 'Environment Variables';

    protected static ?string $modelLabel = 'Environment Variable';

    protected static ?string $pluralModelLabel = 'Environment Variables';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return EnvVariableForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EnvVariablesTable::configure($table);
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
            'index' => ListEnvVariables::route('/'),
            'create' => CreateEnvVariable::route('/create'),
            'edit' => EditEnvVariable::route('/{record}/edit'),
        ];
    }
}
