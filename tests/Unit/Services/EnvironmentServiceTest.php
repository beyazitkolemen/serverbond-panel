<?php

namespace Tests\Unit\Services;

use App\Enums\SiteType;
use App\Models\Site;
use App\Services\EnvironmentService;
use App\Services\MySQLService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Mockery;
use Tests\TestCase;

class EnvironmentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        File::deleteDirectory(storage_path('app/env-service-test'));

        parent::tearDown();
    }

    public function test_it_builds_env_file_from_example_and_database_credentials(): void
    {
        $mysqlService = Mockery::mock(MySQLService::class);
        $mysqlService->shouldReceive('createDatabaseForSite')
            ->once()
            ->andReturn([
                'success' => true,
                'database' => 'db_demo_app',
                'user' => 'user_demo_app',
                'password' => 'p@ss-1234',
            ]);

        $service = new EnvironmentService($mysqlService);

        $rootPath = storage_path('app/env-service-test');
        File::deleteDirectory($rootPath);
        File::makeDirectory($rootPath, 0755, true);

        File::put($rootPath . '/.env.example', <<<ENV
APP_NAME=Demo
DB_CONNECTION=sqlite
DB_DATABASE=legacy
DB_USERNAME=root
DB_PASSWORD=
ENV);

        $site = Site::create([
            'name' => 'Demo',
            'domain' => 'demo.test',
            'type' => SiteType::Laravel,
            'root_directory' => $rootPath,
        ]);

        $service->synchronizeEnvironmentFile($site, $rootPath);

        $envPath = $rootPath . '/.env';
        $this->assertTrue(File::exists($envPath));

        $envContent = File::get($envPath);
        $this->assertStringContainsString('DB_CONNECTION=mysql', $envContent);
        $this->assertStringContainsString('DB_HOST=127.0.0.1', $envContent);
        $this->assertStringContainsString('DB_PORT=3306', $envContent);
        $this->assertStringContainsString('DB_DATABASE=db_demo_app', $envContent);
        $this->assertStringContainsString('DB_USERNAME=user_demo_app', $envContent);
        $this->assertStringContainsString('DB_PASSWORD="p@ss-1234"', $envContent);

        $site->refresh();
        $this->assertSame('db_demo_app', $site->database_name);
        $this->assertSame('user_demo_app', $site->database_user);
        $this->assertSame('p@ss-1234', $site->database_password);
    }
}
