<?php

namespace App\Filament\Resources\SslCertificates\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SslCertificateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('site_id')
                    ->relationship('site', 'name')
                    ->required(),
                TextInput::make('type')
                    ->required()
                    ->default('letsencrypt'),
                TextInput::make('domain')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('active'),
                Textarea::make('certificate')
                    ->columnSpanFull(),
                Textarea::make('private_key')
                    ->columnSpanFull(),
                Textarea::make('chain')
                    ->columnSpanFull(),
                DateTimePicker::make('issued_at'),
                DateTimePicker::make('expires_at'),
                Toggle::make('auto_renew')
                    ->required(),
                Textarea::make('error')
                    ->columnSpanFull(),
            ]);
    }
}
