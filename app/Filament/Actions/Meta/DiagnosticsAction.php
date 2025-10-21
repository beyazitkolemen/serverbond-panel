<?php

declare(strict_types=1);

namespace App\Filament\Actions\Meta;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;

class DiagnosticsAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'diagnostics';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Hızlı Tanılama')
            ->icon('heroicon-o-wrench-screwdriver')
            ->color('warning')
            ->action(function (): void {
                $this->executeScript();
            });
    }

    private function executeScript(): void
    {
        try {
            $scriptPath = config('serverbond-action.base_dir') . '/meta/diagnostics.sh';
            
            $result = Process::timeout(config('serverbond-action.execution.timeout', 300))
                ->run("bash {$scriptPath}");

            if ($result->successful()) {
                $output = $result->output();
                $data = json_decode($output, true);

                if ($data && isset($data['data'])) {
                    $diagnostics = $data['data'];
                    $message = "Nginx: " . ($diagnostics['nginx'] ?? 'Bilinmiyor') . 
                              " | PHP: " . ($diagnostics['php'] ?? 'Bilinmiyor') . 
                              " | MySQL: " . ($diagnostics['mysql'] ?? 'Bilinmiyor') . 
                              " | Redis: " . ($diagnostics['redis'] ?? 'Bilinmiyor');
                    
                    Notification::make()
                        ->title('Sistem Tanılaması')
                        ->body($message)
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Sistem Tanılaması')
                        ->body($data['message'] ?? 'Tanılama tamamlandı')
                        ->info()
                        ->send();
                }
            } else {
                throw new \Exception($result->errorOutput());
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Tanılama Hatası')
                ->body('Tanılama sırasında hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}