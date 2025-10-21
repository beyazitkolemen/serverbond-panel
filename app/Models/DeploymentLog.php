<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeploymentLog extends Model
{
    protected $fillable = [
        'deployment_id',
        'site_id',
        'level',
        'message',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function deployment(): BelongsTo
    {
        return $this->belongsTo(Deployment::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public static function log(
        int $siteId,
        string $level,
        string $message,
        ?int $deploymentId = null,
        ?array $context = null
    ): self {
        return self::create([
            'site_id' => $siteId,
            'deployment_id' => $deploymentId,
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ]);
    }

    public static function info(int $siteId, string $message, ?int $deploymentId = null, ?array $context = null): self
    {
        return self::log($siteId, 'info', $message, $deploymentId, $context);
    }

    public static function success(int $siteId, string $message, ?int $deploymentId = null, ?array $context = null): self
    {
        return self::log($siteId, 'success', $message, $deploymentId, $context);
    }

    public static function warning(int $siteId, string $message, ?int $deploymentId = null, ?array $context = null): self
    {
        return self::log($siteId, 'warning', $message, $deploymentId, $context);
    }

    public static function error(int $siteId, string $message, ?int $deploymentId = null, ?array $context = null): self
    {
        return self::log($siteId, 'error', $message, $deploymentId, $context);
    }
}

