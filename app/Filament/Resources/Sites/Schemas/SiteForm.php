<?php

namespace App\Filament\Resources\Sites\Schemas;

use App\Enums\SiteType;
use App\Enums\PHPVersion;
use App\Enums\SiteStatus;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class SiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Temel Bilgiler')
                    ->description('Site ile ilgili temel bilgileri girin')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextInput::make('name')
                            ->label('Site Adı')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('domain')
                            ->label('Domain')
                            ->required()
                            ->url()
                            ->maxLength(255),
                        TextInput::make('git_repository')
                            ->label('Git Repository')
                            ->url()
                            ->helperText('Git repository URL\'si'),
                        TextInput::make('git_branch')
                            ->label('Git Branch')
                            ->required()
                            ->default('main'),
                        Select::make('type')
                            ->label('Site Tipi')
                            ->options(SiteType::class)
                            ->default('laravel')
                            ->required(),
                        Select::make('php_version')
                            ->label('PHP Versiyonu')
                            ->options(PHPVersion::class)
                            ->default(PHPVersion::PHP84)
                            ->required(),

                    ])
                    ->columns(2)->columnSpanFull(),







                Section::make('Deployment Script')
                    ->description('Özel deployment script\'i')
                    ->icon('heroicon-o-command-line')
                    ->schema([
                        Textarea::make('deployment_script')
                            ->label('Deployment Script')
                            ->rows(10)
                            ->helperText('Deploy sırasında çalıştırılacak özel komutlar')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()->visibleOn('edit'),

                Section::make('Notlar')
                    ->description('Site hakkında ek notlar')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notlar')
                            ->rows(4)
                            ->helperText('Site hakkında önemli notlar ve açıklamalar')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()->visibleOn('edit'),
            ]);
    }
}
