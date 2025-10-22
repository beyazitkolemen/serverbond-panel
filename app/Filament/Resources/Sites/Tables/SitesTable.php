<?php

declare(strict_types=1);

namespace App\Filament\Resources\Sites\Tables;

use App\Enums\DeploymentTrigger;
use App\Enums\SiteStatus;
use App\Enums\SiteType;
use App\Actions\Nginx\AddSiteService;
use App\Actions\Nginx\ConfigTestService as NginxConfigTestService;
use App\Actions\Nginx\ReloadService as NginxReloadService;
use App\Models\Site;
use App\Services\CloudflareService;
use App\Services\DeploymentService;
use App\Services\RedisService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Throwable;

class SitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Site Adı')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon(fn ($record) => $record->type->icon())
                    ->url(fn ($record) => "https://{$record->domain}", shouldOpenInNewTab: true),

                TextColumn::make('domain')
                    ->label('Alan Adı')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Alan adı kopyalandı!')
                    ->icon('heroicon-o-globe-alt'),

                BadgeColumn::make('type')
                    ->label('Tip')
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->colors([
                        'primary' => SiteType::Laravel,
                        'success' => SiteType::PHP,
                        'info' => SiteType::Static,
                        'warning' => SiteType::NodeJS,
                        'danger' => SiteType::Python,
                    ]),

                BadgeColumn::make('status')
                    ->label('Durum')
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->colors([
                        'success' => SiteStatus::Active,
                        'gray' => SiteStatus::Inactive,
                        'warning' => SiteStatus::Deploying,
                        'danger' => SiteStatus::Error,
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => SiteStatus::Active,
                        'heroicon-o-pause-circle' => SiteStatus::Inactive,
                        'heroicon-o-arrow-path' => SiteStatus::Deploying,
                        'heroicon-o-x-circle' => SiteStatus::Error,
                    ]),

                TextColumn::make('php_version')
                    ->label('PHP')
                    ->badge()
                    ->color('info')
                    ->default('-')
                    ->toggleable(),

                IconColumn::make('ssl_enabled')
                    ->label('SSL')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('success')
                    ->falseColor('gray'),

                IconColumn::make('auto_deploy')
                    ->label('Auto Deploy')
                    ->boolean()
                    ->trueIcon('heroicon-o-bolt')
                    ->falseIcon('heroicon-o-bolt-slash')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->toggleable(),

                IconColumn::make('cloudflare_tunnel_enabled')
                    ->label('CF Tunnel')
                    ->boolean()
                    ->trueIcon('heroicon-o-cloud')
                    ->falseIcon('heroicon-o-cloud-arrow-down')
                    ->trueColor('info')
                    ->falseColor('gray')
                    ->toggleable(),

                TextColumn::make('last_deployed_at')
                    ->label('Son Deploy')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('-')
                    ->icon('heroicon-o-rocket-launch')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Site Tipi')
                    ->options(SiteType::class)
                    ->multiple(),

                SelectFilter::make('status')
                    ->label('Durum')
                    ->options(SiteStatus::class)
                    ->multiple(),

                TrashedFilter::make()
                    ->label('Silinmiş Kayıtlar'),
            ])
            ->recordActions([
                Action::make('deploy')
                    ->label('Deploy')
                    ->icon('heroicon-o-rocket-launch')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Site Deploy Edilsin mi?')
                    ->modalDescription(fn ($record) => "'{$record->name}' sitesi deploy edilecek. Bu işlem birkaç dakika sürebilir.")
                    ->modalSubmitActionLabel('Deploy Et')
                    ->action(function ($record) {
                        $deploymentService = app(DeploymentService::class);

                        try {
                            $deployment = $deploymentService->deploy(
                                $record,
                                DeploymentTrigger::Manual,
                                auth()->id()
                            );

                            Notification::make()
                                ->title('Deployment Başlatıldı')
                                ->success()
                                ->body("'{$record->name}' deployment süreci başladı.")
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Deployment Hatası')
                                ->danger()
                                ->body($e->getMessage())
                                ->send();
                        }
                    })
                    ->visible(fn ($record) => $record->git_repository),

                Action::make('configureCloudflareTunnel')
                    ->label('Cloudflare Tunnel')
                    ->icon('heroicon-o-cloud')
                    ->color('info')
                    ->modalHeading('Cloudflare Tunnel Aktifleştir')
                    ->modalDescription('Cloudflare Dashboard\'dan aldığınız tunnel token\'ı girerek tunnel\'ı hemen aktifleştirebilirsiniz.')
                    ->modalSubmitActionLabel('Aktifleştir')
                    ->form([
                        Textarea::make('cloudflare_tunnel_token')
                            ->label('Tunnel Token')
                            ->placeholder('eyJhIjoiXXXXX...')
                            ->rows(4)
                            ->required()
                            ->helperText('Cloudflare Dashboard > Zero Trust > Networks > Tunnels bölümünden token alabilirsiniz.')
                            ->default(fn ($record) => $record->cloudflare_tunnel_token),
                    ])
                    ->action(function ($record, array $data) {
                        $cloudflareService = app(CloudflareService::class);

                        try {
                            // Token'ı kaydet
                            $record->update([
                                'cloudflare_tunnel_token' => $data['cloudflare_tunnel_token'],
                                'cloudflare_tunnel_enabled' => true,
                            ]);

                            // Tunnel'ı başlat
                            $result = $cloudflareService->runTunnelWithToken($record);

                            if ($result['success']) {
                                Notification::make()
                                    ->title('Tunnel Aktifleştirildi')
                                    ->success()
                                    ->body("'{$record->name}' için Cloudflare Tunnel başarıyla başlatıldı.")
                                    ->send();
                            } else {
                                throw new \Exception($result['error'] ?? 'Tunnel başlatılamadı');
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Tunnel Hatası')
                                ->danger()
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

                Action::make('stopCloudflareTunnel')
                    ->label('Tunnel Durdur')
                    ->icon('heroicon-o-stop-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cloudflare Tunnel Durdurulsun mu?')
                    ->modalDescription(fn ($record) => "'{$record->name}' için Cloudflare Tunnel durdurulacak.")
                    ->modalSubmitActionLabel('Durdur')
                    ->action(function ($record) {
                        $cloudflareService = app(CloudflareService::class);

                        try {
                            $result = $cloudflareService->stopTunnel($record);

                            if ($result['success']) {
                                $record->update(['cloudflare_tunnel_enabled' => false]);

                                Notification::make()
                                    ->title('Tunnel Durduruldu')
                                    ->success()
                                    ->body("'{$record->name}' için Cloudflare Tunnel durduruldu.")
                                    ->send();
                            } else {
                                throw new \Exception($result['error'] ?? 'Tunnel durdurulamadı');
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Tunnel Hatası')
                                ->danger()
                                ->body($e->getMessage())
                                ->send();
                        }
                    })
                    ->visible(fn ($record) => $record->cloudflare_tunnel_enabled),

                Action::make('configureNginx')
                    ->label('Nginx Yapılandır')
                    ->icon('heroicon-o-cog')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Nginx Yapılandırılsın mı?')
                    ->modalDescription(fn ($record) => "'{$record->domain}' için Nginx config oluşturulup aktifleştirilecek.")
                    ->action(function ($record) {
                        $addSiteService = app(AddSiteService::class);
                        $configTestService = app(NginxConfigTestService::class);
                        $reloadService = app(NginxReloadService::class);

                        try {
                            $siteType = $record->type;
                            $result = $addSiteService->execute(
                                domain: $record->domain,
                                type: self::mapSiteTypeForNginx($siteType),
                                root: self::resolveDocumentRoot($record),
                                phpVersion: self::resolvePhpVersion($record),
                                upstreamPort: self::resolveUpstreamPort($siteType),
                            );

                            if (($result['status'] ?? null) !== 'success') {
                                Notification::make()
                                    ->title('Nginx Yapılandırma Hatası')
                                    ->danger()
                                    ->body($result['message'] ?? 'Site için konfigürasyon oluşturulamadı.')
                                    ->send();

                                return;
                            }

                            $testResult = $configTestService->execute();
                            if (($testResult['status'] ?? null) !== 'success') {
                                Notification::make()
                                    ->title('Nginx Test Hatası')
                                    ->danger()
                                    ->body($testResult['message'] ?? 'nginx -t başarısız oldu. Config dosyasını kontrol edin.')
                                    ->persistent()
                                    ->send();

                                return;
                            }

                            $reloadResult = $reloadService->execute();
                            if (($reloadResult['status'] ?? null) !== 'success') {
                                Notification::make()
                                    ->title('Nginx Yeniden Yüklenemedi')
                                    ->danger()
                                    ->body($reloadResult['message'] ?? 'systemctl reload nginx başarısız oldu.')
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->title('Nginx Yapılandırıldı')
                                ->success()
                                ->body("'{$record->domain}' nginx konfigürasyonu oluşturuldu.")
                                ->send();
                        } catch (Throwable $e) {
                            Notification::make()
                                ->title('Nginx Hatası')
                                ->danger()
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

                Action::make('clearRedisCache')
                    ->label('Redis Temizle')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Redis Önbelleğini Temizle')
                    ->modalDescription(fn ($record) => "'{$record->domain}' için Redis anahtarları temizlenecek. Emin misiniz?")
                    ->action(function ($record) {
                        $redisService = app(RedisService::class);

                        try {
                            $result = $redisService->clearSiteCache($record);

                            if ($result['success']) {
                                $deleted = $result['deleted'] ?? 0;

                                Notification::make()
                                    ->title('Redis Temizlendi')
                                    ->success()
                                    ->body("{$deleted} anahtar silindi ({$result['pattern']}).")
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Redis Hatası')
                                    ->danger()
                                    ->body($result['error'] ?? 'Redis anahtarları temizlenemedi.')
                                    ->send();
                            }
                        } catch (Throwable $e) {
                            Notification::make()
                                ->title('Redis Hatası')
                                ->danger()
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

                EditAction::make()
                    ->label('Düzenle'),

                DeleteAction::make()
                    ->label('Sil')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Seçilenleri Sil'),
                    ForceDeleteBulkAction::make()
                        ->label('Kalıcı Sil'),
                    RestoreBulkAction::make()
                        ->label('Geri Yükle'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    private static function resolveDocumentRoot(Site $site): string
    {
        $baseDirectory = rtrim($site->root_directory ?? '/srv/serverbond/sites', '/');
        $basePath = $baseDirectory . '/' . $site->domain;

        if (in_array($site->type, [SiteType::Laravel, SiteType::PHP, SiteType::Static], true)) {
            $publicDirectory = trim((string) ($site->public_directory ?: 'public'), '/');

            return $publicDirectory !== ''
                ? $basePath . '/' . $publicDirectory
                : $basePath;
        }

        return $basePath;
    }

    private static function resolvePhpVersion(Site $site): ?string
    {
        return match ($site->type) {
            SiteType::Laravel, SiteType::PHP => $site->php_version ?? config('deployment.nginx.default_php_version'),
            default => null,
        };
    }

    private static function resolveUpstreamPort(SiteType $type): ?int
    {
        return match ($type) {
            SiteType::NodeJS => (int) config('deployment.ports.nodejs'),
            SiteType::Python => (int) config('deployment.ports.python'),
            default => null,
        };
    }

    private static function mapSiteTypeForNginx(SiteType $type): string
    {
        return match ($type) {
            SiteType::NodeJS => 'node',
            default => $type->value,
        };
    }

}
