<?php

declare(strict_types=1);

namespace App\Services;

class GitService
{
    /**
     * Git repository'nin default branch'ini tespit eder
     */
    public function detectDefaultBranch(string $repositoryUrl): string
    {
        // Repository URL'i boşsa varsayılan dön
        if (empty($repositoryUrl)) {
            return config('deployment.git.default_branch');
        }

        // GitHub, GitLab, Bitbucket gibi servislerde API ile branch bilgisini çek
        try {
            // URL'den repository bilgilerini çıkar
            $parsedUrl = $this->parseGitUrl($repositoryUrl);

            if (!$parsedUrl) {
                return config('deployment.git.default_branch');
            }

            // GitHub için API kontrolü
            if (str_contains($repositoryUrl, 'github.com')) {
                $branch = $this->detectGitHubDefaultBranch($parsedUrl['owner'], $parsedUrl['repo']);
                if ($branch) {
                    return $branch;
                }
            }

            // GitLab için API kontrolü
            if (str_contains($repositoryUrl, 'gitlab.com')) {
                $branch = $this->detectGitLabDefaultBranch($parsedUrl['owner'], $parsedUrl['repo']);
                if ($branch) {
                    return $branch;
                }
            }

            // API başarısız olduysa git ls-remote ile kontrol et
            $branch = $this->detectBranchViaGit($repositoryUrl);
            if ($branch) {
                return $branch;
            }
        } catch (\Exception $e) {
            // Hata durumunda varsayılan
            \Log::warning('Git branch detection failed', [
                'url' => $repositoryUrl,
                'error' => $e->getMessage(),
            ]);

            return config('deployment.git.default_branch');
        }

        return config('deployment.git.default_branch');
    }

    /**
     * Git URL'den owner ve repo bilgilerini çıkarır
     */
    protected function parseGitUrl(string $url): ?array
    {
        // GitHub HTTPS URL: https://github.com/user/repo.git
        // GitHub SSH URL: git@github.com:user/repo.git
        if (preg_match('#github\.com[:/]([^/]+)/([^/\.]+)#', $url, $matches)) {
            return ['owner' => $matches[1], 'repo' => $matches[2]];
        }

        // GitLab HTTPS URL: https://gitlab.com/user/repo.git
        // GitLab SSH URL: git@gitlab.com:user/repo.git
        if (preg_match('#gitlab\.com[:/]([^/]+)/([^/\.]+)#', $url, $matches)) {
            return ['owner' => $matches[1], 'repo' => $matches[2]];
        }

        // Bitbucket HTTPS URL: https://bitbucket.org/user/repo.git
        if (preg_match('#bitbucket\.org[:/]([^/]+)/([^/\.]+)#', $url, $matches)) {
            return ['owner' => $matches[1], 'repo' => $matches[2]];
        }

        return null;
    }

    /**
     * GitHub API ile default branch'i tespit eder
     */
    protected function detectGitHubDefaultBranch(string $owner, string $repo): ?string
    {
        try {
            $url = "https://api.github.com/repos/{$owner}/{$repo}";

            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => "User-Agent: " . config('deployment.git.user_agent') . "\r\n",
                    'timeout' => config('deployment.git.api_timeout'),
                ],
            ]);

            $response = @file_get_contents($url, false, $context);

            if ($response === false) {
                return null;
            }

            $data = json_decode($response, true);

            return $data['default_branch'] ?? null;
        } catch (\Exception $e) {
            \Log::warning('GitHub API branch detection failed', [
                'owner' => $owner,
                'repo' => $repo,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * GitLab API ile default branch'i tespit eder
     */
    protected function detectGitLabDefaultBranch(string $owner, string $repo): ?string
    {
        try {
            $projectPath = urlencode("{$owner}/{$repo}");
            $url = "https://gitlab.com/api/v4/projects/{$projectPath}";

            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => "User-Agent: " . config('deployment.git.user_agent') . "\r\n",
                    'timeout' => config('deployment.git.api_timeout'),
                ],
            ]);

            $response = @file_get_contents($url, false, $context);

            if ($response === false) {
                return null;
            }

            $data = json_decode($response, true);

            return $data['default_branch'] ?? null;
        } catch (\Exception $e) {
            \Log::warning('GitLab API branch detection failed', [
                'owner' => $owner,
                'repo' => $repo,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Git ls-remote komutu ile default branch'i tespit eder
     */
    protected function detectBranchViaGit(string $repositoryUrl): ?string
    {
        try {
            // Public repository için git ls-remote kullan
            $command = "git ls-remote --symref " . escapeshellarg($repositoryUrl) . " HEAD 2>&1";
            $output = @shell_exec($command);

            if (empty($output)) {
                return null;
            }

            // Output: ref: refs/heads/main	HEAD
            if (preg_match('#ref: refs/heads/([^\s]+)#', $output, $matches)) {
                return $matches[1];
            }
        } catch (\Exception $e) {
            \Log::warning('Git ls-remote branch detection failed', [
                'url' => $repositoryUrl,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        return null;
    }

    /**
     * Repository'nin tüm branch'lerini listeler
     */
    public function listRemoteBranches(string $repositoryUrl): array
    {
        try {
            $command = "git ls-remote --heads " . escapeshellarg($repositoryUrl) . " 2>&1";
            $output = @shell_exec($command);

            if (empty($output)) {
                return [];
            }

            $branches = [];
            $lines = explode("\n", trim($output));

            foreach ($lines as $line) {
                // Format: <hash>	refs/heads/<branch-name>
                if (preg_match('#refs/heads/([^\s]+)#', $line, $matches)) {
                    $branches[] = $matches[1];
                }
            }

            return $branches;
        } catch (\Exception $e) {
            \Log::warning('Git branch listing failed', [
                'url' => $repositoryUrl,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Repository'nin erişilebilir olup olmadığını kontrol eder
     */
    public function isRepositoryAccessible(string $repositoryUrl): bool
    {
        try {
            $command = "git ls-remote " . escapeshellarg($repositoryUrl) . " HEAD 2>&1";
            $output = @shell_exec($command);

            return !empty($output) && !str_contains($output, 'fatal');
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Repository URL'den proje adını çıkarır
     */
    public function extractProjectName(string $repositoryUrl): ?string
    {
        $parsed = $this->parseGitUrl($repositoryUrl);

        if ($parsed) {
            return $parsed['repo'];
        }

        // Genel URL'lerden proje adını çıkarmaya çalış
        if (preg_match('#/([^/]+?)(?:\.git)?$#', $repositoryUrl, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
