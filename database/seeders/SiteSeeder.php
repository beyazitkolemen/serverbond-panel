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

        $site = Site::create([
            'name' => 'Laravel Blog',
            'domain' => 'blog.example.com',
            'type' => SiteType::Laravel->value,
            'php_version' => PHPVersion::PHP84->value,
            'root_directory' => '/var/www/blog.example.com',
        ]);

        $appService = new AppService();
        $appService->createApp($site);
    }
}
