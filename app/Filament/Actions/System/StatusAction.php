<?php

declare(strict_types=1);

namespace App\Filament\Actions\System;

use App\Actions\System\StatusService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Throwable;

class StatusAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'status';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Sistem Durumu')
            ->icon('heroicon-o-chart-bar')
            ->color('info')
            ->action(function (): void {
                $this->executeAction();
            });
    }

    private function executeAction(): void
    {
        try {
            $result = app(StatusService::class)->execute();

            $status = $result['status'] ?? null;
            $message = $result['message'] ?? null;
            $data = $result['data'] ?? null;

            if ($status === 'success') {
                if (is_array($data)) {
                    $message = 'CPU: ' . ($data['cpu_usage'] ?? 'N/A') . '%'
                        . ' | RAM: ' . ($data['memory_usage'] ?? 'N/A') . '%'
                        . ' | Disk: ' . ($data['disk_usage'] ?? 'N/A') . '%'
                        . ' | Load: ' . ($data['load_average'] ?? 'N/A');
                }

                Notification::make()
                    ->title('Sistem Durumu')
                    ->body($message ?? 'Sistem durumu alındı')
                    ->success()
                    ->send();

                return;
            }

            if ($status === 'warning') {
                Notification::make()
                    ->title('Sistem Durumu')
                    ->body($message ?? 'Sistem durumu alınırken uyarılar oluştu')
                    ->warning()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Sistem Durumu Hatası')
                ->body($message ?? 'Durum bilgisi alınamadı')
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Sistem Durumu Hatası')
                ->body('Durum bilgisi alınırken hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
