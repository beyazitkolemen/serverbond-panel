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

        // Base content'i önce al (template bilgileri ile)
        $envContent = $this->resolveBaseEnvironmentContent($site, $rootPath, $outputCallback);

        // Database provision et (başarısız olsa da devam eder)
        try {
            $credentials = $this->provisionDatabase($site, $outputCallback);

            // Site'yi refresh et (database bilgileri güncellenmiş olabilir)
            $site->refresh();

            // Database config uygula
            $envContent = $this->applyDatabaseConfiguration($envContent, $credentials);
        } catch (\Exception $e) {
            $this->notify($outputCallback, '⚠ Database provision warning: ' . $e->getMessage());
            $this->notify($outputCallback, 'Continuing with existing .env values...');

            \Log::warning('EnvironmentService: Database provision failed, using template', [
                'error' => $e->getMessage(),
            ]);
        }

        // .env dosyasına yaz
        File::put($rootPath . '/.env', $envContent);

        $this->notify($outputCallback, '.env file synchronized successfully.');
    }

    private function provisionDatabase(Site $site, ?callable $outputCallback): array
    {
        // Eğer site'de zaten database bilgileri varsa, direkt onları kullan (MySQL'e bağlanma)
        if ($site->database_name && $site->database_user && $site->database_password) {
            $this->notify($outputCallback, '✓ Using existing database credentials');

            \Log::info('EnvironmentService: Using existing database credentials', [
                'database' => $site->database_name,
                'username' => $site->database_user,
                'password_length' => strlen($site->database_password),
            ]);

            return [
                'connection' => config('deployment.database.connection'),
                'host' => config('deployment.database.host'),
                'port' => config('deployment.database.port'),
                'database' => $site->database_name,
                'username' => $site->database_user,
                'password' => $site->database_password, // Plain text
            ];
        }

        // Yoksa MySQL'de yeni oluştur
        $this->notify($outputCallback, 'Creating new MySQL database...');

        $result = $this->mySQLService->createDatabaseForSite($site);

        \Log::info('EnvironmentService: MySQL provision result', [
            'success' => $result['success'] ?? false,
            'has_database' => isset($result['database']),
            'has_user' => isset($result['user']),
            'has_password' => isset($result['password']),
        ]);

        if (($result['success'] ?? false) === true) {
            $this->notify(
                $outputCallback,
                sprintf('✓ Database created: %s@%s',
                    $result['user'],
                    $result['database']
                )
            );

            // Plain text credentials döndür
            return [
                'connection' => config('deployment.database.connection'),
                'host' => config('deployment.database.host'),
                'port' => config('deployment.database.port'),
                'database' => $result['database'],
                'username' => $result['user'],
                'password' => $result['password'], // Plain text
            ];
        }

        // MySQLService başarısız oldu
        $message = $result['error'] ?? 'MySQL veritabanı oluşturulamadı.';

        $this->notify($outputCallback, '✗ Database creation failed: ' . $message);

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
            \Log::warning('EnvironmentService: Database config skipped - missing credentials');
            return $envContent;
        }

        // Database config replacements
        $replacements = [
            '/^DB_CONNECTION=.*$/m'     => 'DB_CONNECTION=' . $credentials['connection'],
            '/^#\s*DB_HOST=.*$/m'       => 'DB_HOST=' . $credentials['host'],
            '/^DB_HOST=.*$/m'           => 'DB_HOST=' . $credentials['host'],
            '/^#\s*DB_PORT=.*$/m'       => 'DB_PORT=' . $credentials['port'],
            '/^DB_PORT=.*$/m'           => 'DB_PORT=' . $credentials['port'],
            '/^#\s*DB_DATABASE=.*$/m'   => 'DB_DATABASE=' . $credentials['database'],
            '/^DB_DATABASE=.*$/m'       => 'DB_DATABASE=' . $credentials['database'],
            '/^#\s*DB_USERNAME=.*$/m'   => 'DB_USERNAME=' . $credentials['username'],
            '/^DB_USERNAME=.*$/m'       => 'DB_USERNAME=' . $credentials['username'],
            '/^#\s*DB_PASSWORD=.*$/m'   => 'DB_PASSWORD=' . ($credentials['password'] ?? ''),
            '/^DB_PASSWORD=.*$/m'       => 'DB_PASSWORD=' . ($credentials['password'] ?? ''),
        ];

        foreach ($replacements as $pattern => $replacement) {
            if (preg_match($pattern, $envContent)) {
                $envContent = (string) preg_replace($pattern, $replacement, $envContent);
                \Log::debug("ENV: Applied replacement", ['pattern' => $pattern, 'replacement' => $replacement]);
            }
        }

        \Log::info('EnvironmentService: Database config applied successfully');

        return $envContent;
    }

    private function setEnvValue(string $content, string $key, string $value): string
    {
        $normalizedValue = $this->normalizeEnvValue($value);
        $escapedKey = preg_quote($key, '/');

        // Pattern 1: Aktif key (DB_HOST=value)
        $activePattern = '/^' . $escapedKey . '=.*$/m';

        // Pattern 2: Comment'li key (# DB_HOST=value veya #DB_HOST=value)
        $commentPattern = '/^#\s*' . $escapedKey . '=.*$/m';

        // Aktif key varsa değiştir
        if (preg_match($activePattern, $content)) {
            $replaced = (string) preg_replace($activePattern, $key . '=' . $normalizedValue, $content);
            \Log::debug("ENV: Updated active {$key} = {$normalizedValue}");
            return $replaced;
        }

        // Comment'li key varsa, uncomment et ve değiştir
        if (preg_match($commentPattern, $content)) {
            $replaced = (string) preg_replace($commentPattern, $key . '=' . $normalizedValue, $content);
            \Log::debug("ENV: Uncommented {$key} = {$normalizedValue}");
            return $replaced;
        }

        // Key hiç yoksa sona ekle
        $content = rtrim($content);
        if ($content !== '') {
            $content .= PHP_EOL;
        }

        \Log::debug("ENV: Added new {$key} = {$normalizedValue}");
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
