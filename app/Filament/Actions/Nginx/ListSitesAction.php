<?php

declare(strict_types=1);

namespace App\Filament\Actions\Nginx;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;

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
                $this->executeScript();
            });
    }

    private function executeScript(): void
    {
        try {
            $scriptPath = config('serverbond-action.base_dir') . '/nginx/list_sites.sh';
            
            $result = Process::timeout(config('serverbond-action.execution.timeout', 300))
                ->run("bash {$scriptPath}");

            if ($result->successful()) {
                $output = $result->output();
                $data = json_decode($output, true);

                if ($data && isset($data['data']) && is_array($data['data'])) {
                    $sites = $data['data'];
                    $totalSites = count($sites);
                    $activeSites = count(array_filter($sites, fn($site) => $site['status'] === 'active'));
                    $message = "Toplam {$totalSites} site, {$activeSites} aktif";
                    
                    Notification::make()
                        ->title('Site Listesi')
                        ->body($message)
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Site Listesi')
                        ->body($data['message'] ?? 'Site listesi alındı')
                        ->info()
                        ->send();
                }
            } else {
                throw new \Exception($result->errorOutput());
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Site Listesi Hatası')
                ->body('Site listesi alınırken hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}