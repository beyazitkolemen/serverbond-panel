<?php

declare(strict_types=1);

namespace App\Filament\Actions\System;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;

class UfwConfigureAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'ufw_configure';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('UFW Firewall Kurulumu')
            ->icon('heroicon-o-shield-exclamation')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('UFW Firewall Yapılandırması')
            ->modalDescription('UFW firewall kurulacak ve temel kurallar (80/443/22) eklenecek.')
            ->modalSubmitActionLabel('Kur ve Yapılandır')
            ->action(function (): void {
                $this->executeScript();
            });
    }

    private function executeScript(): void
    {
        try {
            $scriptPath = config('serverbond-action.base_dir') . '/system/ufw_configure.sh';
            
            $result = Process::timeout(config('serverbond-action.execution.timeout', 300))
                ->run("bash {$scriptPath}");

            if ($result->successful()) {
                $output = $result->output();
                $data = json_decode($output, true);

                Notification::make()
                    ->title('UFW Firewall Kurulumu Başarılı')
                    ->body($data['message'] ?? 'UFW firewall başarıyla kuruldu ve yapılandırıldı')
                    ->success()
                    ->send();
            } else {
                throw new \Exception($result->errorOutput());
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('UFW Kurulum Hatası')
                ->body('UFW kurulumu sırasında hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}