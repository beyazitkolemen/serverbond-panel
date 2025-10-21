<?php

declare(strict_types=1);

namespace App\Filament\Actions\System;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;

class HostnameAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'hostname';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Hostname Yönetimi')
            ->icon('heroicon-o-computer-desktop')
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
                TextInput::make('hostname')
                    ->label('Yeni Hostname')
                    ->visible(fn (callable $get) => $get('action') === 'set')
                    ->required(fn (callable $get) => $get('action') === 'set')
                    ->placeholder('ornek-sunucu')
                    ->helperText('Sadece küçük harf, rakam ve tire kullanın'),
            ])
            ->action(function (array $data): void {
                $this->executeScript($data);
            });
    }

    private function executeScript(array $data): void
    {
        try {
            $scriptPath = config('serverbond-action.base_dir') . '/system/hostname.sh';
            
            $command = "bash {$scriptPath} --action={$data['action']}";
            if ($data['action'] === 'set' && !empty($data['hostname'])) {
                $command .= " --hostname={$data['hostname']}";
            }
            
            $result = Process::timeout(config('serverbond-action.execution.timeout', 300))
                ->run($command);

            if ($result->successful()) {
                $output = $result->output();
                $response = json_decode($output, true);

                Notification::make()
                    ->title('Hostname İşlemi Başarılı')
                    ->body($response['message'] ?? 'Hostname işlemi tamamlandı')
                    ->success()
                    ->send();
            } else {
                throw new \Exception($result->errorOutput());
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Hostname Hatası')
                ->body('Hostname işlemi sırasında hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}