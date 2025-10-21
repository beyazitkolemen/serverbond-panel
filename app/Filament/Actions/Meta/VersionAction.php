<?php

declare(strict_types=1);

namespace App\Filament\Actions\Meta;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;

class VersionAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'version';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Agent Sürüm Bilgisi')
            ->icon('heroicon-o-information-circle')
            ->color('info')
            ->action(function (): void {
                $this->executeScript();
            });
    }

    private function executeScript(): void
    {
        try {
            $scriptPath = config('serverbond-action.base_dir') . '/meta/version.sh';
            
            $result = Process::timeout(config('serverbond-action.execution.timeout', 300))
                ->run("bash {$scriptPath}");

            if ($result->successful()) {
                $output = $result->output();
                $data = json_decode($output, true);

                if ($data && isset($data['data'])) {
                    $version = $data['data'];
                    $message = "Sürüm: {$version['version']} | Build: {$version['build']} | Tarih: {$version['date']}";
                    
                    Notification::make()
                        ->title('Agent Sürüm Bilgisi')
                        ->body($message)
                        ->info()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Agent Sürüm Bilgisi')
                        ->body($data['message'] ?? 'Sürüm bilgisi alındı')
                        ->info()
                        ->send();
                }
            } else {
                throw new \Exception($result->errorOutput());
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Sürüm Bilgisi Hatası')
                ->body('Sürüm bilgisi alınırken hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}