<?php

declare(strict_types=1);

namespace App\Filament\Resources\Databases;

use App\Filament\Resources\Databases\Pages\CreateDatabase;
use App\Filament\Resources\Databases\Pages\EditDatabase;
use App\Filament\Resources\Databases\Pages\ListDatabases;
use App\Filament\Resources\Databases\Schemas\DatabaseForm;
use App\Filament\Resources\Databases\Tables\DatabasesTable;
use App\Models\Database;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class DatabaseResource extends Resource
{
    protected static ?string $model = Database::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-circle-stack';

    protected static string|UnitEnum|null $navigationGroup = 'Veritabanı';

    protected static ?string $navigationLabel = 'MySQL Databases';

    protected static ?string $modelLabel = 'Database';

    protected static ?string $pluralModelLabel = 'Databases';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return DatabaseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DatabasesTable::configure($table);
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
            'index' => ListDatabases::route('/'),
            'create' => CreateDatabase::route('/create'),
            'edit' => EditDatabase::route('/{record}/edit'),
        ];
    }
}
