<?php

declare(strict_types=1);

namespace App\Filament\Actions\Nginx;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;

class RemoveSiteAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'remove_site';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Site Kaldır')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Site Kaldırma')
            ->modalDescription('Site kaldırılacak ve konfigürasyon dosyası silinecek.')
            ->form([
                TextInput::make('domain')
                    ->label('Domain')
                    ->required()
                    ->placeholder('example.com')
                    ->helperText('Kaldırılacak site domain adresi'),
            ])
            ->action(function (array $data): void {
                $this->executeScript($data);
            });
    }

    private function executeScript(array $data): void
    {
        try {
            $scriptPath = config('serverbond-action.base_dir') . '/nginx/remove_site.sh';
            
            $result = Process::timeout(config('serverbond-action.execution.timeout', 300))
                ->run("bash {$scriptPath} --domain={$data['domain']}");

            if ($result->successful()) {
                $output = $result->output();
                $response = json_decode($output, true);

                Notification::make()
                    ->title('Site Kaldırıldı')
                    ->body($response['message'] ?? "Site {$data['domain']} başarıyla kaldırıldı")
                    ->success()
                    ->send();
            } else {
                throw new \Exception($result->errorOutput());
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Site Kaldırma Hatası')
                ->body('Site kaldırılırken hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}