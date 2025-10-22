<?php

declare(strict_types=1);

namespace App\Filament\Actions\Nginx;

use App\Actions\Nginx\RemoveSiteService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Throwable;

class RemoveSiteAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'remove_site';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Site Kaldır')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Site Kaldırma')
            ->modalDescription('Site kaldırılacak ve konfigürasyon dosyası silinecek.')
            ->form([
                TextInput::make('domain')
                    ->label('Domain')
                    ->required()
                    ->placeholder('example.com')
                    ->helperText('Kaldırılacak site domain adresi'),
            ])
            ->action(function (array $data): void {
                $this->executeAction($data);
            });
    }

    private function executeAction(array $data): void
    {
        try {
            $result = app(RemoveSiteService::class)->execute($data['domain']);

            $status = $result['status'] ?? null;
            $message = $result['message'] ?? null;

            if ($status === 'success') {
                Notification::make()
                    ->title('Site Kaldırıldı')
                    ->body($message ?? "Site {$data['domain']} başarıyla kaldırıldı")
                    ->success()
                    ->send();

                return;
            }

            if ($status === 'warning') {
                Notification::make()
                    ->title('Site Kaldırma Uyarısı')
                    ->body($message ?? 'Site kaldırılırken uyarılar oluştu')
                    ->warning()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Site Kaldırma Hatası')
                ->body($message ?? 'Site kaldırılırken hata oluştu')
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Site Kaldırma Hatası')
                ->body('Site kaldırılırken hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}