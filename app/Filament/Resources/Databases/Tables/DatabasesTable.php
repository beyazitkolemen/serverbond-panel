<?php

declare(strict_types=1);

namespace App\Filament\Resources\Databases\Tables;

use App\Services\MySQLService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DatabasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Database Adı')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-circle-stack')
                    ->copyable()
                    ->copyMessage('Database adı kopyalandı!'),

                TextColumn::make('username')
                    ->label('Kullanıcı')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Kullanıcı adı kopyalandı!')
                    ->icon('heroicon-o-user'),

                TextColumn::make('password')
                    ->label('Şifre')
                    ->placeholder('•••••••••')
                    ->copyable()
                    ->copyMessage('Şifre kopyalandı!')
                    ->icon('heroicon-o-key')
                    ->toggleable(),

                TextColumn::make('site.name')
                    ->label('İlişkili Site')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-')
                    ->icon('heroicon-o-globe-alt')
                    ->toggleable(),

                TextColumn::make('charset')
                    ->label('Charset')
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                TextColumn::make('collation')
                    ->label('Collation')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('max_connections')
                    ->label('Max Conn.')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('site_id')
                    ->label('Site')
                    ->relationship('site', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('charset')
                    ->label('Charset')
                    ->options([
                        'utf8mb4' => 'utf8mb4',
                        'utf8' => 'utf8',
                        'latin1' => 'latin1',
                    ]),
            ])
            ->recordActions([
                Action::make('createInMySQL')
                    ->label('MySQL\'e Oluştur')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('MySQL\'de Oluştur')
                    ->modalDescription(fn($record) => "'{$record->name}' database ve kullanıcısı MySQL server'da oluşturulacak.")
                    ->action(function ($record) {
                        $mysqlService = app(MySQLService::class);

                        try {
                            // Database oluştur
                            $mysqlService->createDatabase($record->name, $record->charset, $record->collation);

                            // Kullanıcı oluştur
                            $mysqlService->createUser($record->username, $record->password);

                            // İzinleri ver
                            $mysqlService->grantPermissions($record->name, $record->username);

                            Notification::make()
                                ->title('MySQL Database Oluşturuldu')
                                ->success()
                                ->body("'{$record->name}' başarıyla MySQL'de oluşturuldu.")
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('MySQL Hatası')
                                ->danger()
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

                Action::make('testConnection')
                    ->label('Bağlantıyı Test Et')
                    ->icon('heroicon-o-signal')
                    ->color('info')
                    ->action(function ($record) {
                        $mysqlService = app(MySQLService::class);

                        try {
                            $result = $mysqlService->testConnection($record->name, $record->username, $record->password);

                            if ($result) {
                                Notification::make()
                                    ->title('Bağlantı Başarılı')
                                    ->success()
                                    ->body("'{$record->name}' database'ine başarıyla bağlanıldı.")
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Bağlantı Başarısız')
                                    ->danger()
                                    ->body("'{$record->name}' database'ine bağlanılamadı.")
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Test Hatası')
                                ->danger()
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

                EditAction::make()
                    ->label('Düzenle'),

                DeleteAction::make()
                    ->label('Sil')
                    ->requiresConfirmation()
                    ->before(function ($record) {
                        // Database silinmeden önce MySQL'den de sil
                        $mysqlService = app(MySQLService::class);

                        try {
                            $mysqlService->dropDatabase($record->name);
                            $mysqlService->dropUser($record->username);
                        } catch (\Exception $e) {
                            // MySQL'de yoksa hata verme
                            \Log::warning('MySQL database/user deletion failed', [
                                'database' => $record->name,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }),
            ])
            ->toolbarActions([
                Action::make('syncFromMySQL')
                    ->label('MySQL\'den Senkronize Et')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('MySQL Database\'lerini Senkronize Et')
                    ->modalDescription('MySQL server\'daki mevcut database\'ler panel\'e aktarılacak. Mevcut kayıtlar korunacak.')
                    ->modalSubmitActionLabel('Senkronize Et')
                    ->action(function () {
                        $mysqlService = app(MySQLService::class);

                        try {
                            $result = $mysqlService->syncDatabases();

                            Notification::make()
                                ->title('Senkronizasyon Tamamlandı')
                                ->success()
                                ->body(sprintf(
                                    '%d database bulundu. %d yeni kayıt eklendi, %d kayıt zaten mevcut.',
                                    $result['total'],
                                    count($result['synced']),
                                    count($result['skipped'])
                                ))
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Senkronizasyon Hatası')
                                ->danger()
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Seçilenleri Sil'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
