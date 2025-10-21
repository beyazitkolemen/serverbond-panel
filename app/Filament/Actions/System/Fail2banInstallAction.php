<?php

declare(strict_types=1);

namespace App\Filament\Actions\System;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;

class Fail2banInstallAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'fail2ban_install';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Fail2ban Kurulumu')
            ->icon('heroicon-o-shield-check')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Fail2ban Güvenlik Kurulumu')
            ->modalDescription('Fail2ban kurulacak ve SSH/Nginx jail\'leri etkinleştirilecek.')
            ->modalSubmitActionLabel('Kur ve Etkinleştir')
            ->action(function (): void {
                $this->executeScript();
            });
    }

    private function executeScript(): void
    {
        try {
            $scriptPath = config('serverbond-action.base_dir') . '/system/fail2ban_install.sh';
            
            $result = Process::timeout(config('serverbond-action.execution.timeout', 300))
                ->run("bash {$scriptPath}");

            if ($result->successful()) {
                $output = $result->output();
                $data = json_decode($output, true);

                Notification::make()
                    ->title('Fail2ban Kurulumu Başarılı')
                    ->body($data['message'] ?? 'Fail2ban başarıyla kuruldu ve yapılandırıldı')
                    ->success()
                    ->send();
            } else {
                throw new \Exception($result->errorOutput());
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Fail2ban Kurulum Hatası')
                ->body('Fail2ban kurulumu sırasında hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}