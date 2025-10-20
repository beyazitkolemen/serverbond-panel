<?php

declare(strict_types=1);

namespace App\Filament\Resources\Deployments\Tables;

use App\Enums\DeploymentStatus;
use App\Enums\DeploymentTrigger;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DeploymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('site.name')
                    ->label('Site')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-globe-alt')
                    ->weight('bold'),

                BadgeColumn::make('status')
                    ->label('Durum')
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->colors([
                        'gray' => DeploymentStatus::Pending,
                        'info' => DeploymentStatus::Running,
                        'success' => DeploymentStatus::Success,
                        'danger' => DeploymentStatus::Failed,
                    ])
                    ->icons([
                        'heroicon-o-clock' => DeploymentStatus::Pending,
                        'heroicon-o-arrow-path' => DeploymentStatus::Running,
                        'heroicon-o-check-circle' => DeploymentStatus::Success,
                        'heroicon-o-x-circle' => DeploymentStatus::Failed,
                    ]),

                BadgeColumn::make('trigger')
                    ->label('Tetikleyici')
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->colors([
                        'primary' => DeploymentTrigger::Manual,
                        'warning' => DeploymentTrigger::Auto,
                        'info' => DeploymentTrigger::Webhook,
                    ]),

                TextColumn::make('commit_hash')
                    ->label('Commit')
                    ->limit(8)
                    ->copyable()
                    ->copyMessage('Commit hash kopyalandı!')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('commit_message')
                    ->label('Mesaj')
                    ->limit(30)
                    ->placeholder('-')
                    ->tooltip(fn ($record) => $record->commit_message)
                    ->toggleable(),

                TextColumn::make('commit_author')
                    ->label('Yazar')
                    ->placeholder('-')
                    ->icon('heroicon-o-user')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('user.name')
                    ->label('Deploy Eden')
                    ->placeholder('Sistem')
                    ->icon('heroicon-o-user-circle')
                    ->toggleable(),

                TextColumn::make('duration')
                    ->label('Süre')
                    ->formatStateUsing(fn ($state) => $state ? gmdate('i:s', $state) . ' dk' : '-')
                    ->sortable()
                    ->icon('heroicon-o-clock'),

                TextColumn::make('started_at')
                    ->label('Başlangıç')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('finished_at')
                    ->label('Bitiş')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Durum')
                    ->options(DeploymentStatus::class)
                    ->multiple(),

                SelectFilter::make('trigger')
                    ->label('Tetikleyici')
                    ->options(DeploymentTrigger::class)
                    ->multiple(),

                SelectFilter::make('site_id')
                    ->label('Site')
                    ->relationship('site', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('viewLogs')
                    ->label('Logları Görüntüle')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->modalHeading(fn ($record) => "Deployment #{$record->id} Logları")
                    ->modalContent(fn ($record) => view('filament.deployment-logs', ['deployment' => $record]))
                    ->modalWidth('5xl')
                    ->slideOver(),

                Action::make('viewOutput')
                    ->label('Çıktı')
                    ->icon('heroicon-o-code-bracket')
                    ->color('success')
                    ->visible(fn ($record) => $record->output)
                    ->modalHeading('Deployment Çıktısı')
                    ->form([
                        Textarea::make('output')
                            ->label('')
                            ->rows(20)
                            ->disabled()
                            ->default(fn ($record) => $record->output),
                    ])
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Kapat'),

                Action::make('viewError')
                    ->label('Hata')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->error)
                    ->modalHeading('Deployment Hatası')
                    ->form([
                        Textarea::make('error')
                            ->label('')
                            ->rows(10)
                            ->disabled()
                            ->default(fn ($record) => $record->error),
                    ])
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Kapat'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Seçilenleri Sil'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('10s');
    }
}
