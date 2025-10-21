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

        // Önce database provision et
        $credentials = $this->provisionDatabase($site, $outputCallback);

        // Site'yi refresh et (database bilgileri güncellenmiş olabilir)
        $site->refresh();

        // Base content al
        $envContent = $this->resolveBaseEnvironmentContent($site, $rootPath, $outputCallback);

        // Database config uygula
        $envContent = $this->applyDatabaseConfiguration($envContent, $credentials);

        // .env dosyasına yaz
        File::put($rootPath . '/.env', $envContent);

        $this->notify($outputCallback, '.env file synchronized successfully.');
    }

    private function provisionDatabase(Site $site, ?callable $outputCallback): array
    {
        $this->notify($outputCallback, 'Provisioning MySQL database...');

        $result = $this->mySQLService->createDatabaseForSite($site);

        if (($result['success'] ?? false) === true) {
            $this->notify(
                $outputCallback,
                sprintf('✓ Database ready: %s@%s (password: %d chars)',
                    $result['user'],
                    $result['database'],
                    strlen($result['password'])
                )
            );

            // Plain text credentials döndür (MySQL'de kullanılan)
            return [
                'connection' => config('deployment.database.connection'),
                'host' => config('deployment.database.host'),
                'port' => config('deployment.database.port'),
                'database' => $result['database'],
                'username' => $result['user'],
                'password' => $result['password'], // Plain text
            ];
        }

        // MySQLService başarısız oldu, hata ver
        $message = $result['error'] ?? 'MySQL veritabanı oluşturulamadı.';

        $this->notify($outputCallback, '✗ Database provision failed: ' . $message);

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
        \Log::info('EnvironmentService: Applying database config', [
            'credentials' => [
                'connection' => $credentials['connection'] ?? 'N/A',
                'host' => $credentials['host'] ?? 'N/A',
                'port' => $credentials['port'] ?? 'N/A',
                'database' => $credentials['database'] ?? 'EMPTY',
                'username' => $credentials['username'] ?? 'EMPTY',
                'password_length' => isset($credentials['password']) ? strlen($credentials['password']) : 0,
            ]
        ]);

        // Database bilgileri var mı kontrol et
        if (empty($credentials['database']) || empty($credentials['username'])) {
            \Log::warning('EnvironmentService: Database config skipped - missing credentials', [
                'database_empty' => empty($credentials['database']),
                'username_empty' => empty($credentials['username']),
            ]);
            return $envContent;
        }

        // Password kontrolü
        if (empty($credentials['password'])) {
            \Log::error('EnvironmentService: Password is empty!');
        }

        // Her bir değeri tek tek yaz ve log tut
        $envContent = $this->setEnvValue($envContent, 'DB_CONNECTION', $credentials['connection']);
        $envContent = $this->setEnvValue($envContent, 'DB_HOST', $credentials['host']);
        $envContent = $this->setEnvValue($envContent, 'DB_PORT', (string) $credentials['port']);
        $envContent = $this->setEnvValue($envContent, 'DB_DATABASE', $credentials['database']);
        $envContent = $this->setEnvValue($envContent, 'DB_USERNAME', $credentials['username']);
        $envContent = $this->setEnvValue($envContent, 'DB_PASSWORD', $credentials['password'] ?? '');

        \Log::info('EnvironmentService: Database config applied successfully');

        return $envContent;
    }

    private function setEnvValue(string $content, string $key, string $value): string
    {
        $normalizedValue = $this->normalizeEnvValue($value);

        // Daha esnek pattern - boşluk, comment vb. yakalar
        $pattern = '/^[\s]*' . preg_quote($key, '/') . '[\s]*=.*$/m';

        // Eğer key mevcutsa değiştir
        if (preg_match($pattern, $content) === 1) {
            $replaced = (string) preg_replace($pattern, $key . '=' . $normalizedValue, $content);
            \Log::debug("ENV: Updated {$key}", ['value' => $normalizedValue]);
            return $replaced;
        }

        // Key yoksa - DB_ ile başlayan section'ı bul ve orada ekle
        if (str_starts_with($key, 'DB_')) {
            // DB section'ını bul (son DB_ değerinden sonra)
            $dbSectionPattern = '/(DB_[A-Z_]+\s*=.*?)(\n\s*\n)/s';

            if (preg_match($dbSectionPattern, $content)) {
                // DB section'ı sonuna ekle
                $replacement = "$1\n" . $key . '=' . $normalizedValue . "$2";
                $newContent = preg_replace($dbSectionPattern, $replacement, $content, 1);

                if ($newContent !== null && $newContent !== $content) {
                    \Log::debug("ENV: Added {$key} to DB section", ['value' => $normalizedValue]);
                    return $newContent;
                }
            }
        }

        // Hiçbir yerde bulunamadıysa sona ekle
        $content = rtrim($content);
        if ($content !== '') {
            $content .= PHP_EOL;
        }

        \Log::debug("ENV: Added {$key} to end", ['value' => $normalizedValue]);
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
