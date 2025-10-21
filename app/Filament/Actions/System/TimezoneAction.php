<?php

declare(strict_types=1);

namespace App\Filament\Actions\System;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;

class TimezoneAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'timezone';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Saat Dilimi Yönetimi')
            ->icon('heroicon-o-clock')
            ->color('primary')
            ->form([
                Select::make('action')
                    ->label('İşlem')
                    ->options([
                        'view' => 'Görüntüle',
                        'set' => 'Değiştir',
                    ])
                    ->required()
                    ->live(),
                TextInput::make('tz')
                    ->label('Saat Dilimi')
                    ->visible(fn (callable $get) => $get('action') === 'set')
                    ->required(fn (callable $get) => $get('action') === 'set')
                    ->placeholder('Europe/Istanbul')
                    ->helperText('Örnek: Europe/Istanbul, America/New_York, Asia/Tokyo'),
            ])
            ->action(function (array $data): void {
                $this->executeScript($data);
            });
    }

    private function executeScript(array $data): void
    {
        try {
            $scriptPath = config('serverbond-action.base_dir') . '/system/timezone.sh';
            
            $command = "bash {$scriptPath} --action={$data['action']}";
            if ($data['action'] === 'set' && !empty($data['tz'])) {
                $command .= " --tz={$data['tz']}";
            }
            
            $result = Process::timeout(config('serverbond-action.execution.timeout', 300))
                ->run($command);

            if ($result->successful()) {
                $output = $result->output();
                $response = json_decode($output, true);

                Notification::make()
                    ->title('Saat Dilimi İşlemi Başarılı')
                    ->body($response['message'] ?? 'Saat dilimi işlemi tamamlandı')
                    ->success()
                    ->send();
            } else {
                throw new \Exception($result->errorOutput());
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Saat Dilimi Hatası')
                ->body('Saat dilimi işlemi sırasında hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}