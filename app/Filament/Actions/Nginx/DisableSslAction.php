<?php

declare(strict_types=1);

namespace App\Filament\Actions\Nginx;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;

class DisableSslAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'disable_ssl';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('SSL Devre Dışı Bırak')
            ->icon('heroicon-o-shield-exclamation')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('SSL Devre Dışı Bırakma')
            ->modalDescription('SSL sertifikası kaldırılacak ve site HTTP-only moduna geçecek.')
            ->form([
                TextInput::make('domain')
                    ->label('Domain')
                    ->required()
                    ->placeholder('example.com')
                    ->helperText('SSL\'i kaldırılacak domain'),
            ])
            ->action(function (array $data): void {
                $this->executeScript($data);
            });
    }

    private function executeScript(array $data): void
    {
        try {
            $scriptPath = config('serverbond-action.base_dir') . '/nginx/disable_ssl.sh';
            
            $result = Process::timeout(config('serverbond-action.execution.timeout', 300))
                ->run("bash {$scriptPath} --domain={$data['domain']}");

            if ($result->successful()) {
                $output = $result->output();
                $response = json_decode($output, true);

                Notification::make()
                    ->title('SSL Devre Dışı Bırakıldı')
                    ->body($response['message'] ?? "SSL {$data['domain']} için devre dışı bırakıldı")
                    ->warning()
                    ->send();
            } else {
                throw new \Exception($result->errorOutput());
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('SSL Devre Dışı Bırakma Hatası')
                ->body('SSL devre dışı bırakılırken hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}