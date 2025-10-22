<?php

declare(strict_types=1);

namespace App\Filament\Actions\Meta;

use App\Actions\Meta\UpdateService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Throwable;

class UpdateAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'update';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Agent Güncelle')
            ->icon('heroicon-o-arrow-path')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Agent Güncelleme')
            ->modalDescription('Agent kendini güncelleyecek (git pull + service restart). Bu işlem sırasında agent geçici olarak kullanılamayabilir.')
            ->modalSubmitActionLabel('Güncelle')
            ->action(function (): void {
                $this->executeAction();
            });
    }

    private function executeAction(): void
    {
        try {
            $result = app(UpdateService::class)->execute();

            $status = $result['status'] ?? null;
            $message = $result['message'] ?? null;

            if ($status === 'success') {
                Notification::make()
                    ->title('Agent Güncelleme Başarılı')
                    ->body($message ?? 'Agent başarıyla güncellendi')
                    ->success()
                    ->send();

                return;
            }

            if ($status === 'warning') {
                Notification::make()
                    ->title('Agent Güncelleme Uyarısı')
                    ->body($message ?? 'Güncelleme sırasında uyarılar oluştu')
                    ->warning()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Agent Güncelleme Hatası')
                ->body($message ?? 'Güncelleme sırasında hata oluştu')
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Agent Güncelleme Hatası')
                ->body('Güncelleme sırasında hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}