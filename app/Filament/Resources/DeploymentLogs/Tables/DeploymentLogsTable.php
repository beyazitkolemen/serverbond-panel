<?php

declare(strict_types=1);

namespace App\Filament\Resources\DeploymentLogs\Tables;

use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DeploymentLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('site.name')
                    ->label('Site')
                    ->sortable()
                    ->searchable()
                    ->weight('bold')
                    ->icon('heroicon-o-globe-alt')
                    ->url(fn ($record) => $record->site ? route('filament.admin.resources.sites.edit', $record->site) : null),

                TextColumn::make('deployment.id')
                    ->label('Deployment #')
                    ->sortable()
                    ->searchable()
                    ->placeholder('-')
                    ->url(fn ($record) => $record->deployment ? route('filament.admin.resources.deployments.edit', $record->deployment) : null),

                BadgeColumn::make('level')
                    ->label('Seviye')
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->colors([
                        'success' => 'success',
                        'info' => 'info',
                        'warning' => 'warning',
                        'danger' => 'error',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'success',
                        'heroicon-o-information-circle' => 'info',
                        'heroicon-o-exclamation-triangle' => 'warning',
                        'heroicon-o-x-circle' => 'error',
                    ])
                    ->sortable(),

                TextColumn::make('message')
                    ->label('Mesaj')
                    ->limit(80)
                    ->searchable()
                    ->wrap()
                    ->tooltip(fn ($record) => $record->message),

                TextColumn::make('created_at')
                    ->label('Tarih')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('level')
                    ->label('Seviye')
                    ->options([
                        'info' => 'Info',
                        'success' => 'Success',
                        'warning' => 'Warning',
                        'error' => 'Error',
                    ])
                    ->multiple(),

                SelectFilter::make('site_id')
                    ->label('Site')
                    ->relationship('site', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Başlangıç Tarihi'),
                        DatePicker::make('created_until')
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
                ViewAction::make()
                    ->modalHeading(fn ($record) => 'Log Detayı #' . $record->id)
                    ->modalContent(fn ($record) => view('filament.resources.deployment-logs.view-log', [
                        'record' => $record,
                    ])),

                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('10s');
    }
}

