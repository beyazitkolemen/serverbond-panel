<?php

declare(strict_types=1);

namespace App\Filament\Actions\Meta;

use App\Actions\Meta\CapabilitiesService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Throwable;

class CapabilitiesAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'capabilities';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Mevcut Yetenekler')
            ->icon('heroicon-o-list-bullet')
            ->color('info')
            ->action(function (): void {
                $this->executeAction();
            });
    }

    private function executeAction(): void
    {
        try {
            $result = app(CapabilitiesService::class)->execute();

            $status = $result['status'] ?? null;
            $message = $result['message'] ?? null;
            $data = $result['data'] ?? null;

            if ($status === 'success') {
                if (is_array($data)) {
                    $totalScripts = count($data);
                    $categories = array_unique(array_map(
                        static fn ($capability): string => is_array($capability) ? ($capability['category'] ?? 'genel') : 'genel',
                        $data
                    ));
                    $message = "Toplam {$totalScripts} script, " . count($categories) . " kategori mevcut";
                }

                Notification::make()
                    ->title('Agent Yetenekleri')
                    ->body($message ?? 'Yetenek listesi alındı')
                    ->success()
                    ->send();

                return;
            }

            if ($status === 'warning') {
                Notification::make()
                    ->title('Agent Yetenekleri')
                    ->body($message ?? 'Yetenek listesi alınırken uyarılar oluştu')
                    ->warning()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Yetenek Listesi Hatası')
                ->body($message ?? 'Yetenek listesi alınamadı')
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Yetenek Listesi Hatası')
                ->body('Yetenek listesi alınırken hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}