<?php

declare(strict_types=1);

namespace App\Filament\Actions\Meta;

use App\Actions\Meta\HealthService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Throwable;

class HealthAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'health';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Agent Sağlık Kontrolü')
            ->icon('heroicon-o-heart')
            ->color('success')
            ->action(function (): void {
                $this->executeAction();
            });
    }

    private function executeAction(): void
    {
        try {
            $result = app(HealthService::class)->execute();

            $status = $result['status'] ?? null;
            $message = $result['message'] ?? null;

            if ($status === 'success') {
                Notification::make()
                    ->title('Agent Sağlıklı')
                    ->body($message ?? 'Tüm sistemler normal çalışıyor')
                    ->success()
                    ->send();

                return;
            }

            if ($status === 'warning') {
                Notification::make()
                    ->title('Agent Sağlık Uyarısı')
                    ->body($message ?? 'Bazı sistemlerde sorun tespit edildi')
                    ->warning()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Sağlık Kontrolü Hatası')
                ->body($message ?? 'Sağlık kontrolü başarısız oldu')
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Sağlık Kontrolü Hatası')
                ->body('Sağlık kontrolü sırasında hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}