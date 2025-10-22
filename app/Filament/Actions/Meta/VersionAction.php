<?php

declare(strict_types=1);

namespace App\Filament\Actions\Meta;

use App\Actions\Meta\VersionService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Throwable;

class VersionAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'version';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Agent Sürüm Bilgisi')
            ->icon('heroicon-o-information-circle')
            ->color('info')
            ->action(function (): void {
                $this->executeAction();
            });
    }

    private function executeAction(): void
    {
        try {
            $result = app(VersionService::class)->execute();

            $status = $result['status'] ?? null;
            $message = $result['message'] ?? null;
            $data = $result['data'] ?? null;

            if ($status === 'success') {
                if (is_array($data)) {
                    $message = 'Sürüm: ' . ($data['version'] ?? 'Bilinmiyor')
                        . ' | Build: ' . ($data['build'] ?? 'Bilinmiyor')
                        . ' | Tarih: ' . ($data['date'] ?? 'Bilinmiyor');
                }

                Notification::make()
                    ->title('Agent Sürüm Bilgisi')
                    ->body($message ?? 'Sürüm bilgisi alındı')
                    ->info()
                    ->send();

                return;
            }

            if ($status === 'warning') {
                Notification::make()
                    ->title('Agent Sürüm Bilgisi')
                    ->body($message ?? 'Sürüm bilgisi alınırken uyarılar oluştu')
                    ->warning()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Sürüm Bilgisi Hatası')
                ->body($message ?? 'Sürüm bilgisi alınamadı')
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Sürüm Bilgisi Hatası')
                ->body('Sürüm bilgisi alınırken hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}