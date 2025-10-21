<?php

declare(strict_types=1);

namespace App\Filament\Actions\Nginx;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;

class EnableSslAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'enable_ssl';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('SSL Etkinleştir')
            ->icon('heroicon-o-shield-check')
            ->color('success')
            ->form([
                TextInput::make('domain')
                    ->label('Domain')
                    ->required()
                    ->placeholder('example.com')
                    ->helperText('SSL sertifikası alınacak domain'),
                TextInput::make('email')
                    ->label('E-posta')
                    ->required()
                    ->email()
                    ->placeholder('admin@example.com')
                    ->helperText('Let\'s Encrypt için e-posta adresi'),
                Checkbox::make('redirect_https')
                    ->label('HTTP\'den HTTPS\'e Yönlendir')
                    ->default(true)
                    ->helperText('HTTP trafiğini otomatik olarak HTTPS\'e yönlendir'),
            ])
            ->action(function (array $data): void {
                $this->executeScript($data);
            });
    }

    private function executeScript(array $data): void
    {
        try {
            $scriptPath = config('serverbond-action.base_dir') . '/nginx/enable_ssl.sh';
            
            $command = "bash {$scriptPath} --domain={$data['domain']} --email={$data['email']}";
            
            if ($data['redirect_https']) {
                $command .= " --redirect_https=true";
            }
            
            $result = Process::timeout(config('serverbond-action.execution.timeout', 300))
                ->run($command);

            if ($result->successful()) {
                $output = $result->output();
                $response = json_decode($output, true);

                Notification::make()
                    ->title('SSL Etkinleştirildi')
                    ->body($response['message'] ?? "SSL sertifikası {$data['domain']} için başarıyla alındı")
                    ->success()
                    ->send();
            } else {
                throw new \Exception($result->errorOutput());
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('SSL Etkinleştirme Hatası')
                ->body('SSL etkinleştirilirken hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}