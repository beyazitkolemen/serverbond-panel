<?php

declare(strict_types=1);

namespace App\Filament\Actions\Nginx;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;

class ConfigTestAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'config_test';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Nginx Konfig Test')
            ->icon('heroicon-o-check-circle')
            ->color('info')
            ->action(function (): void {
                $this->executeScript();
            });
    }

    private function executeScript(): void
    {
        try {
            $scriptPath = config('serverbond-action.base_dir') . '/nginx/config_test.sh';
            
            $result = Process::timeout(config('serverbond-action.execution.timeout', 300))
                ->run("bash {$scriptPath}");

            if ($result->successful()) {
                $output = $result->output();
                $data = json_decode($output, true);

                Notification::make()
                    ->title('Nginx Konfig Test Başarılı')
                    ->body($data['message'] ?? 'Nginx konfigürasyonu geçerli')
                    ->success()
                    ->send();
            } else {
                $errorOutput = $result->errorOutput();
                Notification::make()
                    ->title('Nginx Konfig Test Hatası')
                    ->body('Konfigürasyon hatası: ' . $errorOutput)
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Nginx Konfig Test Hatası')
                ->body('Konfig test sırasında hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}