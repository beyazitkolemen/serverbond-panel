<?php

declare(strict_types=1);

namespace App\Enums;

enum DeploymentStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Success = 'success';
    case Failed = 'failed';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Bekliyor',
            self::Running => 'Çalışıyor',
            self::Success => 'Başarılı',
            self::Failed => 'Başarısız',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending => 'gray',
            self::Running => 'info',
            self::Success => 'success',
            self::Failed => 'danger',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Pending => 'heroicon-o-clock',
            self::Running => 'heroicon-o-arrow-path',
            self::Success => 'heroicon-o-check-circle',
            self::Failed => 'heroicon-o-x-circle',
        };
    }
}

