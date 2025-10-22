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
                Select::make('type')
                    ->options(SiteType::class)
                    ->default('laravel')
                    ->required(),
                TextInput::make('root_directory')
                    ->required()
                    ->default('/var/www'),
                TextInput::make('public_directory'),
                TextInput::make('git_repository'),
                TextInput::make('git_branch')
                    ->required()
                    ->default('main'),
                TextInput::make('git_deploy_key'),
                Select::make('status')
                    ->options(SiteStatus::class)
                    ->default('inactive')
                    ->required(),
                Select::make('php_version')
                    ->options(PHPVersion::class)
                    ->default(PHPVersion::PHP84)
                    ->required(),
                TextInput::make('database_name'),
                TextInput::make('database_user'),
                TextInput::make('database_password')
                    ->password(),
                Toggle::make('ssl_enabled')
                    ->required(),
                Toggle::make('auto_deploy')
                    ->required(),
                TextInput::make('cloudflare_tunnel_id'),
                Toggle::make('cloudflare_tunnel_enabled')
                    ->required(),
                DateTimePicker::make('last_deployed_at'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                Textarea::make('deployment_script')
                    ->columnSpanFull(),
            ]);
    }
}
