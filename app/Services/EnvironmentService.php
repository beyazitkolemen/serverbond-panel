<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Site;
use Illuminate\Support\Facades\File;

class EnvironmentService
{

    public function synchronizeEnvironmentFile(Site $site, string $rootPath, ?callable $outputCallback = null): void
    {
        File::ensureDirectoryExists($rootPath);

        // Base content al (template bilgileri ile)
        $envContent = $this->resolveBaseEnvironmentContent($site, $rootPath, $outputCallback);

        // Database bilgileri site'de varsa uygula
        if ($site->database_name && $site->database_user && $site->database_password) {
            $this->notify($outputCallback, 'Applying database configuration...');

            $credentials = [
                'connection' => config('deployment.database.connection'),
                'host' => config('deployment.database.host'),
                'port' => config('deployment.database.port'),
                'database' => $site->database_name,
                'username' => $site->database_user,
                'password' => $site->database_password,
            ];

            $envContent = $this->applyDatabaseConfiguration($envContent, $credentials);
        } else {
            $this->notify($outputCallback, 'No database credentials found, using template defaults...');
        }

        // .env dosyasına yaz
        File::put($rootPath . '/.env', $envContent);

        $this->notify($outputCallback, '.env file synchronized successfully.');
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

        // Satır satır işle - daha garantili
        $lines = explode("\n", $envContent);
        $processed = [];
        $dbKeysFound = [
            'DB_CONNECTION' => false,
            'DB_HOST' => false,
            'DB_PORT' => false,
            'DB_DATABASE' => false,
            'DB_USERNAME' => false,
            'DB_PASSWORD' => false,
        ];

        foreach ($lines as $line) {
            $originalLine = $line;
            $matched = false;

            // DB_CONNECTION
            if (preg_match('/^DB_CONNECTION=/i', $line)) {
                $line = 'DB_CONNECTION=' . $credentials['connection'];
                $dbKeysFound['DB_CONNECTION'] = true;
                $matched = true;
            }
            // # DB_HOST (comment'li)
            elseif (preg_match('/^#\s*DB_HOST=/i', $line)) {
                $line = 'DB_HOST=' . $credentials['host'];
                $dbKeysFound['DB_HOST'] = true;
                $matched = true;
            }
            // DB_HOST (aktif)
            elseif (preg_match('/^DB_HOST=/i', $line)) {
                $line = 'DB_HOST=' . $credentials['host'];
                $dbKeysFound['DB_HOST'] = true;
                $matched = true;
            }
            // # DB_PORT (comment'li)
            elseif (preg_match('/^#\s*DB_PORT=/i', $line)) {
                $line = 'DB_PORT=' . $credentials['port'];
                $dbKeysFound['DB_PORT'] = true;
                $matched = true;
            }
            // DB_PORT (aktif)
            elseif (preg_match('/^DB_PORT=/i', $line)) {
                $line = 'DB_PORT=' . $credentials['port'];
                $dbKeysFound['DB_PORT'] = true;
                $matched = true;
            }
            // # DB_DATABASE (comment'li)
            elseif (preg_match('/^#\s*DB_DATABASE=/i', $line)) {
                $line = 'DB_DATABASE=' . $credentials['database'];
                $dbKeysFound['DB_DATABASE'] = true;
                $matched = true;
            }
            // DB_DATABASE (aktif)
            elseif (preg_match('/^DB_DATABASE=/i', $line)) {
                $line = 'DB_DATABASE=' . $credentials['database'];
                $dbKeysFound['DB_DATABASE'] = true;
                $matched = true;
            }
            // # DB_USERNAME (comment'li)
            elseif (preg_match('/^#\s*DB_USERNAME=/i', $line)) {
                $line = 'DB_USERNAME=' . $credentials['username'];
                $dbKeysFound['DB_USERNAME'] = true;
                $matched = true;
            }
            // DB_USERNAME (aktif)
            elseif (preg_match('/^DB_USERNAME=/i', $line)) {
                $line = 'DB_USERNAME=' . $credentials['username'];
                $dbKeysFound['DB_USERNAME'] = true;
                $matched = true;
            }
            // # DB_PASSWORD (comment'li)
            elseif (preg_match('/^#\s*DB_PASSWORD=/i', $line)) {
                $line = 'DB_PASSWORD=' . ($credentials['password'] ?? '');
                $dbKeysFound['DB_PASSWORD'] = true;
                $matched = true;
            }
            // DB_PASSWORD (aktif)
            elseif (preg_match('/^DB_PASSWORD=/i', $line)) {
                $line = 'DB_PASSWORD=' . ($credentials['password'] ?? '');
                $dbKeysFound['DB_PASSWORD'] = true;
                $matched = true;
            }

            if ($matched) {
                \Log::debug("ENV: Replaced line", ['from' => $originalLine, 'to' => $line]);
            }

            $processed[] = $line;
        }

        // Eksik kalan key'leri sona ekle
        foreach ($dbKeysFound as $key => $found) {
            if (!$found) {
                $value = match($key) {
                    'DB_CONNECTION' => $credentials['connection'],
                    'DB_HOST' => $credentials['host'],
                    'DB_PORT' => $credentials['port'],
                    'DB_DATABASE' => $credentials['database'],
                    'DB_USERNAME' => $credentials['username'],
                    'DB_PASSWORD' => $credentials['password'] ?? '',
                };
                $processed[] = "{$key}={$value}";
                \Log::debug("ENV: Added missing key", ['key' => $key, 'value' => $value]);
            }
        }

        \Log::info('EnvironmentService: Database config applied successfully');

        return implode("\n", $processed);
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
