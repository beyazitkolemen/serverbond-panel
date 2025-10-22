<?php

declare(strict_types=1);

namespace App\Filament\Actions\Nginx;

use App\Actions\Nginx\ReloadService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Throwable;

class ReloadAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'reload';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Nginx Konfig Reload')
            ->icon('heroicon-o-arrow-path')
            ->color('info')
            ->action(function (): void {
                $this->executeAction();
            });
    }

    private function executeAction(): void
    {
        try {
            $result = app(ReloadService::class)->execute();

            $status = $result['status'] ?? null;
            $message = $result['message'] ?? null;

            if ($status === 'success') {
                Notification::make()
                    ->title('Nginx Konfig Reload Başarılı')
                    ->body($message ?? 'Nginx konfigürasyonu başarıyla yeniden yüklendi')
                    ->success()
                    ->send();

                return;
            }

            if ($status === 'warning') {
                Notification::make()
                    ->title('Nginx Konfig Reload Uyarısı')
                    ->body($message ?? 'Nginx konfigürasyonu yeniden yüklenirken uyarılar oluştu')
                    ->warning()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Nginx Reload Hatası')
                ->body($message ?? 'Nginx konfigürasyonu yeniden yüklenemedi')
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Nginx Reload Hatası')
                ->body('Nginx konfig reload sırasında hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}