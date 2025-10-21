<?php

declare(strict_types=1);

namespace App\Filament\Resources\DeploymentLogs\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DeploymentLogsTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('site.name')
                    ->label('Site')
                    ->sortable()
                    ->searchable()
                    ->url(fn ($record) => $record->site ? route('filament.admin.resources.sites.edit', $record->site) : null),

                Tables\Columns\TextColumn::make('deployment.id')
                    ->label('Deployment #')
                    ->sortable()
                    ->searchable()
                    ->url(fn ($record) => $record->deployment ? route('filament.admin.resources.deployments.edit', $record->deployment) : null),

                Tables\Columns\BadgeColumn::make('level')
                    ->label('Seviye')
                    ->colors([
                        'success' => 'success',
                        'info' => 'info',
                        'warning' => 'warning',
                        'danger' => 'error',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('message')
                    ->label('Mesaj')
                    ->limit(100)
                    ->searchable()
                    ->wrap()
                    ->tooltip(fn ($record) => $record->message),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tarih')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('level')
                    ->label('Seviye')
                    ->options([
                        'info' => 'Info',
                        'success' => 'Success',
                        'warning' => 'Warning',
                        'error' => 'Error',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('site_id')
                    ->label('Site')
                    ->relationship('site', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label('Başlangıç Tarihi'),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label('Bitiş Tarihi'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading(fn ($record) => 'Log Detayı #' . $record->id)
                    ->modalContent(fn ($record) => view('filament.resources.deployment-logs.view-log', [
                        'record' => $record,
                    ])),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('10s');
    }
}

