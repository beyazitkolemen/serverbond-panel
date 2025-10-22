<?php

declare(strict_types=1);

namespace App\Filament\Actions\System;

use App\Actions\System\Fail2banInstallService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Throwable;

class Fail2banInstallAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'fail2ban_install';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Fail2ban Kurulumu')
            ->icon('heroicon-o-shield-check')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Fail2ban Güvenlik Kurulumu')
            ->modalDescription('Fail2ban kurulacak ve SSH/Nginx jail\'leri etkinleştirilecek.')
            ->modalSubmitActionLabel('Kur ve Etkinleştir')
            ->action(function (): void {
                $this->executeAction();
            });
    }

    private function executeAction(): void
    {
        try {
            $result = app(Fail2banInstallService::class)->execute();

            $status = $result['status'] ?? null;
            $message = $result['message'] ?? null;

            if ($status === 'success') {
                Notification::make()
                    ->title('Fail2ban Kurulumu Başarılı')
                    ->body($message ?? 'Fail2ban başarıyla kuruldu ve yapılandırıldı')
                    ->success()
                    ->send();

                return;
            }

            if ($status === 'warning') {
                Notification::make()
                    ->title('Fail2ban Kurulumu')
                    ->body($message ?? 'Fail2ban kurulumu sırasında uyarılar oluştu')
                    ->warning()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Fail2ban Kurulum Hatası')
                ->body($message ?? 'Fail2ban kurulumu sırasında hata oluştu')
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Fail2ban Kurulum Hatası')
                ->body('Fail2ban kurulumu sırasında hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
