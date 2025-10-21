<?php

declare(strict_types=1);

namespace App\Filament\Actions\System;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;

class LogsAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'logs';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Sistem Logları')
            ->icon('heroicon-o-document-text')
            ->color('info')
            ->form([
                TextInput::make('lines')
                    ->label('Satır Sayısı')
                    ->numeric()
                    ->default(200)
                    ->minValue(1)
                    ->maxValue(10000)
                    ->helperText('Gösterilecek log satır sayısı (1-10000)'),
            ])
            ->action(function (array $data): void {
                $this->executeScript($data);
            });
    }

    private function executeScript(array $data): void
    {
        try {
            $scriptPath = config('serverbond-action.base_dir') . '/system/logs.sh';
            $lines = $data['lines'] ?? 200;
            
            $result = Process::timeout(config('serverbond-action.execution.timeout', 300))
                ->run("bash {$scriptPath} --lines={$lines}");

            if ($result->successful()) {
                $output = $result->output();
                $data = json_decode($output, true);

                Notification::make()
                    ->title('Sistem Logları')
                    ->body($data['message'] ?? 'Loglar başarıyla alındı')
                    ->success()
                    ->send();
            } else {
                throw new \Exception($result->errorOutput());
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Log Hatası')
                ->body('Loglar alınırken hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}