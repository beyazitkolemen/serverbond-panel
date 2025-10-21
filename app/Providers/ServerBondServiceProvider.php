<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Actions\System\UpdateOsService;
use App\Actions\System\RebootService;
use App\Actions\System\StatusService;
use App\Actions\System\HostnameService;
use App\Actions\System\UfwConfigureService;
use App\Actions\System\Fail2banInstallService;
use App\Actions\System\LogsService;
use App\Actions\System\TimezoneService;
use App\Actions\Meta\HealthService;
use App\Actions\Meta\VersionService;
use App\Actions\Meta\CapabilitiesService;
use App\Actions\Meta\UpdateService;
use App\Actions\Meta\DiagnosticsService;
use App\Actions\Nginx\InstallService as NginxInstallService;
use App\Actions\Nginx\StartService as NginxStartService;
use App\Actions\Nginx\StopService as NginxStopService;
use App\Actions\Nginx\RestartService as NginxRestartService;
use App\Actions\Nginx\ReloadService as NginxReloadService;
use App\Actions\Nginx\ConfigTestService as NginxConfigTestService;
use App\Actions\Nginx\AddSiteService as NginxAddSiteService;
use App\Actions\Nginx\RemoveSiteService as NginxRemoveSiteService;
use App\Actions\Nginx\ListSitesService as NginxListSitesService;
use App\Actions\Nginx\EnableSslService as NginxEnableSslService;
use App\Actions\Nginx\DisableSslService as NginxDisableSslService;
use App\Actions\Nginx\RebuildConfService as NginxRebuildConfService;
use App\Actions\Php\InstallStackService as PhpInstallStackService;
use App\Actions\Php\RestartService as PhpRestartService;
use App\Actions\Php\ConfigEditService as PhpConfigEditService;
use App\Actions\Php\InfoService as PhpInfoService;
use App\Actions\Redis\InstallService as RedisInstallService;
use App\Actions\Redis\StartService as RedisStartService;
use App\Actions\Redis\StopService as RedisStopService;
use App\Actions\Redis\RestartService as RedisRestartService;
use App\Actions\Redis\InfoService as RedisInfoService;
use App\Actions\Redis\FlushAllService as RedisFlushAllService;
use App\Actions\Mysql\InstallService as MysqlInstallService;
use App\Actions\Mysql\StartService as MysqlStartService;
use App\Actions\Mysql\StopService as MysqlStopService;
use App\Actions\Mysql\RestartService as MysqlRestartService;
use App\Actions\Mysql\CreateDatabaseService as MysqlCreateDatabaseService;
use App\Actions\Mysql\DeleteDatabaseService as MysqlDeleteDatabaseService;
use App\Actions\Mysql\CreateUserService as MysqlCreateUserService;
use App\Actions\Mysql\DeleteUserService as MysqlDeleteUserService;
use App\Actions\Mysql\ImportSqlService as MysqlImportSqlService;
use App\Actions\Mysql\ExportSqlService as MysqlExportSqlService;
use App\Actions\Mysql\StatusService as MysqlStatusService;
use App\Actions\Node\InstallNvmService;
use App\Actions\Node\UseVersionService;
use App\Actions\Node\Pm2InstallService;
use App\Actions\Node\Pm2AppAddService;
use App\Actions\Node\Pm2AppRemoveService;
use App\Actions\Node\Pm2AppRestartService;
use App\Actions\Node\Pm2ListService;
use App\Actions\Static\CreateSiteService;
use App\Actions\Static\DeployArtifactService;
use App\Actions\Wordpress\InstallStackService as WpInstallStackService;
use App\Actions\Wordpress\NewSiteService as WpNewSiteService;
use App\Actions\Wordpress\EnableSslService as WpEnableSslService;
use App\Actions\Wordpress\PluginInstallService as WpPluginInstallService;
use App\Actions\Wordpress\ThemeInstallService as WpThemeInstallService;
use App\Actions\Wordpress\CacheFlushService as WpCacheFlushService;
use App\Actions\Laravel\EnvWriteService;
use App\Actions\Laravel\DeployPipelineService;
use App\Actions\Laravel\ArtisanService;
use App\Actions\Laravel\QueueRestartService;
use App\Actions\Laravel\ScheduleRunService;
use App\Actions\Deploy\CloneRepoService;
use App\Actions\Deploy\GitPullService;
use App\Actions\Deploy\ComposerInstallService;
use App\Actions\Deploy\NpmBuildService;
use App\Actions\Deploy\CustomScriptWriteService;
use App\Actions\Deploy\CustomScriptRunService;
use App\Actions\Deploy\JsonPipelineRunService;
use App\Actions\Supervisor\InstallService as SupervisorInstallService;
use App\Actions\Supervisor\ReloadService as SupervisorReloadService;
use App\Actions\Supervisor\RestartService as SupervisorRestartService;
use App\Actions\Supervisor\AddProgramService as SupervisorAddProgramService;
use App\Actions\Supervisor\RemoveProgramService as SupervisorRemoveProgramService;
use App\Actions\Ssl\InstallCertbotService;
use App\Actions\Ssl\CreateSslService;
use App\Actions\Ssl\RenewSslService;
use App\Actions\Ssl\ListCertsService;
use App\Actions\Ssl\RemoveSslService;
use App\Actions\User\AddUserService;
use App\Actions\User\DeleteUserService;
use App\Actions\User\SshKeyAddService;
use App\Actions\User\SshKeyRemoveService;
use App\Actions\User\ListUsersService;
use App\Actions\Maintenance\EnableModeService;
use App\Actions\Maintenance\DisableModeService;
use App\Actions\Maintenance\BackupFilesService;
use App\Actions\Maintenance\BackupDbService;
use App\Actions\Maintenance\RestoreDbService;

class ServerBondServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // System Services
        $this->app->singleton(UpdateOsService::class);
        $this->app->singleton(RebootService::class);
        $this->app->singleton(StatusService::class);
        $this->app->singleton(HostnameService::class);
        $this->app->singleton(UfwConfigureService::class);
        $this->app->singleton(Fail2banInstallService::class);
        $this->app->singleton(LogsService::class);
        $this->app->singleton(TimezoneService::class);

        // Meta Services
        $this->app->singleton(HealthService::class);
        $this->app->singleton(VersionService::class);
        $this->app->singleton(CapabilitiesService::class);
        $this->app->singleton(UpdateService::class);
        $this->app->singleton(DiagnosticsService::class);

        // Nginx Services
        $this->app->singleton(NginxInstallService::class);
        $this->app->singleton(NginxStartService::class);
        $this->app->singleton(NginxStopService::class);
        $this->app->singleton(NginxRestartService::class);
        $this->app->singleton(NginxReloadService::class);
        $this->app->singleton(NginxConfigTestService::class);
        $this->app->singleton(NginxAddSiteService::class);
        $this->app->singleton(NginxRemoveSiteService::class);
        $this->app->singleton(NginxListSitesService::class);
        $this->app->singleton(NginxEnableSslService::class);
        $this->app->singleton(NginxDisableSslService::class);
        $this->app->singleton(NginxRebuildConfService::class);

        // PHP Services
        $this->app->singleton(PhpInstallStackService::class);
        $this->app->singleton(PhpRestartService::class);
        $this->app->singleton(PhpConfigEditService::class);
        $this->app->singleton(PhpInfoService::class);

        // Redis Services
        $this->app->singleton(RedisInstallService::class);
        $this->app->singleton(RedisStartService::class);
        $this->app->singleton(RedisStopService::class);
        $this->app->singleton(RedisRestartService::class);
        $this->app->singleton(RedisInfoService::class);
        $this->app->singleton(RedisFlushAllService::class);

        // MySQL Services
        $this->app->singleton(MysqlInstallService::class);
        $this->app->singleton(MysqlStartService::class);
        $this->app->singleton(MysqlStopService::class);
        $this->app->singleton(MysqlRestartService::class);
        $this->app->singleton(MysqlCreateDatabaseService::class);
        $this->app->singleton(MysqlDeleteDatabaseService::class);
        $this->app->singleton(MysqlCreateUserService::class);
        $this->app->singleton(MysqlDeleteUserService::class);
        $this->app->singleton(MysqlImportSqlService::class);
        $this->app->singleton(MysqlExportSqlService::class);
        $this->app->singleton(MysqlStatusService::class);

        // Node.js Services
        $this->app->singleton(InstallNvmService::class);
        $this->app->singleton(UseVersionService::class);
        $this->app->singleton(Pm2InstallService::class);
        $this->app->singleton(Pm2AppAddService::class);
        $this->app->singleton(Pm2AppRemoveService::class);
        $this->app->singleton(Pm2AppRestartService::class);
        $this->app->singleton(Pm2ListService::class);

        // Static Site Services
        $this->app->singleton(CreateSiteService::class);
        $this->app->singleton(DeployArtifactService::class);

        // WordPress Services
        $this->app->singleton(WpInstallStackService::class);
        $this->app->singleton(WpNewSiteService::class);
        $this->app->singleton(WpEnableSslService::class);
        $this->app->singleton(WpPluginInstallService::class);
        $this->app->singleton(WpThemeInstallService::class);
        $this->app->singleton(WpCacheFlushService::class);

        // Laravel Services
        $this->app->singleton(EnvWriteService::class);
        $this->app->singleton(DeployPipelineService::class);
        $this->app->singleton(ArtisanService::class);
        $this->app->singleton(QueueRestartService::class);
        $this->app->singleton(ScheduleRunService::class);

        // Deployment Services
        $this->app->singleton(CloneRepoService::class);
        $this->app->singleton(GitPullService::class);
        $this->app->singleton(ComposerInstallService::class);
        $this->app->singleton(NpmBuildService::class);
        $this->app->singleton(CustomScriptWriteService::class);
        $this->app->singleton(CustomScriptRunService::class);
        $this->app->singleton(JsonPipelineRunService::class);

        // Supervisor Services
        $this->app->singleton(SupervisorInstallService::class);
        $this->app->singleton(SupervisorReloadService::class);
        $this->app->singleton(SupervisorRestartService::class);
        $this->app->singleton(SupervisorAddProgramService::class);
        $this->app->singleton(SupervisorRemoveProgramService::class);

        // SSL Services
        $this->app->singleton(InstallCertbotService::class);
        $this->app->singleton(CreateSslService::class);
        $this->app->singleton(RenewSslService::class);
        $this->app->singleton(ListCertsService::class);
        $this->app->singleton(RemoveSslService::class);

        // User Services
        $this->app->singleton(AddUserService::class);
        $this->app->singleton(DeleteUserService::class);
        $this->app->singleton(SshKeyAddService::class);
        $this->app->singleton(SshKeyRemoveService::class);
        $this->app->singleton(ListUsersService::class);

        // Maintenance Services
        $this->app->singleton(EnableModeService::class);
        $this->app->singleton(DisableModeService::class);
        $this->app->singleton(BackupFilesService::class);
        $this->app->singleton(BackupDbService::class);
        $this->app->singleton(RestoreDbService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}