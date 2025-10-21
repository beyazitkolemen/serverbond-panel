<?php

declare(strict_types=1);

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use App\Services\SettingService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSettings extends ListRecords
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clearCache')
                ->label('Cache Temizle')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function (SettingService $settingService) {
                    $settingService->clearCache();

                    Notification::make()
                        ->title('Cache temizlendi')
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}

