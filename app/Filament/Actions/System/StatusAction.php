<?php

declare(strict_types=1);

namespace App\Filament\Actions\System;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;

class StatusAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'status';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Sistem Durumu')
            ->icon('heroicon-o-chart-bar')
            ->color('info')
            ->action(function (): void {
                $this->executeScript();
            });
    }

    private function executeScript(): void
    {
        try {
            $scriptPath = config('serverbond-action.base_dir') . '/system/status.sh';
            
            $result = Process::timeout(config('serverbond-action.execution.timeout', 300))
                ->run("bash {$scriptPath}");

            if ($result->successful()) {
                $output = $result->output();
                $data = json_decode($output, true);

                if ($data && isset($data['data'])) {
                    $status = $data['data'];
                    $message = "CPU: {$status['cpu_usage']}% | RAM: {$status['memory_usage']}% | Disk: {$status['disk_usage']}% | Load: {$status['load_average']}";
                    
                    Notification::make()
                        ->title('Sistem Durumu')
                        ->body($message)
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Sistem Durumu')
                        ->body($data['message'] ?? 'Sistem durumu alındı')
                        ->info()
                        ->send();
                }
            } else {
                throw new \Exception($result->errorOutput());
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Sistem Durumu Hatası')
                ->body('Durum bilgisi alınırken hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}