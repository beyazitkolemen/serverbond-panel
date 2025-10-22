<?php

namespace Database\Seeders;

use App\Models\Site;
use App\Enums\SiteType;
use App\Enums\PHPVersion;
use App\Services\AppService;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        $sites = [
            [
                'name' => 'Laravel Blog',
                'domain' => 'blog.example.com',
                'type' => SiteType::Laravel,
                'php_version' => PHPVersion::PHP84,
            ],
        ];

        foreach ($sites as $site) {
            $site = Site::create([
                'name' => $site['name'],
                'domain' => $site['domain'],
                'type' => $site['type'],
                'php_version' => $site['php_version'],
            ]);
            $appService = new AppService();
            $appService->createApp($site);
        }
    }
}
