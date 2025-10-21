<?php

declare(strict_types=1);

namespace App\Filament\Actions\Nginx;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;

class StopAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'stop';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Nginx Durdur')
            ->icon('heroicon-o-stop')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Nginx Durdurma')
            ->modalDescription('Nginx servisi durdurulacak. Bu işlem web sitelerini erişilemez hale getirebilir.')
            ->modalSubmitActionLabel('Durdur')
            ->action(function (): void {
                $this->executeScript();
            });
    }

    private function executeScript(): void
    {
        try {
            $scriptPath = config('serverbond-action.base_dir') . '/nginx/stop.sh';
            
            $result = Process::timeout(config('serverbond-action.execution.timeout', 300))
                ->run("bash {$scriptPath}");

            if ($result->successful()) {
                $output = $result->output();
                $data = json_decode($output, true);

                Notification::make()
                    ->title('Nginx Durduruldu')
                    ->body($data['message'] ?? 'Nginx servisi başarıyla durduruldu')
                    ->warning()
                    ->send();
            } else {
                throw new \Exception($result->errorOutput());
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Nginx Durdurma Hatası')
                ->body('Nginx durdurulurken hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}