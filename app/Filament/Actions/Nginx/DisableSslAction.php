<?php

declare(strict_types=1);

namespace App\Filament\Actions\Nginx;

use App\Actions\Nginx\DisableSslService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Throwable;

class DisableSslAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'disable_ssl';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('SSL Devre Dışı Bırak')
            ->icon('heroicon-o-shield-exclamation')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('SSL Devre Dışı Bırakma')
            ->modalDescription('SSL sertifikası kaldırılacak ve site HTTP-only moduna geçecek.')
            ->form([
                TextInput::make('domain')
                    ->label('Domain')
                    ->required()
                    ->placeholder('example.com')
                    ->helperText('SSL\'i kaldırılacak domain'),
            ])
            ->action(function (array $data): void {
                $this->executeAction($data);
            });
    }

    private function executeAction(array $data): void
    {
        try {
            $result = app(DisableSslService::class)->execute($data['domain']);

            $status = $result['status'] ?? null;
            $message = $result['message'] ?? null;

            if ($status === 'success') {
                Notification::make()
                    ->title('SSL Devre Dışı Bırakıldı')
                    ->body($message ?? "SSL {$data['domain']} için devre dışı bırakıldı")
                    ->warning()
                    ->send();

                return;
            }

            if ($status === 'warning') {
                Notification::make()
                    ->title('SSL Devre Dışı Bırakma Uyarısı')
                    ->body($message ?? 'SSL devre dışı bırakılırken uyarılar oluştu')
                    ->warning()
                    ->send();

                return;
            }

            Notification::make()
                ->title('SSL Devre Dışı Bırakma Hatası')
                ->body($message ?? 'SSL devre dışı bırakılırken hata oluştu')
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('SSL Devre Dışı Bırakma Hatası')
                ->body('SSL devre dışı bırakılırken hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}