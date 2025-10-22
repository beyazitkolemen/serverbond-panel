<?php

declare(strict_types=1);

namespace App\Filament\Actions\System;

use App\Actions\System\UpdateOsService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Throwable;

class UpdateOsAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'update_os';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Sistem Güncelle')
            ->icon('heroicon-o-arrow-path')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Sistem Paketlerini Güncelle')
            ->modalDescription('Apt paketlerini güncelleyecek ve yükseltecek. Bu işlem sistem yeniden başlatması gerektirebilir.')
            ->modalSubmitActionLabel('Güncelle')
            ->action(function (): void {
                $this->executeAction();
            });
    }

    private function executeAction(): void
    {
        try {
            $result = app(UpdateOsService::class)->execute();

            $status = $result['status'] ?? null;
            $message = $result['message'] ?? null;

            if ($status === 'success') {
                Notification::make()
                    ->title('Sistem Güncelleme Başarılı')
                    ->body($message ?? 'Paketler başarıyla güncellendi')
                    ->success()
                    ->send();

                return;
            }

            if ($status === 'warning') {
                Notification::make()
                    ->title('Sistem Güncelleme Uyarısı')
                    ->body($message ?? 'Güncelleme sırasında uyarılar oluştu')
                    ->warning()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Sistem Güncelleme Hatası')
                ->body($message ?? 'Güncelleme sırasında hata oluştu')
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Sistem Güncelleme Hatası')
                ->body('Güncelleme sırasında hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
