<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\SystemService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ServerStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $systemService = app(SystemService::class);

        $cpuUsage = $systemService->getCpuUsage();
        $memoryUsage = $systemService->getMemoryUsage();
        $diskUsage = $systemService->getDiskUsage();
        $systemInfo = $systemService->getSystemInfo();

        return [
            Stat::make('CPU Kullanımı', number_format($cpuUsage, 1) . '%')
                ->description('İşlemci yükü')
                ->descriptionIcon('heroicon-o-cpu-chip')
                ->color($cpuUsage > 80 ? 'danger' : ($cpuUsage > 60 ? 'warning' : 'success'))
                ->chart([$cpuUsage, $cpuUsage * 0.9, $cpuUsage * 1.1, $cpuUsage]),

            Stat::make('RAM Kullanımı', $this->formatBytes($memoryUsage['used']) . ' / ' . $this->formatBytes($memoryUsage['total']))
                ->description(number_format($memoryUsage['percentage'], 1) . '% kullanımda')
                ->descriptionIcon('heroicon-o-server')
                ->color($memoryUsage['percentage'] > 80 ? 'danger' : ($memoryUsage['percentage'] > 60 ? 'warning' : 'success'))
                ->chart([$memoryUsage['percentage'], $memoryUsage['percentage'] * 0.95, $memoryUsage['percentage'] * 1.05, $memoryUsage['percentage']]),

            Stat::make('Disk Kullanımı', $this->formatBytes($diskUsage['used']) . ' / ' . $this->formatBytes($diskUsage['total']))
                ->description(number_format($diskUsage['percentage'], 1) . '% kullanımda')
                ->descriptionIcon('heroicon-o-circle-stack')
                ->color($diskUsage['percentage'] > 80 ? 'danger' : ($diskUsage['percentage'] > 60 ? 'warning' : 'success'))
                ->chart([$diskUsage['percentage'], $diskUsage['percentage'] * 0.98, $diskUsage['percentage'] * 1.02, $diskUsage['percentage']]),

            Stat::make('Sistem Bilgisi', $systemInfo['hostname'])
                ->description($systemInfo['os'])
                ->descriptionIcon('heroicon-o-server-stack')
                ->color('info'),
        ];
    }

    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
