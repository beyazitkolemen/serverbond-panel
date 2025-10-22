<?php

declare(strict_types=1);

namespace App\Filament\Actions\Nginx;

use App\Actions\Nginx\ListSitesService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Throwable;

class ListSitesAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'list_sites';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Site Listesi')
            ->icon('heroicon-o-list-bullet')
            ->color('info')
            ->action(function (): void {
                $this->executeAction();
            });
    }

    private function executeAction(): void
    {
        try {
            $result = app(ListSitesService::class)->execute();

            $status = $result['status'] ?? null;
            $message = $result['message'] ?? null;
            $data = $result['data'] ?? null;

            if ($status === 'success') {
                if (is_array($data)) {
                    $totalSites = count($data);
                    $activeSites = count(array_filter(
                        $data,
                        static fn ($site): bool => is_array($site) && (($site['status'] ?? null) === 'active')
                    ));
                    $message = "Toplam {$totalSites} site, {$activeSites} aktif";
                }

                Notification::make()
                    ->title('Site Listesi')
                    ->body($message ?? 'Site listesi alındı')
                    ->success()
                    ->send();

                return;
            }

            if ($status === 'warning') {
                Notification::make()
                    ->title('Site Listesi')
                    ->body($message ?? 'Site listesi alınırken uyarılar oluştu')
                    ->warning()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Site Listesi Hatası')
                ->body($message ?? 'Site listesi alınamadı')
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Site Listesi Hatası')
                ->body('Site listesi alınırken hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}