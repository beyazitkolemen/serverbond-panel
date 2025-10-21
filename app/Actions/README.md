# ServerBond Agent Services

Bu klasör, ServerBond Agent Codex'inde tanımlanan tüm script'leri Laravel servisleri olarak kullanılabilir hale getirir.

## Kurulum

1. Service Provider'ı `config/app.php` dosyasına ekleyin:

```php
'providers' => [
    // ... diğer provider'lar
    App\Providers\ServerBondServiceProvider::class,
],
```

## Kullanım

Servisler Laravel'in dependency injection sistemi ile kullanılabilir:

```php
use App\Actions\System\StatusService;
use App\Actions\Nginx\AddSiteService;
use App\Actions\Laravel\DeployPipelineService;

class ExampleController extends Controller
{
    public function __construct(
        private StatusService $statusService,
        private AddSiteService $addSiteService,
        private DeployPipelineService $deployService
    ) {}

    public function getSystemStatus()
    {
        return $this->statusService->execute();
    }

    public function addNginxSite()
    {
        return $this->addSiteService->execute(
            domain: 'example.com',
            type: 'laravel',
            root: '/var/www/example.com/public',
            phpVersion: '8.3'
        );
    }

    public function deployLaravelProject()
    {
        return $this->deployService->execute(
            project: 'my-project',
            runBuild: true
        );
    }
}
```

## Kategoriler

### System
- `UpdateOsService` - OS paketlerini güncelle
- `RebootService` - Sunucuyu yeniden başlat
- `StatusService` - Sistem durumu
- `HostnameService` - Hostname yönetimi
- `UfwConfigureService` - Firewall yapılandırması
- `Fail2banInstallService` - Fail2ban kurulumu
- `LogsService` - Sistem logları
- `TimezoneService` - Saat dilimi yönetimi

### Meta
- `HealthService` - Agent sağlık kontrolü
- `VersionService` - Agent sürüm bilgisi
- `CapabilitiesService` - Mevcut script listesi
- `UpdateService` - Agent güncelleme
- `DiagnosticsService` - Hızlı tanılama

### Nginx
- `InstallService` - Nginx kurulumu
- `StartService`, `StopService`, `RestartService`, `ReloadService` - Servis yönetimi
- `ConfigTestService` - Konfigürasyon testi
- `AddSiteService`, `RemoveSiteService` - Site yönetimi
- `ListSitesService` - Site listesi
- `EnableSslService`, `DisableSslService` - SSL yönetimi
- `RebuildConfService` - Konfigürasyon yeniden oluşturma

### PHP
- `InstallStackService` - PHP-FPM ve eklentiler
- `RestartService` - PHP-FPM yeniden başlatma
- `ConfigEditService` - PHP konfigürasyon düzenleme
- `InfoService` - PHP bilgileri

### Redis
- `InstallService` - Redis kurulumu
- `StartService`, `StopService`, `RestartService` - Servis yönetimi
- `InfoService` - Redis bilgileri
- `FlushAllService` - Tüm veriyi temizleme

### MySQL
- `InstallService` - MySQL/MariaDB kurulumu
- `StartService`, `StopService`, `RestartService` - Servis yönetimi
- `CreateDatabaseService`, `DeleteDatabaseService` - Veritabanı yönetimi
- `CreateUserService`, `DeleteUserService` - Kullanıcı yönetimi
- `ImportSqlService`, `ExportSqlService` - Veri aktarımı
- `StatusService` - MySQL durumu

### Node.js
- `InstallNvmService` - NVM kurulumu
- `UseVersionService` - Node sürüm seçimi
- `Pm2InstallService` - PM2 kurulumu
- `Pm2AppAddService`, `Pm2AppRemoveService`, `Pm2AppRestartService` - PM2 uygulama yönetimi
- `Pm2ListService` - PM2 süreç listesi

### Static Sites
- `CreateSiteService` - Static site oluşturma
- `DeployArtifactService` - Build artifact dağıtımı

### WordPress
- `InstallStackService` - WordPress stack kurulumu
- `NewSiteService` - Yeni WordPress sitesi
- `EnableSslService` - WordPress SSL
- `PluginInstallService`, `ThemeInstallService` - Eklenti/tema yönetimi
- `CacheFlushService` - Cache temizleme

### Laravel
- `EnvWriteService` - .env dosyası yazma
- `DeployPipelineService` - Deploy pipeline
- `ArtisanService` - Artisan komutları
- `QueueRestartService` - Queue yeniden başlatma
- `ScheduleRunService` - Schedule çalıştırma

### Deployment
- `CloneRepoService` - Repo klonlama
- `GitPullService` - Git pull
- `ComposerInstallService` - Composer install
- `NpmBuildService` - NPM build
- `CustomScriptWriteService`, `CustomScriptRunService` - Custom script yönetimi
- `JsonPipelineRunService` - JSON pipeline çalıştırma

### Supervisor
- `InstallService` - Supervisor kurulumu
- `ReloadService`, `RestartService` - Servis yönetimi
- `AddProgramService`, `RemoveProgramService` - Program yönetimi

### SSL
- `InstallCertbotService` - Certbot kurulumu
- `CreateSslService` - SSL sertifika oluşturma
- `RenewSslService` - Sertifika yenileme
- `ListCertsService` - Sertifika listesi
- `RemoveSslService` - SSL kaldırma

### User Management
- `AddUserService`, `DeleteUserService` - Kullanıcı yönetimi
- `SshKeyAddService`, `SshKeyRemoveService` - SSH key yönetimi
- `ListUsersService` - Kullanıcı listesi

### Maintenance
- `EnableModeService`, `DisableModeService` - Maintenance mode
- `BackupFilesService` - Dosya yedekleme
- `BackupDbService`, `RestoreDbService` - Veritabanı yedekleme/geri yükleme

## Özellikler

- **Type Safety**: Tüm servisler PHP 8.1+ strict typing ile yazılmıştır
- **Dependency Injection**: Laravel'in service container'ı ile entegre
- **Error Handling**: Kapsamlı hata yönetimi ve loglama
- **Parameter Validation**: Gerekli parametrelerin otomatik doğrulanması
- **JSON Output**: Script'lerden dönen JSON çıktıların otomatik parse edilmesi
- **Logging**: Tüm işlemler Laravel log sistemi ile kaydedilir

## Notlar

- Tüm servisler `/opt/serverbond-agent/scripts/` dizinindeki script'leri çalıştırır
- Script'lerin mevcut olması gereklidir
- Servisler singleton olarak kayıtlıdır
- Her servis kendi kategorisindeki script'leri yönetir