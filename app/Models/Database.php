<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Database extends Model
{
    protected $fillable = [
        'name',
        'username',
        'password',
        'charset',
        'collation',
        'max_connections',
        'site_id',
        'notes',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'max_connections' => 'integer',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
