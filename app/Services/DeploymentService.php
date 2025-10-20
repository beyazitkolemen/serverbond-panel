<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DeploymentStatus;
use App\Enums\DeploymentTrigger;
use App\Enums\SiteStatus;
use App\Models\Deployment;
use App\Models\Site;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class DeploymentService
{
    public function deploy(Site $site, DeploymentTrigger $trigger = DeploymentTrigger::Manual, ?int $userId = null): Deployment
    {
        $deployment = Deployment::create([
            'site_id' => $site->id,
            'user_id' => $userId,
            'status' => DeploymentStatus::Pending,
            'trigger' => $trigger,
            'started_at' => now(),
        ]);

        $site->update(['status' => SiteStatus::Deploying]);

        try {
            $this->runDeployment($site, $deployment);
        } catch (\Exception $e) {
            $deployment->update([
                'status' => DeploymentStatus::Failed,
                'error' => $e->getMessage(),
                'finished_at' => now(),
                'duration' => now()->diffInSeconds($deployment->started_at),
            ]);

            $site->update(['status' => SiteStatus::Error]);
        }

        return $deployment->fresh();
    }

    protected function runDeployment(Site $site, Deployment $deployment): void
    {
        $deployment->update(['status' => DeploymentStatus::Running]);

        $rootPath = rtrim($site->root_directory, '/') . '/' . $site->domain;
        $output = [];

        // 1. Clone or pull repository
        if ($site->git_repository) {
            if (!File::exists($rootPath)) {
                $output[] = "Cloning repository...";
                $result = Process::path(dirname($rootPath))
                    ->run("git clone -b {$site->git_branch} {$site->git_repository} {$site->domain}");

                $output[] = $result->output();

                if (!$result->successful()) {
                    throw new \Exception("Git clone failed: " . $result->errorOutput());
                }
            } else {
                $output[] = "Pulling latest changes...";
                $result = Process::path($rootPath)->run("git pull origin {$site->git_branch}");

                $output[] = $result->output();

                if (!$result->successful()) {
                    throw new \Exception("Git pull failed: " . $result->errorOutput());
                }
            }

            // Get commit info
            $commitHash = Process::path($rootPath)->run('git rev-parse HEAD');
            $commitMessage = Process::path($rootPath)->run('git log -1 --pretty=%B');
            $commitAuthor = Process::path($rootPath)->run('git log -1 --pretty=format:"%an <%ae>"');

            $deployment->update([
                'commit_hash' => trim($commitHash->output()),
                'commit_message' => trim($commitMessage->output()),
                'commit_author' => trim($commitAuthor->output()),
            ]);
        }

        // 2. Type-specific deployment
        match($site->type->value) {
            'laravel' => $this->deployLaravel($site, $rootPath, $output),
            'php' => $this->deployPHP($site, $rootPath, $output),
            'nodejs' => $this->deployNodeJS($site, $rootPath, $output),
            'python' => $this->deployPython($site, $rootPath, $output),
            'static' => $this->deployStatic($site, $rootPath, $output),
            default => null,
        };

        // 3. Set permissions
        $output[] = "Setting permissions...";
        Process::run("chown -R www-data:www-data {$rootPath}");
        Process::run("chmod -R 755 {$rootPath}");

        // Update deployment
        $deployment->update([
            'status' => DeploymentStatus::Success,
            'output' => implode("\n", $output),
            'finished_at' => now(),
            'duration' => now()->diffInSeconds($deployment->started_at),
        ]);

        $site->update([
            'status' => SiteStatus::Active,
            'last_deployed_at' => now(),
        ]);
    }

    protected function deployLaravel(Site $site, string $rootPath, array &$output): void
    {
        // Install dependencies
        $output[] = "Installing Composer dependencies...";
        $result = Process::path($rootPath)->run('composer install --no-dev --optimize-autoloader');
        $output[] = $result->output();

        // Run migrations
        $output[] = "Running migrations...";
        $result = Process::path($rootPath)->run('php artisan migrate --force');
        $output[] = $result->output();

        // Clear and cache config
        $output[] = "Optimizing...";
        Process::path($rootPath)->run('php artisan config:cache');
        Process::path($rootPath)->run('php artisan route:cache');
        Process::path($rootPath)->run('php artisan view:cache');

        // Storage link
        if (!File::exists("{$rootPath}/public/storage")) {
            Process::path($rootPath)->run('php artisan storage:link');
        }

        // NPM (if package.json exists)
        if (File::exists("{$rootPath}/package.json")) {
            $output[] = "Installing NPM dependencies...";
            Process::path($rootPath)->run('npm install');
            Process::path($rootPath)->run('npm run build');
        }
    }

    protected function deployPHP(Site $site, string $rootPath, array &$output): void
    {
        // Composer if exists
        if (File::exists("{$rootPath}/composer.json")) {
            $output[] = "Installing Composer dependencies...";
            $result = Process::path($rootPath)->run('composer install --no-dev --optimize-autoloader');
            $output[] = $result->output();
        }
    }

    protected function deployNodeJS(Site $site, string $rootPath, array &$output): void
    {
        $output[] = "Installing NPM dependencies...";
        $result = Process::path($rootPath)->run('npm install');
        $output[] = $result->output();

        $output[] = "Building project...";
        $result = Process::path($rootPath)->run('npm run build');
        $output[] = $result->output();

        $output[] = "Restarting PM2...";
        Process::path($rootPath)->run("pm2 restart {$site->domain} || pm2 start npm --name {$site->domain} -- start");
    }

    protected function deployPython(Site $site, string $rootPath, array &$output): void
    {
        $output[] = "Installing Python dependencies...";

        if (File::exists("{$rootPath}/requirements.txt")) {
            $result = Process::path($rootPath)->run('pip3 install -r requirements.txt');
            $output[] = $result->output();
        }

        $output[] = "Restarting service...";
        Process::run("systemctl restart {$site->domain}");
    }

    protected function deployStatic(Site $site, string $rootPath, array &$output): void
    {
        $output[] = "Static site deployed successfully.";
    }
}

