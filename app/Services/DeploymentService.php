<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DeploymentStatus;
use App\Enums\DeploymentTrigger;
use App\Enums\SiteStatus;
use App\Models\Deployment;
use App\Models\Site;
use App\Services\EnvironmentService;
use Illuminate\Process\ProcessResult;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Throwable;

class DeploymentService
{
    public function __construct(
        private readonly EnvironmentService $environmentService,
    ) {}

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

        $output = [];

        try {
            $deployment->update(['status' => DeploymentStatus::Running]);

            $this->runDeployment($site, $deployment, $output);

            $deployment->update([
                'status' => DeploymentStatus::Success,
                'output' => $this->formatOutput($output),
                'finished_at' => now(),
                'duration' => now()->diffInSeconds($deployment->started_at),
            ]);

            $site->update([
                'status' => SiteStatus::Active,
                'last_deployed_at' => now(),
            ]);
        } catch (Throwable $e) {
            $deployment->update([
                'status' => DeploymentStatus::Failed,
                'output' => $this->formatOutput($output),
                'error' => $e->getMessage(),
                'finished_at' => now(),
                'duration' => now()->diffInSeconds($deployment->started_at),
            ]);

            $site->update(['status' => SiteStatus::Error]);

            throw $e;
        }

        return $deployment->fresh();
    }

    protected function runDeployment(Site $site, Deployment $deployment, array &$output): void
    {
        $rootPath = rtrim($site->root_directory, '/') . '/' . $site->domain;

        if ($site->git_repository) {
            $this->updateRepository($site, $deployment, $rootPath, $output);
        }

        $this->appendOutput($output, 'Creating/updating .env file...');

        $this->synchronizeEnvironmentFile($site, $rootPath, $output);

        if ($site->deployment_script) {
            $this->appendOutput($output, 'Running deployment script...');
            $this->runDeploymentScript($site, $rootPath, $output);
        }
    }

    protected function runDeploymentScript(Site $site, string $rootPath, array &$output): void
    {
        // Create temporary script file
        $scriptPath = $rootPath . '/' . config('deployment.paths.script_name');
        File::put($scriptPath, $site->deployment_script);

        // Make script executable
        chmod($scriptPath, config('deployment.script_permissions'));

        try {
            // Run the deployment script
            $result = Process::path($rootPath)
                ->timeout(config('deployment.timeout'))
                ->run('bash ' . config('deployment.paths.script_name'));

            $this->captureProcessStreams($result, $output);

            if (!$result->successful()) {
                throw new \RuntimeException('Deployment script failed: ' . trim($result->errorOutput()));
            }
        } finally {
            // Clean up script file
            if (File::exists($scriptPath)) {
                File::delete($scriptPath);
            }
        }
    }

    protected function updateRepository(Site $site, Deployment $deployment, string $rootPath, array &$output): void
    {
        $parentDirectory = dirname($rootPath);

        if (!File::exists($parentDirectory)) {
            File::makeDirectory($parentDirectory, config('deployment.directory_permissions'), true);
            $this->appendOutput($output, "Creating parent directory: {$parentDirectory}");
        }

        if (!File::exists($rootPath)) {
            File::makeDirectory($rootPath, config('deployment.directory_permissions'), true);
            $this->appendOutput($output, "Preparing site directory: {$rootPath}");
        }

        $branch = $this->resolveBranch($site);

        $this->withGitEnvironment($site, function (array $environment) use ($site, $deployment, $rootPath, $branch, &$output) {
            $gitDirectory = $rootPath . '/.git';

            if (!File::exists($gitDirectory)) {
                File::cleanDirectory($rootPath);
                $this->appendOutput($output, 'Cloning repository...');

                $this->runProcess(
                    sprintf(
                        'git clone -b %s %s .',
                        escapeshellarg($branch),
                        escapeshellarg($site->git_repository)
                    ),
                    $rootPath,
                    $output,
                    'Git clone failed',
                    $environment
                );
            } else {
                $this->appendOutput($output, 'Pulling latest changes...');

                $this->runProcess(
                    sprintf('git pull origin %s', escapeshellarg($branch)),
                    $rootPath,
                    $output,
                    'Git pull failed',
                    $environment
                );
            }

            $commitHashResult = $this->runProcess(
                'git rev-parse HEAD',
                $rootPath,
                $output,
                'Unable to determine commit hash',
                $environment
            );

            $commitHash = trim($commitHashResult->output());

            if ($commitHash !== '') {
                $this->appendOutput($output, 'Checked out commit ' . substr($commitHash, 0, 7));
            }

            $commitMessage = trim(
                $this->runProcess(
                    'git log -1 --pretty=%B',
                    $rootPath,
                    $output,
                    'Unable to read commit message',
                    $environment
                )->output()
            );

            $commitAuthor = trim(
                $this->runProcess(
                    'git log -1 --pretty=format:"%an <%ae>"',
                    $rootPath,
                    $output,
                    'Unable to read commit author',
                    $environment
                )->output()
            );

            $deployment->update([
                'commit_hash' => $commitHash,
                'commit_message' => $commitMessage,
                'commit_author' => $commitAuthor,
            ]);
        });
    }

    protected function synchronizeEnvironmentFile(Site $site, string $rootPath, array &$output): void
    {
        $this->environmentService->synchronizeEnvironmentFile(
            $site,
            $rootPath,
            function (string $message) use (&$output): void {
                $this->appendOutput($output, $message);
            }
        );
    }

    protected function runProcess(
        string|array $command,
        string $workingDirectory,
        array &$output,
        string $failureMessage,
        array $environment = []
    ): ProcessResult {
        $pendingProcess = Process::path($workingDirectory);

        if (!empty($environment)) {
            $pendingProcess = $pendingProcess->env($environment);
        }

        $result = $pendingProcess->run($command);

        $this->captureProcessStreams($result, $output);

        if (!$result->successful()) {
            $error = trim($result->errorOutput());

            throw new \RuntimeException(trim($failureMessage . ($error !== '' ? ': ' . $error : '')));
        }

        return $result;
    }

    protected function captureProcessStreams(ProcessResult $result, array &$output): void
    {
        $stdOut = trim($result->output());
        if ($stdOut !== '') {
            $this->appendOutput($output, $stdOut);
        }

        $stdErr = trim($result->errorOutput());
        if ($stdErr !== '') {
            $this->appendOutput($output, $stdErr);
        }
    }

    protected function withGitEnvironment(Site $site, callable $callback): mixed
    {
        $environment = [];
        $deployKeyPath = null;

        if ($site->git_deploy_key) {
            $deployKeyPath = config('deployment.paths.deploy_keys') . '/deploy-key-' . $site->id . '-' . Str::random(16);

            File::ensureDirectoryExists(dirname($deployKeyPath));
            File::put($deployKeyPath, $site->git_deploy_key);
            chmod($deployKeyPath, config('deployment.deploy_key_permissions'));

            $environment['GIT_SSH_COMMAND'] = sprintf(
                'ssh -i %s -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null',
                escapeshellarg($deployKeyPath)
            );
        }

        try {
            return $callback($environment);
        } finally {
            if ($deployKeyPath && File::exists($deployKeyPath)) {
                File::delete($deployKeyPath);
            }
        }
    }

    protected function formatOutput(array $output): string
    {
        $filtered = array_filter(array_map(fn ($line) => trim((string) $line), $output), fn ($line) => $line !== '');

        return implode("\n", $filtered);
    }

    protected function appendOutput(array &$output, string $message): void
    {
        $output[] = $message;
    }

    protected function resolveBranch(Site $site): string
    {
        $branch = trim((string) $site->git_branch);

        return $branch !== '' ? $branch : config('deployment.git.default_branch');
    }
}
