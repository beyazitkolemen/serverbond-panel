<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Site;
use Illuminate\Support\Facades\File;
use RuntimeException;

class EnvironmentService
{
    public function __construct(
        private readonly MySQLService $mySQLService,
    ) {}

    public function synchronizeEnvironmentFile(Site $site, string $rootPath, ?callable $outputCallback = null): void
    {
        File::ensureDirectoryExists($rootPath);

        $credentials = $this->provisionDatabase($site, $outputCallback);
        $envContent = $this->resolveBaseEnvironmentContent($site, $rootPath, $outputCallback);
        $envContent = $this->applyDatabaseConfiguration($envContent, $credentials);

        File::put($rootPath . '/.env', $envContent);

        $this->notify($outputCallback, '.env file synchronized successfully.');
    }

    private function provisionDatabase(Site $site, ?callable $outputCallback): array
    {
        // Eğer database oluşturma kapalıysa veya bilgiler yoksa boş dön
        if (!$site->database_name && !$site->database_user) {
            $this->notify($outputCallback, 'Database provisioning skipped (not configured).');

            return [
                'connection' => config('deployment.database.connection'),
                'host' => config('deployment.database.host'),
                'port' => config('deployment.database.port'),
                'database' => '',
                'username' => '',
                'password' => '',
            ];
        }

        $this->notify($outputCallback, 'Ensuring MySQL database is provisioned...');

        $result = $this->mySQLService->createDatabaseForSite($site);

        if (($result['success'] ?? false) === true) {
            $site->fill([
                'database_name' => $result['database'],
                'database_user' => $result['user'],
                'database_password' => $result['password'],
            ]);

            if ($site->isDirty(['database_name', 'database_user', 'database_password'])) {
                $site->save();
                $site->refresh();
            }

            $this->notify(
                $outputCallback,
                sprintf('Database ready (name: %s, user: %s).', $result['database'], $result['user'])
            );

            return [
                'connection' => config('deployment.database.connection'),
                'host' => config('deployment.database.host'),
                'port' => config('deployment.database.port'),
                'database' => $result['database'],
                'username' => $result['user'],
                'password' => $result['password'],
            ];
        }

        if ($site->database_name && $site->database_user && $site->database_password) {
            $this->notify($outputCallback, 'Using existing database credentials stored for the site.');

            // Site'den oku - database_password attribute'ü otomatik decrypt eder
            return [
                'connection' => config('deployment.database.connection'),
                'host' => config('deployment.database.host'),
                'port' => config('deployment.database.port'),
                'database' => $site->database_name,
                'username' => $site->database_user,
                'password' => $site->database_password, // Attribute sayesinde decrypt edilmiş
            ];
        }

        $message = $result['error'] ?? 'MySQL veritabanı oluşturulamadı.';

        throw new RuntimeException($message);
    }

    private function resolveBaseEnvironmentContent(Site $site, string $rootPath, ?callable $outputCallback): string
    {
        $envPath = $rootPath . '/.env';
        $envExamplePath = $rootPath . '/.env.example';

        if (File::exists($envPath)) {
            $this->notify($outputCallback, 'Existing .env file found, merging configuration.');

            return (string) File::get($envPath);
        }

        if (File::exists($envExamplePath)) {
            $this->notify($outputCallback, 'Using .env.example as base configuration.');

            return (string) File::get($envExamplePath);
        }

        $defaultContent = $site->getDefaultEnvContent();

        if ($defaultContent !== '') {
            $this->notify($outputCallback, 'Generated environment file from default template.');

            return $defaultContent;
        }

        $this->notify($outputCallback, 'No environment template found, starting with empty configuration.');

        return '';
    }

    private function applyDatabaseConfiguration(string $envContent, array $credentials): string
    {
        // Sadece database bilgileri varsa yaz
        if (!empty($credentials['database']) && !empty($credentials['username'])) {
            $envContent = $this->setEnvValue($envContent, 'DB_CONNECTION', $credentials['connection']);
            $envContent = $this->setEnvValue($envContent, 'DB_HOST', $credentials['host']);
            $envContent = $this->setEnvValue($envContent, 'DB_PORT', $credentials['port']);
            $envContent = $this->setEnvValue($envContent, 'DB_DATABASE', $credentials['database']);
            $envContent = $this->setEnvValue($envContent, 'DB_USERNAME', $credentials['username']);
            $envContent = $this->setEnvValue($envContent, 'DB_PASSWORD', $credentials['password'] ?? '');
        }

        return $envContent;
    }

    private function setEnvValue(string $content, string $key, string $value): string
    {
        $normalizedValue = $this->normalizeEnvValue($value);
        $pattern = '/^' . preg_quote($key, '/') . '=.*/m';

        if (preg_match($pattern, $content) === 1) {
            return (string) preg_replace($pattern, $key . '=' . $normalizedValue, $content);
        }

        $content = rtrim($content);
        if ($content !== '') {
            $content .= PHP_EOL;
        }

        return $content . $key . '=' . $normalizedValue . PHP_EOL;
    }

    private function normalizeEnvValue(string $value): string
    {
        if ($value === '') {
            return '""';
        }

        if (preg_match('/^[A-Za-z0-9_\-]+$/', $value) === 1) {
            return $value;
        }

        return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
    }

    private function notify(?callable $callback, string $message): void
    {
        if ($callback !== null) {
            $callback($message);
        }
    }
}
