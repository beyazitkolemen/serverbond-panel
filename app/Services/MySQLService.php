<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Site;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class MySQLService
{
    public function createDatabase(string $databaseName): bool
    {
        try {
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function createUser(string $username, string $password): bool
    {
        try {
            DB::statement("CREATE USER IF NOT EXISTS '{$username}'@'localhost' IDENTIFIED BY '{$password}'");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function grantPrivileges(string $databaseName, string $username): bool
    {
        try {
            DB::statement("GRANT ALL PRIVILEGES ON `{$databaseName}`.* TO '{$username}'@'localhost'");
            DB::statement("FLUSH PRIVILEGES");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function createDatabaseForSite(Site $site): array
    {
        $dbName = $site->database_name ?: 'db_' . Str::slug($site->domain, '_');
        $dbUser = $site->database_user ?: 'user_' . Str::slug($site->domain, '_');
        $dbPassword = $site->database_password ?: Str::random(32);

        $databaseCreated = $this->createDatabase($dbName);
        $userCreated = $this->createUser($dbUser, $dbPassword);
        $privilegesGranted = $this->grantPrivileges($dbName, $dbUser);

        if ($databaseCreated && $userCreated && $privilegesGranted) {
            $site->update([
                'database_name' => $dbName,
                'database_user' => $dbUser,
                'database_password' => encrypt($dbPassword),
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
            'error' => 'Failed to create database',
        ];
    }

    public function deleteDatabase(string $databaseName): bool
    {
        try {
            DB::statement("DROP DATABASE IF EXISTS `{$databaseName}`");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function deleteUser(string $username): bool
    {
        try {
            DB::statement("DROP USER IF EXISTS '{$username}'@'localhost'");
            DB::statement("FLUSH PRIVILEGES");
            return true;
        } catch (\Exception $e) {
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
            $databases = DB::select('SHOW DATABASES');
            return array_column($databases, 'Database');
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getDatabaseSize(string $databaseName): int
    {
        try {
            $result = DB::selectOne(
                "SELECT SUM(data_length + index_length) as size
                FROM information_schema.TABLES
                WHERE table_schema = ?",
                [$databaseName]
            );

            return (int) ($result->size ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }
}

