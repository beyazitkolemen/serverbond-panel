<?php

declare(strict_types=1);

namespace App\Filament\Actions\Nginx;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;

class RestartAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'restart';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Nginx Yeniden Başlat')
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Nginx Yeniden Başlatma')
            ->modalDescription('Nginx servisi yeniden başlatılacak. Kısa bir süre web siteleri erişilemez olabilir.')
            ->modalSubmitActionLabel('Yeniden Başlat')
            ->action(function (): void {
                $this->executeScript();
            });
    }

    private function executeScript(): void
    {
        try {
            $scriptPath = config('serverbond-action.base_dir') . '/nginx/restart.sh';
            
            $result = Process::timeout(config('serverbond-action.execution.timeout', 300))
                ->run("bash {$scriptPath}");

            if ($result->successful()) {
                $output = $result->output();
                $data = json_decode($output, true);

                Notification::make()
                    ->title('Nginx Yeniden Başlatıldı')
                    ->body($data['message'] ?? 'Nginx servisi başarıyla yeniden başlatıldı')
                    ->success()
                    ->send();
            } else {
                throw new \Exception($result->errorOutput());
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Nginx Yeniden Başlatma Hatası')
                ->body('Nginx yeniden başlatılırken hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}