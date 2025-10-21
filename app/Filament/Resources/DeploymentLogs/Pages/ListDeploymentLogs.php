<?php

declare(strict_types=1);

namespace App\Filament\Resources\DeploymentLogs\Pages;

use App\Filament\Resources\DeploymentLogs\DeploymentLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeploymentLogs extends ListRecords
{
    protected static string $resource = DeploymentLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('clear_old_logs')
                ->label('Eski Logları Temizle')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Eski Logları Temizle')
                ->modalDescription('30 günden eski logları silmek istediğinize emin misiniz?')
                ->action(function () {
                    $deleted = \App\Models\DeploymentLog::where('created_at', '<', now()->subDays(30))->delete();

                    \Filament\Notifications\Notification::make()
                        ->title('Loglar temizlendi')
                        ->body("{$deleted} adet log silindi.")
                        ->success()
                        ->send();
                }),
        ];
    }
}

