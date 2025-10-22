<?php

declare(strict_types=1);

namespace App\Filament\Actions\Meta;

use App\Actions\Meta\DiagnosticsService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Throwable;

class DiagnosticsAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'diagnostics';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Hızlı Tanılama')
            ->icon('heroicon-o-wrench-screwdriver')
            ->color('warning')
            ->action(function (): void {
                $this->executeAction();
            });
    }

    private function executeAction(): void
    {
        try {
            $result = app(DiagnosticsService::class)->execute();

            $status = $result['status'] ?? null;
            $message = $result['message'] ?? null;
            $data = $result['data'] ?? null;

            if ($status === 'success') {
                if (is_array($data)) {
                    $message = 'Nginx: ' . ($data['nginx'] ?? 'Bilinmiyor')
                        . ' | PHP: ' . ($data['php'] ?? 'Bilinmiyor')
                        . ' | MySQL: ' . ($data['mysql'] ?? 'Bilinmiyor')
                        . ' | Redis: ' . ($data['redis'] ?? 'Bilinmiyor');
                }

                Notification::make()
                    ->title('Sistem Tanılaması')
                    ->body($message ?? 'Tanılama tamamlandı')
                    ->success()
                    ->send();

                return;
            }

            if ($status === 'warning') {
                Notification::make()
                    ->title('Sistem Tanılaması')
                    ->body($message ?? 'Tanılama tamamlandı ancak uyarılar mevcut')
                    ->warning()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Tanılama Hatası')
                ->body($message ?? 'Tanılama sırasında hata oluştu')
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Tanılama Hatası')
                ->body('Tanılama sırasında hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}