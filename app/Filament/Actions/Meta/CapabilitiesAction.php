<?php

declare(strict_types=1);

namespace App\Filament\Actions\Meta;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;

class CapabilitiesAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'capabilities';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Mevcut Yetenekler')
            ->icon('heroicon-o-list-bullet')
            ->color('info')
            ->action(function (): void {
                $this->executeScript();
            });
    }

    private function executeScript(): void
    {
        try {
            $scriptPath = config('serverbond-action.base_dir') . '/meta/capabilities.sh';
            
            $result = Process::timeout(config('serverbond-action.execution.timeout', 300))
                ->run("bash {$scriptPath}");

            if ($result->successful()) {
                $output = $result->output();
                $data = json_decode($output, true);

                if ($data && isset($data['data']) && is_array($data['data'])) {
                    $capabilities = $data['data'];
                    $totalScripts = count($capabilities);
                    $categories = array_unique(array_column($capabilities, 'category'));
                    $message = "Toplam {$totalScripts} script, " . count($categories) . " kategori mevcut";
                    
                    Notification::make()
                        ->title('Agent Yetenekleri')
                        ->body($message)
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Agent Yetenekleri')
                        ->body($data['message'] ?? 'Yetenek listesi alındı')
                        ->info()
                        ->send();
                }
            } else {
                throw new \Exception($result->errorOutput());
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Yetenek Listesi Hatası')
                ->body('Yetenek listesi alınırken hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}