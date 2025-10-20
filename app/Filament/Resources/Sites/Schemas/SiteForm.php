<?php

namespace App\Filament\Resources\Sites\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('domain')
                    ->required(),
                TextInput::make('type')
                    ->required()
                    ->default('laravel'),
                TextInput::make('root_directory')
                    ->required()
                    ->default('/var/www'),
                TextInput::make('public_directory'),
                TextInput::make('git_repository'),
                TextInput::make('git_branch')
                    ->required()
                    ->default('main'),
                TextInput::make('git_deploy_key'),
                TextInput::make('status')
                    ->required()
                    ->default('inactive'),
                TextInput::make('php_version'),
                TextInput::make('database_name'),
                TextInput::make('database_user'),
                TextInput::make('database_password')
                    ->password(),
                Toggle::make('ssl_enabled')
                    ->required(),
                Toggle::make('auto_deploy')
                    ->required(),
                DateTimePicker::make('last_deployed_at'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
