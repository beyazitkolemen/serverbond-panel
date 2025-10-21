<?php

declare(strict_types=1);

namespace App\Filament\Actions\Nginx;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;

class InstallAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'install';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Nginx Kurulumu')
            ->icon('heroicon-o-globe-alt')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Nginx Web Sunucu Kurulumu')
            ->modalDescription('Nginx kurulacak ve temel optimizasyonlar yapılacak.')
            ->modalSubmitActionLabel('Kur')
            ->action(function (): void {
                $this->executeScript();
            });
    }

    private function executeScript(): void
    {
        try {
            $scriptPath = config('serverbond-action.base_dir') . '/nginx/install.sh';
            
            $result = Process::timeout(config('serverbond-action.execution.timeout', 300))
                ->run("bash {$scriptPath}");

            if ($result->successful()) {
                $output = $result->output();
                $data = json_decode($output, true);

                Notification::make()
                    ->title('Nginx Kurulumu Başarılı')
                    ->body($data['message'] ?? 'Nginx başarıyla kuruldu ve yapılandırıldı')
                    ->success()
                    ->send();
            } else {
                throw new \Exception($result->errorOutput());
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Nginx Kurulum Hatası')
                ->body('Nginx kurulumu sırasında hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}