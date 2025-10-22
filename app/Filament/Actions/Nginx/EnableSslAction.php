<?php

declare(strict_types=1);

namespace App\Filament\Actions\Nginx;

use App\Actions\Nginx\EnableSslService;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Throwable;

class EnableSslAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'enable_ssl';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('SSL Etkinleştir')
            ->icon('heroicon-o-shield-check')
            ->color('success')
            ->form([
                TextInput::make('domain')
                    ->label('Domain')
                    ->required()
                    ->placeholder('example.com')
                    ->helperText('SSL sertifikası alınacak domain'),
                TextInput::make('email')
                    ->label('E-posta')
                    ->required()
                    ->email()
                    ->placeholder('admin@example.com')
                    ->helperText('Let\'s Encrypt için e-posta adresi'),
                Checkbox::make('redirect_https')
                    ->label('HTTP\'den HTTPS\'e Yönlendir')
                    ->default(true)
                    ->helperText('HTTP trafiğini otomatik olarak HTTPS\'e yönlendir'),
            ])
            ->action(function (array $data): void {
                $this->executeAction($data);
            });
    }

    private function executeAction(array $data): void
    {
        try {
            $result = app(EnableSslService::class)->execute(
                domain: $data['domain'],
                email: $data['email'],
                redirectHttps: (bool) ($data['redirect_https'] ?? true),
            );

            $status = $result['status'] ?? null;
            $message = $result['message'] ?? null;

            if ($status === 'success') {
                Notification::make()
                    ->title('SSL Etkinleştirildi')
                    ->body($message ?? "SSL sertifikası {$data['domain']} için başarıyla alındı")
                    ->success()
                    ->send();

                return;
            }

            if ($status === 'warning') {
                Notification::make()
                    ->title('SSL Etkinleştirme Uyarısı')
                    ->body($message ?? 'SSL etkinleştirilirken uyarılar oluştu')
                    ->warning()
                    ->send();

                return;
            }

            Notification::make()
                ->title('SSL Etkinleştirme Hatası')
                ->body($message ?? 'SSL etkinleştirilirken hata oluştu')
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('SSL Etkinleştirme Hatası')
                ->body('SSL etkinleştirilirken hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}