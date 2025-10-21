<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SiteStatus;
use App\Enums\SiteType;
use App\Services\DeploymentScriptService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'domain',
        'type',
        'root_directory',
        'public_directory',
        'git_repository',
        'git_branch',
        'git_deploy_key',
        'status',
        'php_version',
        'database_name',
        'database_user',
        'database_password',
        'ssl_enabled',
        'auto_deploy',
        'deploy_webhook_token',
        'cloudflare_tunnel_token',
        'cloudflare_tunnel_id',
        'cloudflare_tunnel_enabled',
        'last_deployed_at',
        'notes',
        'deployment_script',
    ];

    protected $casts = [
        'type' => SiteType::class,
        'status' => SiteStatus::class,
        'ssl_enabled' => 'boolean',
        'auto_deploy' => 'boolean',
        'cloudflare_tunnel_enabled' => 'boolean',
        'last_deployed_at' => 'datetime',
    ];



    public function deployments(): HasMany
    {
        return $this->hasMany(Deployment::class)->latest();
    }

    public function latestDeployment(): HasOne
    {
        return $this->hasOne(Deployment::class)->latestOfMany();
    }

    public function deploymentLogs(): HasMany
    {
        return $this->hasMany(DeploymentLog::class)->latest();
    }

    public function sslCertificate(): HasOne
    {
        return $this->hasOne(SslCertificate::class);
    }

    public function envVariables(): HasMany
    {
        return $this->hasMany(EnvVariable::class);
    }

    public function isActive(): bool
    {
        return $this->status === SiteStatus::Active;
    }

    public function isDeploying(): bool
    {
        return $this->status === SiteStatus::Deploying;
    }

    protected static function booted(): void
    {
        static::creating(function (Site $site) {
            // Deployment script yoksa, site tipine göre default oluştur
            if (empty($site->deployment_script)) {
                $scriptService = app(DeploymentScriptService::class);
                $site->deployment_script = $scriptService->getDefaultScript($site->type);
            }
        });

        static::saved(function (Site $site) {
            // .env dosyası içeriği varsa kaydet
            if (request()->has('env_content') && !empty(request('env_content'))) {
                $site->saveEnvFile(request('env_content'));
            }
        });
    }


    /**
     * Site tipi için varsayılan deployment script'ini döndürür
     */
    public static function getDefaultDeploymentScript(SiteType $type): string
    {
        $scriptService = app(DeploymentScriptService::class);
        return $scriptService->getDefaultScript($type);
    }

    /**
     * .env dosyasını siteye kaydet
     */
    public function saveEnvFile(string $content): bool
    {
        $sitePath = rtrim($this->root_directory, '/') . '/' . $this->domain;
        $envPath = $sitePath . '/.env';

        // Site dizini yoksa oluştur
        if (!file_exists($sitePath)) {
            mkdir($sitePath, 0755, true);
        }

        // .env dosyasını kaydet
        return file_put_contents($envPath, $content) !== false;
    }

    /**
     * .env dosyasını oku
     */
    public function getEnvFile(): ?string
    {
        $envPath = rtrim($this->root_directory, '/') . '/' . $this->domain . '/.env';

        if (file_exists($envPath)) {
            return file_get_contents($envPath);
        }

        return null;
    }

    /**
     * .env dosyasının template'ini döndür
     */
    public function getDefaultEnvContent(): string
    {
        return match ($this->type) {
            SiteType::Laravel => $this->getLaravelEnvTemplate(),
            SiteType::PHP => $this->getPhpEnvTemplate(),
            SiteType::NodeJS => $this->getNodeEnvTemplate(),
            SiteType::Python => $this->getPythonEnvTemplate(),
            default => '',
        };
    }

    protected function getLaravelEnvTemplate(): string
    {
        $dbName = $this->database_name ?? '';
        $dbUser = $this->database_user ?? '';
        $dbPassword = $this->database_password ?? '';

        return <<<ENV
APP_NAME={$this->name}
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://{$this->domain}

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE={$dbName}
DB_USERNAME={$dbUser}
DB_PASSWORD={$dbPassword}

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@{$this->domain}"
MAIL_FROM_NAME="{$this->name}"
ENV;
    }

    protected function getPhpEnvTemplate(): string
    {
        $dbName = $this->database_name ?? '';
        $dbUser = $this->database_user ?? '';
        $dbPassword = $this->database_password ?? '';

        return <<<ENV
APP_NAME={$this->name}
APP_ENV=production
APP_DEBUG=false
APP_URL=https://{$this->domain}

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE={$dbName}
DB_USERNAME={$dbUser}
DB_PASSWORD={$dbPassword}
ENV;
    }

    protected function getNodeEnvTemplate(): string
    {
        $dbName = $this->database_name ?? '';
        $dbUser = $this->database_user ?? '';
        $dbPassword = $this->database_password ?? '';

        return <<<ENV
NODE_ENV=production
APP_NAME={$this->name}
APP_URL=https://{$this->domain}
PORT=3000

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE={$dbName}
DB_USERNAME={$dbUser}
DB_PASSWORD={$dbPassword}
ENV;
    }

    protected function getPythonEnvTemplate(): string
    {
        $dbName = $this->database_name ?? '';
        $dbUser = $this->database_user ?? '';
        $dbPassword = $this->database_password ?? '';

        return <<<ENV
DEBUG=False
SECRET_KEY=
ALLOWED_HOSTS={$this->domain},www.{$this->domain}

DATABASE_NAME={$dbName}
DATABASE_USER={$dbUser}
DATABASE_PASSWORD={$dbPassword}
DATABASE_HOST=127.0.0.1
DATABASE_PORT=3306
ENV;
    }
}
