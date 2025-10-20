<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Site;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class MySQLService
{
    public function __construct(
        private readonly DatabaseManager $databaseManager,
    ) {
    }

    public function createDatabase(string $databaseName): bool
    {
        try {
            $identifier = $this->quoteIdentifier($this->normalizeIdentifier($databaseName));

            return $this->connection()->statement(
                "CREATE DATABASE IF NOT EXISTS {$identifier} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
            );
        } catch (Throwable $e) {
            return false;
        }
    }

    public function createUser(string $username, string $password): bool
    {
        try {
            $identifier = $this->formatUserIdentifier($username);

            return $this->connection()->statement(
                "CREATE USER IF NOT EXISTS {$identifier} IDENTIFIED BY ?",
                [$password]
            );
        } catch (Throwable $e) {
            return false;
        }
    }

    public function grantPrivileges(string $databaseName, string $username): bool
    {
        try {
            $database = $this->quoteIdentifier($this->normalizeIdentifier($databaseName));
            $user = $this->formatUserIdentifier($username);

            $granted = $this->connection()->statement(
                "GRANT ALL PRIVILEGES ON {$database}.* TO {$user}"
            );

            if (! $granted) {
                return false;
            }

            return $this->connection()->statement('FLUSH PRIVILEGES');
        } catch (Throwable $e) {
            return false;
        }
    }

    public function createDatabaseForSite(Site $site): array
    {
        try {
            $dbName = $site->database_name ?: 'db_' . Str::slug($site->domain, '_');
            $dbUser = $site->database_user ?: 'user_' . Str::slug($site->domain, '_');
            $dbPassword = $site->database_password
                ? $this->decryptPassword($site->database_password)
                : Str::random(32);

            $dbName = $this->normalizeIdentifier($dbName);
            $dbUser = $this->normalizeIdentifier($dbUser);

            $databaseCreated = $this->createDatabase($dbName);
            $userCreated = $this->createUser($dbUser, $dbPassword);
            $privilegesGranted = $this->grantPrivileges($dbName, $dbUser);

            if ($databaseCreated && $userCreated && $privilegesGranted) {
                $site->update([
                    'database_name' => $dbName,
                    'database_user' => $dbUser,
                    'database_password' => Crypt::encryptString($dbPassword),
                ]);

                return [
                    'success' => true,
                    'database' => $dbName,
                    'user' => $dbUser,
                    'password' => $dbPassword,
                ];
            }

            return [
                'success' => false,
                'error' => 'Veritabanı, kullanıcı veya yetki oluşturulamadı.',
            ];
        } catch (InvalidArgumentException $exception) {
            return [
                'success' => false,
                'error' => $exception->getMessage(),
            ];
        }
    }

    public function deleteDatabase(string $databaseName): bool
    {
        try {
            $identifier = $this->quoteIdentifier($this->normalizeIdentifier($databaseName));

            return $this->connection()->statement("DROP DATABASE IF EXISTS {$identifier}");
        } catch (Throwable $e) {
            return false;
        }
    }

    public function deleteUser(string $username): bool
    {
        try {
            $identifier = $this->formatUserIdentifier($username);

            $dropped = $this->connection()->statement("DROP USER IF EXISTS {$identifier}");

            if (! $dropped) {
                return false;
            }

            return $this->connection()->statement('FLUSH PRIVILEGES');
        } catch (Throwable $e) {
            return false;
        }
    }

    public function deleteDatabaseForSite(Site $site): bool
    {
        if ($site->database_name) {
            $this->deleteDatabase($site->database_name);
        }

        if ($site->database_user) {
            $this->deleteUser($site->database_user);
        }

        return true;
    }

    public function getDatabases(): array
    {
        try {
            $databases = $this->connection()->select('SHOW DATABASES');
            return array_column($databases, 'Database');
        } catch (Throwable $e) {
            return [];
        }
    }

    public function getDatabaseSize(string $databaseName): int
    {
        try {
            $result = $this->connection()->selectOne(
                "SELECT SUM(data_length + index_length) as size
                FROM information_schema.TABLES
                WHERE table_schema = ?",
                [$databaseName]
            );

            return (int) ($result->size ?? 0);
        } catch (Throwable $e) {
            return 0;
        }
    }

    protected function normalizeIdentifier(string $value): string
    {
        $normalized = preg_replace('/[^A-Za-z0-9_]/', '_', $value);
        $normalized = trim((string) $normalized, '_');

        if ($normalized === '') {
            throw new InvalidArgumentException('MySQL identifier must contain at least one alphanumeric character.');
        }

        if (strlen($normalized) > 64) {
            $normalized = substr($normalized, 0, 64);
        }

        if (! preg_match('/^[A-Za-z0-9_]+$/', $normalized)) {
            throw new InvalidArgumentException('MySQL identifier may only contain letters, numbers, and underscores.');
        }

        return $normalized;
    }

    protected function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    protected function formatUserIdentifier(string $username, string $host = 'localhost'): string
    {
        $username = $this->normalizeIdentifier($username);
        $host = $this->sanitizeHost($host);

        return sprintf("'%s'@'%s'", $username, $host);
    }

    protected function sanitizeHost(string $host): string
    {
        if (! preg_match('/^[A-Za-z0-9._-]+$/', $host)) {
            throw new InvalidArgumentException('Invalid MySQL host value.');
        }

        return str_replace("'", "''", $host);
    }

    protected function connection(): ConnectionInterface
    {
        return $this->databaseManager->connection();
    }

    protected function decryptPassword(string $encrypted): string
    {
        try {
            return Crypt::decryptString($encrypted);
        } catch (Throwable $exception) {
            return $encrypted;
        }
    }
}

