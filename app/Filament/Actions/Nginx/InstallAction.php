<?php

declare(strict_types=1);

namespace App\Filament\Actions\Nginx;

use App\Actions\Nginx\InstallService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Throwable;

class InstallAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'install';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Nginx Kurulumu')
            ->icon('heroicon-o-globe-alt')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Nginx Web Sunucu Kurulumu')
            ->modalDescription('Nginx kurulacak ve temel optimizasyonlar yapılacak.')
            ->modalSubmitActionLabel('Kur')
            ->action(function (): void {
                $this->executeAction();
            });
    }

    private function executeAction(): void
    {
        try {
            $result = app(InstallService::class)->execute();

            $status = $result['status'] ?? null;
            $message = $result['message'] ?? null;

            if ($status === 'success') {
                Notification::make()
                    ->title('Nginx Kurulumu Başarılı')
                    ->body($message ?? 'Nginx başarıyla kuruldu ve yapılandırıldı')
                    ->success()
                    ->send();

                return;
            }

            if ($status === 'warning') {
                Notification::make()
                    ->title('Nginx Kurulumu')
                    ->body($message ?? 'Nginx kurulumu sırasında uyarılar oluştu')
                    ->warning()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Nginx Kurulum Hatası')
                ->body($message ?? 'Nginx kurulumu sırasında hata oluştu')
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Nginx Kurulum Hatası')
                ->body('Nginx kurulumu sırasında hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}