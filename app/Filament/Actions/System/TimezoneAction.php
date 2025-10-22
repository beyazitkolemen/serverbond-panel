<?php

declare(strict_types=1);

namespace App\Filament\Actions\System;

use App\Actions\System\TimezoneService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Throwable;

class TimezoneAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'timezone';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Saat Dilimi Yönetimi')
            ->icon('heroicon-o-clock')
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
                TextInput::make('tz')
                    ->label('Saat Dilimi')
                    ->visible(fn (callable $get) => $get('action') === 'set')
                    ->required(fn (callable $get) => $get('action') === 'set')
                    ->placeholder('Europe/Istanbul')
                    ->helperText('Örnek: Europe/Istanbul, America/New_York, Asia/Tokyo'),
            ])
            ->action(function (array $data): void {
                $this->executeAction($data);
            });
    }

    private function executeAction(array $data): void
    {
        try {
            /** @var TimezoneService $service */
            $service = app(TimezoneService::class);
            $mode = $data['action'] ?? 'view';

            $result = $mode === 'set'
                ? $service->set($data['tz'])
                : $service->view();

            $status = $result['status'] ?? null;
            $message = $result['message'] ?? null;
            $payload = $result['data'] ?? null;

            if ($status === 'success') {
                if ($mode === 'view' && is_array($payload) && isset($payload['timezone'])) {
                    $message = 'Mevcut saat dilimi: ' . $payload['timezone'];
                }

                Notification::make()
                    ->title('Saat Dilimi İşlemi Başarılı')
                    ->body($message ?? 'Saat dilimi işlemi tamamlandı')
                    ->success()
                    ->send();

                return;
            }

            if ($status === 'warning') {
                Notification::make()
                    ->title('Saat Dilimi İşlemi')
                    ->body($message ?? 'Saat dilimi işlemi sırasında uyarılar oluştu')
                    ->warning()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Saat Dilimi Hatası')
                ->body($message ?? 'Saat dilimi işlemi sırasında hata oluştu')
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Saat Dilimi Hatası')
                ->body('Saat dilimi işlemi sırasında hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
