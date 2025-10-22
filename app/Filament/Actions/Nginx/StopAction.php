<?php

declare(strict_types=1);

namespace App\Filament\Actions\Nginx;

use App\Actions\Nginx\StopService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Throwable;

class StopAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'stop';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Nginx Durdur')
            ->icon('heroicon-o-stop')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Nginx Durdurma')
            ->modalDescription('Nginx servisi durdurulacak. Bu işlem web sitelerini erişilemez hale getirebilir.')
            ->modalSubmitActionLabel('Durdur')
            ->action(function (): void {
                $this->executeAction();
            });
    }

    private function executeAction(): void
    {
        try {
            $result = app(StopService::class)->execute();

            $status = $result['status'] ?? null;
            $message = $result['message'] ?? null;

            if ($status === 'success') {
                Notification::make()
                    ->title('Nginx Durduruldu')
                    ->body($message ?? 'Nginx servisi başarıyla durduruldu')
                    ->warning()
                    ->send();

                return;
            }

            if ($status === 'warning') {
                Notification::make()
                    ->title('Nginx Durdurma Uyarısı')
                    ->body($message ?? 'Nginx durdurulurken uyarılar oluştu')
                    ->warning()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Nginx Durdurma Hatası')
                ->body($message ?? 'Nginx durdurulurken hata oluştu')
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Nginx Durdurma Hatası')
                ->body('Nginx durdurulurken hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}