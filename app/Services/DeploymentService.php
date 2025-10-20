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

        // 2. Run custom deployment script
        if ($site->deployment_script) {
            $output[] = "Running deployment script...";
            $this->runDeploymentScript($site, $rootPath, $output);
        }

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

    protected function runDeploymentScript(Site $site, string $rootPath, array &$output): void
    {
        // Create temporary script file
        $scriptPath = $rootPath . '/deploy-script.sh';
        File::put($scriptPath, $site->deployment_script);

        // Make script executable
        chmod($scriptPath, 0755);

        try {
            // Run the deployment script
            $result = Process::path($rootPath)
                ->timeout(600) // 10 dakika timeout
                ->run('bash deploy-script.sh');

            $output[] = $result->output();

            if (!$result->successful()) {
                throw new \Exception("Deployment script failed: " . $result->errorOutput());
            }
        } finally {
            // Clean up script file
            if (File::exists($scriptPath)) {
                File::delete($scriptPath);
            }
        }
    }
}
