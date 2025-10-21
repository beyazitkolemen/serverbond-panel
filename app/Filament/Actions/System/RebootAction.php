<?php

declare(strict_types=1);

namespace App\Filament\Actions\System;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;

class RebootAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'reboot';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Sistemi Yeniden Başlat')
            ->icon('heroicon-o-arrow-path')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Sistem Yeniden Başlatma')
            ->modalDescription('Sunucuyu yeniden başlatacak. Bu işlem tüm servisleri durduracak ve sistem kapanacak.')
            ->modalSubmitActionLabel('Yeniden Başlat')
            ->action(function (): void {
                $this->executeScript();
            });
    }

    private function executeScript(): void
    {
        try {
            $scriptPath = config('serverbond-action.base_dir') . '/system/reboot.sh';
            
            $result = Process::timeout(30) // Kısa timeout çünkü sistem kapanacak
                ->run("bash {$scriptPath}");

            if ($result->successful()) {
                Notification::make()
                    ->title('Sistem Yeniden Başlatılıyor')
                    ->body('Sunucu yeniden başlatma komutu gönderildi')
                    ->warning()
                    ->send();
            } else {
                throw new \Exception($result->errorOutput());
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Yeniden Başlatma Hatası')
                ->body('Yeniden başlatma sırasında hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}