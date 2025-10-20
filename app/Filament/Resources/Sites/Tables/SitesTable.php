<?php

namespace App\Filament\Resources\Sites\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('domain')
                    ->searchable(),
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('root_directory')
                    ->searchable(),
                TextColumn::make('public_directory')
                    ->searchable(),
                TextColumn::make('git_repository')
                    ->searchable(),
                TextColumn::make('git_branch')
                    ->searchable(),
                TextColumn::make('git_deploy_key')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('php_version')
                    ->searchable(),
                TextColumn::make('database_name')
                    ->searchable(),
                TextColumn::make('database_user')
                    ->searchable(),
                IconColumn::make('ssl_enabled')
                    ->boolean(),
                IconColumn::make('auto_deploy')
                    ->boolean(),
                TextColumn::make('last_deployed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
