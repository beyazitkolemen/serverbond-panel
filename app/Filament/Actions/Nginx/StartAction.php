<?php

declare(strict_types=1);

namespace App\Filament\Actions\Nginx;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;

class StartAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'start';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Nginx Başlat')
            ->icon('heroicon-o-play')
            ->color('success')
            ->action(function (): void {
                $this->executeScript();
            });
    }

    private function executeScript(): void
    {
        try {
            $scriptPath = config('serverbond-action.base_dir') . '/nginx/start.sh';
            
            $result = Process::timeout(config('serverbond-action.execution.timeout', 300))
                ->run("bash {$scriptPath}");

            if ($result->successful()) {
                $output = $result->output();
                $data = json_decode($output, true);

                Notification::make()
                    ->title('Nginx Başlatıldı')
                    ->body($data['message'] ?? 'Nginx servisi başarıyla başlatıldı')
                    ->success()
                    ->send();
            } else {
                throw new \Exception($result->errorOutput());
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Nginx Başlatma Hatası')
                ->body('Nginx başlatılırken hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}