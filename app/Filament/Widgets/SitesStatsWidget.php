<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\SiteStatus;
use App\Models\Deployment;
use App\Models\Site;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SitesStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalSites = Site::count();
        $activeSites = Site::where('status', SiteStatus::Active)->count();
        $inactiveSites = Site::where('status', SiteStatus::Inactive)->count();
        $errorSites = Site::where('status', SiteStatus::Error)->count();

        $totalDeployments = Deployment::count();
        $successfulDeployments = Deployment::where('status', 'success')->count();
        $failedDeployments = Deployment::where('status', 'failed')->count();

        $successRate = $totalDeployments > 0 ? ($successfulDeployments / $totalDeployments) * 100 : 0;

        return [
            Stat::make('Toplam Site', $totalSites)
                ->description("{$activeSites} aktif, {$inactiveSites} inaktif")
                ->descriptionIcon('heroicon-o-globe-alt')
                ->color('primary')
                ->chart(array_values(Site::selectRaw('count(*) as count')
                    ->whereBetween('created_at', [now()->subDays(7), now()])
                    ->groupByRaw('DATE(created_at)')
                    ->pluck('count')
                    ->toArray())),

            Stat::make('Deployment Sayısı', $totalDeployments)
                ->description("{$successfulDeployments} başarılı, {$failedDeployments} başarısız")
                ->descriptionIcon('heroicon-o-rocket-launch')
                ->color($failedDeployments > 0 ? 'warning' : 'success'),

            Stat::make('Başarı Oranı', number_format($successRate, 1) . '%')
                ->description('Son deployment başarı oranı')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color($successRate > 90 ? 'success' : ($successRate > 70 ? 'warning' : 'danger'))
                ->chart(array_fill(0, 7, $successRate)),

            Stat::make('Hatalı Siteler', $errorSites)
                ->description('Dikkat gerektiren siteler')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($errorSites > 0 ? 'danger' : 'success'),
        ];
    }
}
