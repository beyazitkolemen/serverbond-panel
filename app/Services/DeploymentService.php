<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DeploymentStatus;
use App\Enums\DeploymentTrigger;
use App\Enums\SiteStatus;
use App\Models\Database;
use App\Models\Deployment;
use App\Models\DeploymentLog;
use App\Models\Site;
use Illuminate\Process\ProcessResult;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class DeploymentService
{
    public function __construct(
        private readonly MySQLService $mySQLService,
        private readonly EnvironmentService $environmentService,
        private readonly CloudflareService $cloudflareService,
    ) {}

    public function deploy(Site $site, DeploymentTrigger $trigger = DeploymentTrigger::Manual, ?int $userId = null): Deployment
    {
        $deployment = $this->initializeDeployment($site, $trigger, $userId);
        $output = [];

        try {
            $deployment->update(['status' => DeploymentStatus::Running]);
            $this->logDeployment($site, $deployment, 'info', 'Deployment başlatıldı');

            $this->executeDeployment($site, $deployment, $output);

            $this->finalizeSuccessfulDeployment($deployment, $site, $output);
            $this->logDeployment($site, $deployment, 'success', 'Deployment başarıyla tamamlandı');
        } catch (Throwable $e) {
            $this->finalizeFailedDeployment($deployment, $site, $output, $e);
            $this->logDeployment($site, $deployment, 'error', 'Deployment başarısız: ' . $e->getMessage());
            throw $e;
        }

        return $deployment->fresh();
    }

    protected function executeDeployment(Site $site, Deployment $deployment, array &$output): void
    {
        $rootPath = $this->getSiteRootPath($site);

        // 1. Git operations
        if ($site->git_repository) {
            $this->log($output, 'Updating repository...', $site, $deployment, 'info');
            $this->updateRepository($site, $deployment, $rootPath, $output);
        }

        // 2. Database provision
        $this->log($output, 'Provisioning database...', $site, $deployment, 'info');
        $this->provisionDatabase($site, $deployment, $output);

        // 3. Environment file
        $this->log($output, 'Syncing environment...', $site, $deployment, 'info');
        $this->syncEnvironmentFile($site, $deployment, $rootPath, $output);

        // 4. Deployment script
        if ($site->deployment_script) {
            $this->log($output, 'Running deployment script...', $site, $deployment, 'info');
            $this->executeDeploymentScript($site, $deployment, $rootPath, $output);
        }

        // 5. Cloudflare tunnel
        if ($site->cloudflare_tunnel_enabled && $site->cloudflare_tunnel_token) {
            $this->log($output, 'Starting Cloudflare tunnel...', $site, $deployment, 'info');
            $this->startTunnel($site, $deployment, $output);
        }
    }

    protected function provisionDatabase(Site $site, Deployment $deployment, array &$output): void
    {
        // Skip if credentials already exist
        if ($this->hasDatabaseCredentials($site)) {
            $this->log($output, "✓ Using existing database: {$site->database_name}", $site, $deployment, 'info');
            return;
        }

        try {
            $result = $this->mySQLService->createDatabaseForSite($site);

            if (!$result['success']) {
                $this->log($output, "✗ Database creation failed: {$result['error']}", $site, $deployment, 'error');
                return;
            }

            $site->refresh();

            $this->log($output, "✓ Database: {$result['database']} (user: {$result['user']})", $site, $deployment, 'success');

            // Register in database panel
            Database::updateOrCreate(
                ['name' => $result['database']],
                [
                    'username' => $result['user'],
                    'password' => $result['password'],
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'site_id' => $site->id,
                    'notes' => 'Auto-created during deployment',
                ]
            );

            $this->log($output, '✓ Database registered', $site, $deployment, 'success');
        } catch (Throwable $e) {
            $this->log($output, "⚠ Database provision skipped: {$e->getMessage()}", $site, $deployment, 'warning');
            Log::warning('Database provision failed', ['site_id' => $site->id, 'error' => $e->getMessage()]);
        }
    }

    protected function updateRepository(Site $site, Deployment $deployment, string $rootPath, array &$output): void
    {
        $this->ensureDirectories($rootPath, $output, $site, $deployment);

        $this->withGitEnvironment($site, function (array $env) use ($site, $deployment, $rootPath, &$output) {
            $isFirstDeployment = !File::exists($rootPath . '/.git');

            if ($isFirstDeployment) {
                $this->cloneRepository($site, $deployment, $rootPath, $env, $output);
            } else {
                $this->pullRepository($site, $deployment, $rootPath, $env, $output);
            }

            $this->captureCommitInfo($deployment, $site, $rootPath, $env, $output);
        });
    }

    protected function cloneRepository(Site $site, Deployment $deployment, string $rootPath, array $env, array &$output): void
    {
        File::cleanDirectory($rootPath);
        $this->log($output, 'Cloning repository...', $site, $deployment, 'info');

        $this->exec(
            sprintf('git clone -b %s %s .', escapeshellarg($this->getBranch($site)), escapeshellarg($site->git_repository)),
            $rootPath,
            $env,
            $output,
            $site,
            $deployment
        );
    }

    protected function pullRepository(Site $site, Deployment $deployment, string $rootPath, array $env, array &$output): void
    {
        $this->log($output, 'Pulling latest changes...', $site, $deployment, 'info');

        $this->exec(
            sprintf('git pull origin %s', escapeshellarg($this->getBranch($site))),
            $rootPath,
            $env,
            $output,
            $site,
            $deployment
        );
    }

    protected function captureCommitInfo(Deployment $deployment, Site $site, string $rootPath, array $env, array &$output): void
    {
        $commitHash = trim($this->exec('git rev-parse HEAD', $rootPath, $env, $output, $site, $deployment)->output());

        if ($commitHash) {
            $this->log($output, 'Commit: ' . substr($commitHash, 0, 7), $site, $deployment, 'info');
        }

        $commitMessage = trim($this->exec('git log -1 --pretty=%B', $rootPath, $env, $output, $site, $deployment)->output());
        $commitAuthor = trim($this->exec('git log -1 --pretty=format:"%an <%ae>"', $rootPath, $env, $output, $site, $deployment)->output());

        $deployment->update([
            'commit_hash' => $commitHash,
            'commit_message' => $commitMessage,
            'commit_author' => $commitAuthor,
        ]);
    }

    protected function executeDeploymentScript(Site $site, Deployment $deployment, string $rootPath, array &$output): void
    {
        $scriptPath = $rootPath . '/' . config('deployment.paths.script_name');

        File::put($scriptPath, $site->deployment_script);
        chmod($scriptPath, config('deployment.script_permissions'));

        try {
            $result = Process::path($rootPath)
                ->timeout(config('deployment.timeout'))
                ->run('bash ' . config('deployment.paths.script_name'));

            // Önce output'u yakala
            $this->captureOutput($result, $output, $site, $deployment);

            if (!$result->successful()) {
                $exitCode = $result->exitCode();
                $stdOut = trim($result->output());
                $stdErr = trim($result->errorOutput());

                // Son birkaç satırı al (en önemli hata genelde sonda)
                $lastLines = array_slice(explode("\n", $stdOut ?: $stdErr), -5);
                $errorSummary = implode("\n", $lastLines);

                $error = sprintf(
                    "Script failed (exit code: %d)\nLast output:\n%s",
                    $exitCode,
                    $errorSummary ?: 'No output'
                );

                $this->log($output, "✗ {$error}", $site, $deployment, 'error', [
                    'exit_code' => $exitCode,
                    'last_lines' => $lastLines,
                ]);

                throw new RuntimeException($error);
            }

            $this->log($output, '✓ Deployment script completed', $site, $deployment, 'success');
        } finally {
            File::delete($scriptPath);
        }
    }

    protected function syncEnvironmentFile(Site $site, Deployment $deployment, string $rootPath, array &$output): void
    {
        $this->environmentService->synchronizeEnvironmentFile(
            $site,
            $rootPath,
            fn(string $msg) => $this->log($output, $msg, $site, $deployment, 'info')
        );
    }

    protected function startTunnel(Site $site, Deployment $deployment, array &$output): void
    {
        $result = $this->cloudflareService->runTunnelWithToken($site);

        $message = $result['success']
            ? '✓ Cloudflare tunnel started'
            : "✗ Tunnel failed: {$result['error']}";

        $level = $result['success'] ? 'success' : 'error';

        $this->log($output, $message, $site, $deployment, $level);
    }

    protected function withGitEnvironment(Site $site, callable $callback): mixed
    {
        $environment = [];
        $deployKeyPath = null;

        if ($site->git_deploy_key) {
            $deployKeyPath = $this->createDeployKey($site);
            $environment['GIT_SSH_COMMAND'] = "ssh -i {$deployKeyPath} -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null";
        }

        try {
            return $callback($environment);
        } finally {
            if ($deployKeyPath && File::exists($deployKeyPath)) {
                File::delete($deployKeyPath);
            }
        }
    }

    protected function exec(string $command, string $cwd, array $env, array &$output, Site $site, Deployment $deployment): ProcessResult
    {
        $process = Process::path($cwd);

        if (!empty($env)) {
            $process = $process->env($env);
        }

        $result = $process->run($command);

        $this->captureOutput($result, $output, $site, $deployment);

        if (!$result->successful()) {
            $error = $result->errorOutput() ?: 'Command failed';
            $this->log($output, "✗ Command failed: {$command}", $site, $deployment, 'error', ['error' => $error]);
            throw new RuntimeException($error);
        }

        return $result;
    }

    protected function initializeDeployment(Site $site, DeploymentTrigger $trigger, ?int $userId): Deployment
    {
        $site->update(['status' => SiteStatus::Deploying]);

        return Deployment::create([
            'site_id' => $site->id,
            'user_id' => $userId,
            'status' => DeploymentStatus::Pending,
            'trigger' => $trigger,
            'started_at' => now(),
        ]);
    }

    protected function finalizeSuccessfulDeployment(Deployment $deployment, Site $site, array $output): void
    {
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
    }

    protected function finalizeFailedDeployment(Deployment $deployment, Site $site, array $output, Throwable $e): void
    {
        $deployment->update([
            'status' => DeploymentStatus::Failed,
            'output' => $this->formatOutput($output),
            'error' => $e->getMessage(),
            'finished_at' => now(),
            'duration' => now()->diffInSeconds($deployment->started_at),
        ]);

        $site->update(['status' => SiteStatus::Error]);
    }

    protected function ensureDirectories(string $rootPath, array &$output, Site $site, Deployment $deployment): void
    {
        $parentDirectory = dirname($rootPath);

        if (!File::exists($parentDirectory)) {
            File::makeDirectory($parentDirectory, config('deployment.directory_permissions'), true);
            $this->log($output, "Created parent directory: {$parentDirectory}", $site, $deployment, 'info');
        }

        if (!File::exists($rootPath)) {
            File::makeDirectory($rootPath, config('deployment.directory_permissions'), true);
            $this->log($output, "Created site directory: {$rootPath}", $site, $deployment, 'info');
        }
    }

    protected function createDeployKey(Site $site): string
    {
        $keyPath = config('deployment.paths.deploy_keys') . '/deploy-key-' . $site->id . '-' . Str::random(16);

        File::ensureDirectoryExists(dirname($keyPath));
        File::put($keyPath, $site->git_deploy_key);
        chmod($keyPath, config('deployment.deploy_key_permissions'));

        return $keyPath;
    }

    protected function captureOutput(ProcessResult $result, array &$output, Site $site, Deployment $deployment): void
    {
        if ($stdOut = trim($result->output())) {
            $this->log($output, $stdOut, $site, $deployment, 'info');
        }

        if ($stdErr = trim($result->errorOutput())) {
            $this->log($output, $stdErr, $site, $deployment, 'warning');
        }
    }

    protected function formatOutput(array $output): string
    {
        return implode("\n", array_filter(array_map('trim', $output)));
    }

    protected function log(array &$output, string $message, ?Site $site = null, ?Deployment $deployment = null, string $level = 'info', ?array $context = null): void
    {
        $output[] = $message;

        if ($site) {
            DeploymentLog::log(
                siteId: $site->id,
                level: $level,
                message: $message,
                deploymentId: $deployment?->id,
                context: $context
            );
        }
    }

    protected function logDeployment(Site $site, Deployment $deployment, string $level, string $message, ?array $context = null): void
    {
        DeploymentLog::log(
            siteId: $site->id,
            level: $level,
            message: $message,
            deploymentId: $deployment->id,
            context: $context
        );
    }

    protected function getSiteRootPath(Site $site): string
    {
        return rtrim($site->root_directory, '/') . '/' . $site->domain;
    }

    protected function getBranch(Site $site): string
    {
        return $site->git_branch ?: config('deployment.git.default_branch');
    }

    protected function hasDatabaseCredentials(Site $site): bool
    {
        return !empty($site->database_name)
            && !empty($site->database_user)
            && !empty($site->database_password);
    }
}
