<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DeploymentStatus;
use App\Enums\DeploymentTrigger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deployment extends Model
{
    protected $fillable = [
        'site_id',
        'user_id',
        'commit_hash',
        'commit_message',
        'commit_author',
        'status',
        'trigger',
        'output',
        'error',
        'started_at',
        'finished_at',
        'duration',
    ];

    protected $casts = [
        'status' => DeploymentStatus::class,
        'trigger' => DeploymentTrigger::class,
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isRunning(): bool
    {
        return $this->status === DeploymentStatus::Running;
    }

    public function isSuccess(): bool
    {
        return $this->status === DeploymentStatus::Success;
    }

    public function isFailed(): bool
    {
        return $this->status === DeploymentStatus::Failed;
    }
}
