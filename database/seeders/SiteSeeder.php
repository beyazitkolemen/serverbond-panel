<?php

namespace Database\Seeders;

use App\Models\Site;
use App\Enums\SiteType;
use App\Models\Database;
use App\Enums\PHPVersion;
use App\Services\AppService;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    public function run(): void
    {        Database::truncate();

        Site::truncate();
        $sites = [
            [
                'name' => 'Laravel Blog',
                'domain' => 'blog.example.com',
                'type' => SiteType::Laravel->value,
                'php_version' => PHPVersion::PHP84->value,
                'root_directory' => '/var/www/deneme',
            ],
        ];

        foreach ($sites as $site) {
            $site = Site::create([
                'name' => $site['name'],
                'domain' => $site['domain'],
                'type' => $site['type'],
                'php_version' => $site['php_version'],
                'root_directory' => '/var/www/deneme',
            ]);
            $appService = new AppService();
            $appService->createApp($site);
            dd($appService);
        }
    }
}
