<?php

declare(strict_types=1);

namespace App\Filament\Actions\Nginx;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;

class ReloadAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'reload';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Nginx Konfig Reload')
            ->icon('heroicon-o-arrow-path')
            ->color('info')
            ->action(function (): void {
                $this->executeScript();
            });
    }

    private function executeScript(): void
    {
        try {
            $scriptPath = config('serverbond-action.base_dir') . '/nginx/reload.sh';
            
            $result = Process::timeout(config('serverbond-action.execution.timeout', 300))
                ->run("bash {$scriptPath}");

            if ($result->successful()) {
                $output = $result->output();
                $data = json_decode($output, true);

                Notification::make()
                    ->title('Nginx Konfig Reload Başarılı')
                    ->body($data['message'] ?? 'Nginx konfigürasyonu başarıyla yeniden yüklendi')
                    ->success()
                    ->send();
            } else {
                throw new \Exception($result->errorOutput());
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Nginx Reload Hatası')
                ->body('Nginx konfig reload sırasında hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}