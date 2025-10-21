<?php

declare(strict_types=1);

namespace App\Filament\Actions\Meta;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;

class HealthAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'health';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Agent Sağlık Kontrolü')
            ->icon('heroicon-o-heart')
            ->color('success')
            ->action(function (): void {
                $this->executeScript();
            });
    }

    private function executeScript(): void
    {
        try {
            $scriptPath = config('serverbond-action.base_dir') . '/meta/health.sh';
            
            $result = Process::timeout(config('serverbond-action.execution.timeout', 300))
                ->run("bash {$scriptPath}");

            if ($result->successful()) {
                $output = $result->output();
                $data = json_decode($output, true);

                if ($data && isset($data['status']) && $data['status'] === 'success') {
                    Notification::make()
                        ->title('Agent Sağlıklı')
                        ->body($data['message'] ?? 'Tüm sistemler normal çalışıyor')
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Agent Sağlık Uyarısı')
                        ->body($data['message'] ?? 'Bazı sistemlerde sorun tespit edildi')
                        ->warning()
                        ->send();
                }
            } else {
                throw new \Exception($result->errorOutput());
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Sağlık Kontrolü Hatası')
                ->body('Sağlık kontrolü sırasında hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}