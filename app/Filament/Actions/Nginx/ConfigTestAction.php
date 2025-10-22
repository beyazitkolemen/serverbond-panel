<?php

declare(strict_types=1);

namespace App\Filament\Actions\Nginx;

use App\Actions\Nginx\ConfigTestService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Throwable;

class ConfigTestAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'config_test';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Nginx Konfig Test')
            ->icon('heroicon-o-check-circle')
            ->color('info')
            ->action(function (): void {
                $this->executeAction();
            });
    }

    private function executeAction(): void
    {
        try {
            $result = app(ConfigTestService::class)->execute();

            $status = $result['status'] ?? null;
            $message = $result['message'] ?? null;

            if ($status === 'success') {
                Notification::make()
                    ->title('Nginx Konfig Test Başarılı')
                    ->body($message ?? 'Nginx konfigürasyonu geçerli')
                    ->success()
                    ->send();

                return;
            }

            if ($status === 'warning') {
                Notification::make()
                    ->title('Nginx Konfig Test Uyarısı')
                    ->body($message ?? 'Konfigürasyon testi uyarılarla tamamlandı')
                    ->warning()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Nginx Konfig Test Hatası')
                ->body($message ?? 'Konfigürasyon hatası oluştu')
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Nginx Konfig Test Hatası')
                ->body('Konfig test sırasında hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}