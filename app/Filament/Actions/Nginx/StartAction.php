<?php

declare(strict_types=1);

namespace App\Filament\Actions\Nginx;

use App\Actions\Nginx\StartService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Throwable;

class StartAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'start';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Nginx Başlat')
            ->icon('heroicon-o-play')
            ->color('success')
            ->action(function (): void {
                $this->executeAction();
            });
    }

    private function executeAction(): void
    {
        try {
            $result = app(StartService::class)->execute();

            $status = $result['status'] ?? null;
            $message = $result['message'] ?? null;

            if ($status === 'success') {
                Notification::make()
                    ->title('Nginx Başlatıldı')
                    ->body($message ?? 'Nginx servisi başarıyla başlatıldı')
                    ->success()
                    ->send();

                return;
            }

            if ($status === 'warning') {
                Notification::make()
                    ->title('Nginx Başlatma Uyarısı')
                    ->body($message ?? 'Nginx başlatılırken uyarılar oluştu')
                    ->warning()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Nginx Başlatma Hatası')
                ->body($message ?? 'Nginx başlatılırken hata oluştu')
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Nginx Başlatma Hatası')
                ->body('Nginx başlatılırken hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}