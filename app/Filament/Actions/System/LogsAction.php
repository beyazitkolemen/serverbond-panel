<?php

declare(strict_types=1);

namespace App\Filament\Actions\System;

use App\Actions\System\LogsService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Throwable;

class LogsAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'logs';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Sistem Logları')
            ->icon('heroicon-o-document-text')
            ->color('info')
            ->form([
                TextInput::make('lines')
                    ->label('Satır Sayısı')
                    ->numeric()
                    ->default(200)
                    ->minValue(1)
                    ->maxValue(10000)
                    ->helperText('Gösterilecek log satır sayısı (1-10000)'),
            ])
            ->action(function (array $data): void {
                $this->executeAction($data);
            });
    }

    private function executeAction(array $data): void
    {
        try {
            $lines = isset($data['lines']) && $data['lines'] !== '' ? (int) $data['lines'] : 200;
            $result = app(LogsService::class)->execute($lines);

            $status = $result['status'] ?? null;
            $message = $result['message'] ?? null;

            if ($status === 'success') {
                Notification::make()
                    ->title('Sistem Logları')
                    ->body($message ?? 'Loglar başarıyla alındı')
                    ->success()
                    ->send();

                return;
            }

            if ($status === 'warning') {
                Notification::make()
                    ->title('Sistem Logları')
                    ->body($message ?? 'Loglar alınırken uyarılar oluştu')
                    ->warning()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Log Hatası')
                ->body($message ?? 'Loglar alınırken hata oluştu')
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Log Hatası')
                ->body('Loglar alınırken hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
