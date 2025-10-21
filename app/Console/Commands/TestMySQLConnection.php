<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\MySQLService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestMySQLConnection extends Command
{
    protected $signature = 'db:test';
    protected $description = 'Test MySQL connection and permissions';

    public function handle(MySQLService $mySQLService): int
    {
        $this->info('🔍 Testing MySQL Connection...');
        $this->newLine();

        try {
            // Test basic connection
            $this->info('→ Testing connection...');
            $result = DB::select('SELECT VERSION() as version, DATABASE() as db, USER() as user');

            $this->line('  ✓ Connection successful!');
            $this->line('  MySQL Version: ' . $result[0]->version);
            $this->line('  Current Database: ' . ($result[0]->db ?? 'none'));
            $this->line('  Current User: ' . $result[0]->user);
            $this->newLine();

            // Test database creation
            $testDb = 'test_sb_' . time();
            $testUser = 'test_user_' . time();
            $testPass = 'test_' . uniqid();

            $this->info('→ Testing database operations...');

            $dbCreated = DB::statement("CREATE DATABASE IF NOT EXISTS `{$testDb}`");
            $this->line('  ' . ($dbCreated ? '✓' : '✗') . ' CREATE DATABASE');
            
            $userCreated = DB::statement("CREATE USER IF NOT EXISTS '{$testUser}'@'%' IDENTIFIED BY '{$testPass}'");
            $this->line('  ' . ($userCreated ? '✓' : '✗') . ' CREATE USER');
            
            $granted = DB::statement("GRANT ALL PRIVILEGES ON `{$testDb}`.* TO '{$testUser}'@'%'");
            $this->line('  ' . ($granted ? '✓' : '✗') . ' GRANT PRIVILEGES');
            
            DB::statement("FLUSH PRIVILEGES");
            $this->line('  ✓ FLUSH PRIVILEGES');
            
            // Cleanup
            DB::statement("DROP DATABASE IF EXISTS `{$testDb}`");
            DB::statement("DROP USER IF EXISTS '{$testUser}'@'%'");
            $this->line('  ✓ Cleanup completed');

            $this->newLine();
            $this->info('✅ All MySQL operations working!');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('✗ MySQL Error: ' . $e->getMessage());
            $this->newLine();

            $this->warn('Config check:');
            $this->line('  Host: ' . config('database.connections.mysql.host'));
            $this->line('  Port: ' . config('database.connections.mysql.port'));
            $this->line('  Database: ' . config('database.connections.mysql.database'));
            $this->line('  Username: ' . config('database.connections.mysql.username'));

            $this->newLine();
            $this->warn('Possible solutions:');
            $this->line('  1. Check .env file database credentials');
            $this->line('  2. Ensure MySQL is running');
            $this->line('  3. Check MySQL user has CREATE DATABASE privilege');
            $this->line('  4. If Docker: docker-compose up -d mysql');

            return self::FAILURE;
        }
    }
}

