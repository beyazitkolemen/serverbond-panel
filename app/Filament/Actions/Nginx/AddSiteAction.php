<?php

declare(strict_types=1);

namespace App\Filament\Actions\Nginx;

use App\Actions\Nginx\AddSiteService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Throwable;

class AddSiteAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'add_site';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Site Ekle')
            ->icon('heroicon-o-plus')
            ->color('success')
            ->form([
                TextInput::make('domain')
                    ->label('Domain')
                    ->required()
                    ->placeholder('example.com')
                    ->helperText('Site domain adresi'),
                Select::make('type')
                    ->label('Site Tipi')
                    ->options([
                        'laravel' => 'Laravel',
                        'node' => 'Node.js',
                        'static' => 'Static',
                        'wordpress' => 'WordPress',
                        'proxy' => 'Proxy',
                    ])
                    ->required()
                    ->live(),
                TextInput::make('root')
                    ->label('Root Path')
                    ->required()
                    ->placeholder('/var/www/example.com')
                    ->helperText('Site dosyalarının bulunduğu dizin'),
                TextInput::make('php_version')
                    ->label('PHP Sürümü')
                    ->placeholder('8.3')
                    ->visible(fn (callable $get) => in_array($get('type'), ['laravel', 'wordpress']))
                    ->helperText('Laravel/WordPress için PHP sürümü'),
                TextInput::make('upstream_port')
                    ->label('Upstream Port')
                    ->numeric()
                    ->visible(fn (callable $get) => in_array($get('type'), ['node', 'proxy']))
                    ->helperText('Node.js/Proxy için port numarası'),
                TextInput::make('server_alias')
                    ->label('Server Alias')
                    ->placeholder('www.example.com')
                    ->helperText('Alternatif domain adları (virgülle ayırın)'),
            ])
            ->action(function (array $data): void {
                $this->executeAction($data);
            });
    }

    private function executeAction(array $data): void
    {
        try {
            $service = app(AddSiteService::class);
            $phpVersion = $data['php_version'] ?? null;
            $upstreamPort = $data['upstream_port'] ?? null;
            $serverAlias = $data['server_alias'] ?? null;

            $result = $service->execute(
                domain: $data['domain'],
                type: $data['type'],
                root: $data['root'],
                phpVersion: $phpVersion !== null && $phpVersion !== '' ? $phpVersion : null,
                upstreamPort: $upstreamPort !== null && $upstreamPort !== '' ? (int) $upstreamPort : null,
                serverAlias: $serverAlias !== null && $serverAlias !== '' ? $serverAlias : null,
            );

            $status = $result['status'] ?? null;
            $message = $result['message'] ?? null;

            if ($status === 'success') {
                Notification::make()
                    ->title('Site Eklendi')
                    ->body($message ?? "Site {$data['domain']} başarıyla eklendi")
                    ->success()
                    ->send();

                return;
            }

            if ($status === 'warning') {
                Notification::make()
                    ->title('Site Ekleme Uyarısı')
                    ->body($message ?? 'Site eklenirken uyarılar oluştu')
                    ->warning()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Site Ekleme Hatası')
                ->body($message ?? 'Site eklenirken hata oluştu')
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Site Ekleme Hatası')
                ->body('Site eklenirken hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}