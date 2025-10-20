<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SiteStatus;
use App\Enums\SiteType;
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
        'last_deployed_at',
        'notes',
    ];

    protected $casts = [
        'type' => SiteType::class,
        'status' => SiteStatus::class,
        'ssl_enabled' => 'boolean',
        'auto_deploy' => 'boolean',
        'last_deployed_at' => 'datetime',
    ];

    protected $hidden = [
        'database_password',
        'git_deploy_key',
        'deploy_webhook_token',
    ];

    public function deployments(): HasMany
    {
        return $this->hasMany(Deployment::class)->latest();
    }

    public function latestDeployment(): HasOne
    {
        return $this->hasOne(Deployment::class)->latestOfMany();
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
}
