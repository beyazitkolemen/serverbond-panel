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





}
