<?php

declare(strict_types=1);

namespace App\Filament\Actions\Nginx;

use App\Actions\Nginx\RestartService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Throwable;

class RestartAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'restart';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Nginx Yeniden Başlat')
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Nginx Yeniden Başlatma')
            ->modalDescription('Nginx servisi yeniden başlatılacak. Kısa bir süre web siteleri erişilemez olabilir.')
            ->modalSubmitActionLabel('Yeniden Başlat')
            ->action(function (): void {
                $this->executeAction();
            });
    }

    private function executeAction(): void
    {
        try {
            $result = app(RestartService::class)->execute();

            $status = $result['status'] ?? null;
            $message = $result['message'] ?? null;

            if ($status === 'success') {
                Notification::make()
                    ->title('Nginx Yeniden Başlatıldı')
                    ->body($message ?? 'Nginx servisi başarıyla yeniden başlatıldı')
                    ->success()
                    ->send();

                return;
            }

            if ($status === 'warning') {
                Notification::make()
                    ->title('Nginx Yeniden Başlatma Uyarısı')
                    ->body($message ?? 'Nginx yeniden başlatılırken uyarılar oluştu')
                    ->warning()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Nginx Yeniden Başlatma Hatası')
                ->body($message ?? 'Nginx yeniden başlatılırken hata oluştu')
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Nginx Yeniden Başlatma Hatası')
                ->body('Nginx yeniden başlatılırken hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}