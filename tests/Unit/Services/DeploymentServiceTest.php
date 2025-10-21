<?php

namespace Tests\Unit\Services;

use App\Enums\DeploymentStatus;
use App\Enums\SiteStatus;
use App\Enums\SiteType;
use App\Models\Deployment;
use App\Models\Site;
use App\Services\DeploymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class DeploymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        File::deleteDirectory(storage_path('app/test-sites'));
        File::deleteDirectory(storage_path('app/deploy-keys'));

        parent::tearDown();
    }

    public function test_deploy_clones_repository_and_records_metadata(): void
    {
        Process::fake([
            "git clone -b 'main' 'git@github.com:example/repo.git' ." => Process::result("Cloning repository"),
            'git rev-parse HEAD' => Process::result("abcdef1234567890\n"),
            'git log -1 --pretty=%B' => Process::result("Initial commit\n"),
            'git log -1 --pretty=format:"%an <%ae>"' => Process::result("Jane Doe <jane@example.com>\n"),
        ]);

        $rootDirectory = storage_path('app/test-sites');
        File::deleteDirectory($rootDirectory);
        File::makeDirectory($rootDirectory, 0755, true);

        $site = Site::create([
            'name' => 'Example',
            'domain' => 'example.com',
            'type' => SiteType::Laravel,
            'root_directory' => $rootDirectory,
            'git_repository' => 'git@github.com:example/repo.git',
            'git_branch' => 'main',
            'git_deploy_key' => 'FAKE-SSH-KEY',
            'database_name' => 'example',
            'database_user' => 'example',
            'database_password' => 'secret',
            'deployment_script' => "#!/bin/bash\necho 'done'",
        ]);

        $service = app(DeploymentService::class);

        $deployment = $service->deploy($site);

        $deployment->refresh();
        $site->refresh();

        $this->assertSame(DeploymentStatus::Success, $deployment->status);
        $this->assertSame('abcdef1234567890', $deployment->commit_hash);
        $this->assertSame('Initial commit', $deployment->commit_message);
        $this->assertSame('Jane Doe <jane@example.com>', $deployment->commit_author);
        $this->assertSame(SiteStatus::Active, $site->status);
        $this->assertStringContainsString('Cloning repository...', $deployment->output);
        $this->assertStringContainsString('Checked out commit abcdef1', $deployment->output);
        $this->assertTrue(File::exists($rootDirectory . '/example.com/.env'));

        Process::assertRan("git clone -b 'main' 'git@github.com:example/repo.git' .");
        Process::assertRan('git rev-parse HEAD');

        $deployKeyFiles = glob(storage_path('app/deploy-keys/deploy-key-' . $site->id . '-*')) ?: [];
        $this->assertSame([], $deployKeyFiles);
    }

    public function test_deploy_records_failure_and_preserves_output(): void
    {
        Process::fake([
            "git clone -b 'main' 'git@github.com:example/repo.git' ." => Process::result('', 'Permission denied', 1),
        ]);

        $rootDirectory = storage_path('app/test-sites');
        File::deleteDirectory($rootDirectory);
        File::makeDirectory($rootDirectory, 0755, true);

        $site = Site::create([
            'name' => 'Example',
            'domain' => 'example.com',
            'type' => SiteType::Laravel,
            'root_directory' => $rootDirectory,
            'git_repository' => 'git@github.com:example/repo.git',
            'git_branch' => 'main',
            'git_deploy_key' => null,
            'database_name' => 'example',
            'database_user' => 'example',
            'database_password' => 'secret',
            'deployment_script' => "#!/bin/bash\necho 'done'",
        ]);

        $service = app(DeploymentService::class);

        $this->expectExceptionMessage('Git clone failed: Permission denied');

        try {
            $service->deploy($site);
        } finally {
            $deployment = Deployment::first();
            $site->refresh();

            $this->assertNotNull($deployment);
            $this->assertSame(DeploymentStatus::Failed, $deployment->status);
            $this->assertSame(SiteStatus::Error, $site->status);
            $this->assertStringContainsString('Cloning repository...', $deployment->output);
            $this->assertStringContainsString('Permission denied', $deployment->output);
        }
    }
}
