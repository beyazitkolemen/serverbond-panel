<?php

declare(strict_types=1);

namespace App\Filament\Actions\System;

use App\Actions\System\RebootService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Throwable;

class RebootAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'reboot';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Sistemi Yeniden Başlat')
            ->icon('heroicon-o-arrow-path')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Sistem Yeniden Başlatma')
            ->modalDescription('Sunucuyu yeniden başlatacak. Bu işlem tüm servisleri durduracak ve sistem kapanacak.')
            ->modalSubmitActionLabel('Yeniden Başlat')
            ->action(function (): void {
                $this->executeAction();
            });
    }

    private function executeAction(): void
    {
        try {
            $result = app(RebootService::class)->execute();

            $status = $result['status'] ?? null;
            $message = $result['message'] ?? null;

            if ($status === 'success') {
                Notification::make()
                    ->title('Sistem Yeniden Başlatılıyor')
                    ->body($message ?? 'Sunucu yeniden başlatma komutu gönderildi')
                    ->warning()
                    ->send();

                return;
            }

            if ($status === 'warning') {
                Notification::make()
                    ->title('Yeniden Başlatma Uyarısı')
                    ->body($message ?? 'Yeniden başlatma işlemi ile ilgili uyarılar oluştu')
                    ->warning()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Yeniden Başlatma Hatası')
                ->body($message ?? 'Yeniden başlatma sırasında hata oluştu')
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Yeniden Başlatma Hatası')
                ->body('Yeniden başlatma sırasında hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
