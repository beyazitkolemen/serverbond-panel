<?php

declare(strict_types=1);

namespace App\Filament\Actions\System;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;

class UpdateOsAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'update_os';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Sistem Güncelle')
            ->icon('heroicon-o-arrow-path')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Sistem Paketlerini Güncelle')
            ->modalDescription('Apt paketlerini güncelleyecek ve yükseltecek. Bu işlem sistem yeniden başlatması gerektirebilir.')
            ->modalSubmitActionLabel('Güncelle')
            ->action(function (): void {
                $this->executeScript();
            });
    }

    private function executeScript(): void
    {
        try {
            $scriptPath = config('serverbond-action.base_dir') . '/system/update_os.sh';
            
            $result = Process::timeout(config('serverbond-action.execution.timeout', 300))
                ->run("bash {$scriptPath}");

            if ($result->successful()) {
                $output = $result->output();
                $data = json_decode($output, true);

                Notification::make()
                    ->title('Sistem Güncelleme Başarılı')
                    ->body($data['message'] ?? 'Paketler başarıyla güncellendi')
                    ->success()
                    ->send();
            } else {
                throw new \Exception($result->errorOutput());
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Sistem Güncelleme Hatası')
                ->body('Güncelleme sırasında hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}