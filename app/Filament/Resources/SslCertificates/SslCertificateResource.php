<?php

namespace App\Filament\Resources\SslCertificates;

use App\Filament\Resources\SslCertificates\Pages\CreateSslCertificate;
use App\Filament\Resources\SslCertificates\Pages\EditSslCertificate;
use App\Filament\Resources\SslCertificates\Pages\ListSslCertificates;
use App\Filament\Resources\SslCertificates\Schemas\SslCertificateForm;
use App\Filament\Resources\SslCertificates\Tables\SslCertificatesTable;
use App\Models\SslCertificate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SslCertificateResource extends Resource
{
    protected static ?string $model = SslCertificate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    
    protected static ?string $navigationGroup = 'Güvenlik';
    
    protected static ?string $navigationLabel = 'SSL Sertifikaları';
    
    protected static ?string $modelLabel = 'SSL Sertifikası';
    
    protected static ?string $pluralModelLabel = 'SSL Sertifikaları';
    
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return SslCertificateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SslCertificatesTable::configure($table);
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
            'index' => ListSslCertificates::route('/'),
            'create' => CreateSslCertificate::route('/create'),
            'edit' => EditSslCertificate::route('/{record}/edit'),
        ];
    }
}
