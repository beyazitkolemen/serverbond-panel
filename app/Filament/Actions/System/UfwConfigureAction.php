<?php

declare(strict_types=1);

namespace App\Filament\Actions\System;

use App\Actions\System\UfwConfigureService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Throwable;

class UfwConfigureAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'ufw_configure';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('UFW Firewall Kurulumu')
            ->icon('heroicon-o-shield-exclamation')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('UFW Firewall Yapılandırması')
            ->modalDescription('UFW firewall kurulacak ve temel kurallar (80/443/22) eklenecek.')
            ->modalSubmitActionLabel('Kur ve Yapılandır')
            ->action(function (): void {
                $this->executeAction();
            });
    }

    private function executeAction(): void
    {
        try {
            $result = app(UfwConfigureService::class)->execute();

            $status = $result['status'] ?? null;
            $message = $result['message'] ?? null;

            if ($status === 'success') {
                Notification::make()
                    ->title('UFW Firewall Kurulumu Başarılı')
                    ->body($message ?? 'UFW firewall başarıyla kuruldu ve yapılandırıldı')
                    ->success()
                    ->send();

                return;
            }

            if ($status === 'warning') {
                Notification::make()
                    ->title('UFW Firewall Kurulumu')
                    ->body($message ?? 'UFW kurulumu sırasında uyarılar oluştu')
                    ->warning()
                    ->send();

                return;
            }

            Notification::make()
                ->title('UFW Kurulum Hatası')
                ->body($message ?? 'UFW kurulumu sırasında hata oluştu')
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('UFW Kurulum Hatası')
                ->body('UFW kurulumu sırasında hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
