<?php

declare(strict_types=1);

namespace App\Filament\Actions\Meta;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;

class UpdateAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'update';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Agent Güncelle')
            ->icon('heroicon-o-arrow-path')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Agent Güncelleme')
            ->modalDescription('Agent kendini güncelleyecek (git pull + service restart). Bu işlem sırasında agent geçici olarak kullanılamayabilir.')
            ->modalSubmitActionLabel('Güncelle')
            ->action(function (): void {
                $this->executeScript();
            });
    }

    private function executeScript(): void
    {
        try {
            $scriptPath = config('serverbond-action.base_dir') . '/meta/update.sh';
            
            $result = Process::timeout(config('serverbond-action.execution.timeout', 300))
                ->run("bash {$scriptPath}");

            if ($result->successful()) {
                $output = $result->output();
                $data = json_decode($output, true);

                Notification::make()
                    ->title('Agent Güncelleme Başarılı')
                    ->body($data['message'] ?? 'Agent başarıyla güncellendi')
                    ->success()
                    ->send();
            } else {
                throw new \Exception($result->errorOutput());
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Agent Güncelleme Hatası')
                ->body('Güncelleme sırasında hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}