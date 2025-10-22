<?php

declare(strict_types=1);

namespace App\Filament\Actions\System;

use App\Actions\System\HostnameService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Throwable;

class HostnameAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'hostname';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Hostname Yönetimi')
            ->icon('heroicon-o-computer-desktop')
            ->color('primary')
            ->form([
                Select::make('action')
                    ->label('İşlem')
                    ->options([
                        'view' => 'Görüntüle',
                        'set' => 'Değiştir',
                    ])
                    ->required()
                    ->live(),
                TextInput::make('hostname')
                    ->label('Yeni Hostname')
                    ->visible(fn (callable $get) => $get('action') === 'set')
                    ->required(fn (callable $get) => $get('action') === 'set')
                    ->placeholder('ornek-sunucu')
                    ->helperText('Sadece küçük harf, rakam ve tire kullanın'),
            ])
            ->action(function (array $data): void {
                $this->executeAction($data);
            });
    }

    private function executeAction(array $data): void
    {
        try {
            /** @var HostnameService $service */
            $service = app(HostnameService::class);
            $mode = $data['action'] ?? 'view';

            $result = $mode === 'set'
                ? $service->set($data['hostname'])
                : $service->view();

            $status = $result['status'] ?? null;
            $message = $result['message'] ?? null;
            $payload = $result['data'] ?? null;

            if ($status === 'success') {
                if ($mode === 'view' && is_array($payload) && isset($payload['hostname'])) {
                    $message = 'Mevcut hostname: ' . $payload['hostname'];
                }

                Notification::make()
                    ->title('Hostname İşlemi Başarılı')
                    ->body($message ?? 'Hostname işlemi tamamlandı')
                    ->success()
                    ->send();

                return;
            }

            if ($status === 'warning') {
                Notification::make()
                    ->title('Hostname İşlemi')
                    ->body($message ?? 'Hostname işlemi sırasında uyarılar oluştu')
                    ->warning()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Hostname Hatası')
                ->body($message ?? 'Hostname işlemi sırasında hata oluştu')
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Hostname Hatası')
                ->body('Hostname işlemi sırasında hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
