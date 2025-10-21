<?php

declare(strict_types=1);

namespace App\Filament\Actions\Nginx;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;

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
                $this->executeScript($data);
            });
    }

    private function executeScript(array $data): void
    {
        try {
            $scriptPath = config('serverbond-action.base_dir') . '/nginx/add_site.sh';
            
            $command = "bash {$scriptPath} --domain={$data['domain']} --type={$data['type']} --root={$data['root']}";
            
            if (!empty($data['php_version'])) {
                $command .= " --php_version={$data['php_version']}";
            }
            
            if (!empty($data['upstream_port'])) {
                $command .= " --upstream_port={$data['upstream_port']}";
            }
            
            if (!empty($data['server_alias'])) {
                $command .= " --server_alias={$data['server_alias']}";
            }
            
            $result = Process::timeout(config('serverbond-action.execution.timeout', 300))
                ->run($command);

            if ($result->successful()) {
                $output = $result->output();
                $response = json_decode($output, true);

                Notification::make()
                    ->title('Site Eklendi')
                    ->body($response['message'] ?? "Site {$data['domain']} başarıyla eklendi")
                    ->success()
                    ->send();
            } else {
                throw new \Exception($result->errorOutput());
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Site Ekleme Hatası')
                ->body('Site eklenirken hata oluştu: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}