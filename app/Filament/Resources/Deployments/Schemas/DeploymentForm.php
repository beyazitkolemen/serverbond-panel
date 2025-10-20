<?php

namespace App\Filament\Resources\Deployments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class DeploymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('site_id')
                    ->relationship('site', 'name')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name'),
                TextInput::make('commit_hash'),
                TextInput::make('commit_message'),
                TextInput::make('commit_author'),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('trigger')
                    ->required()
                    ->default('manual'),
                Textarea::make('output')
                    ->columnSpanFull(),
                Textarea::make('error')
                    ->columnSpanFull(),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('finished_at'),
                TextInput::make('duration')
                    ->numeric(),
            ]);
    }
}
