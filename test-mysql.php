<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 MySQL Connection Test\n";
echo "========================\n\n";

try {
    $db = DB::connection();

    echo "✓ Connection successful!\n\n";

    // Test query
    $result = $db->select('SELECT VERSION() as version, DATABASE() as current_db, USER() as current_user');

    echo "MySQL Info:\n";
    echo "  Version: " . $result[0]->version . "\n";
    echo "  Current DB: " . ($result[0]->current_db ?? 'none') . "\n";
    echo "  Current User: " . $result[0]->current_user . "\n\n";

    // Test database creation
    echo "Testing database operations...\n";

    $testDb = 'test_sb_' . time();
    $testUser = 'test_user_' . time();
    $testPass = 'test_pass_' . rand(1000, 9999);

    // Create database
    echo "  → Creating test database: {$testDb}...\n";
    $dbCreated = $db->statement("CREATE DATABASE IF NOT EXISTS `{$testDb}`");
    echo "    " . ($dbCreated ? "✓" : "✗") . " Database creation\n";

    // Create user
    echo "  → Creating test user: {$testUser}...\n";
    $userCreated = $db->statement("CREATE USER IF NOT EXISTS '{$testUser}'@'%' IDENTIFIED BY '{$testPass}'");
    echo "    " . ($userCreated ? "✓" : "✗") . " User creation\n";
    
    // Grant privileges
    echo "  → Granting privileges...\n";
    $granted = $db->statement("GRANT ALL PRIVILEGES ON `{$testDb}`.* TO '{$testUser}'@'%'");
    $flushed = $db->statement("FLUSH PRIVILEGES");
    echo "    " . ($granted && $flushed ? "✓" : "✗") . " Privileges granted\n";
    
    // Cleanup
    echo "  → Cleaning up test data...\n";
    $db->statement("DROP DATABASE IF EXISTS `{$testDb}`");
    $db->statement("DROP USER IF EXISTS '{$testUser}'@'%'");
    $db->statement("FLUSH PRIVILEGES");
    echo "    ✓ Cleanup completed\n\n";

    echo "✅ All MySQL operations working correctly!\n\n";

    echo "Connection config:\n";
    echo "  Host: " . config('database.connections.mysql.host') . "\n";
    echo "  Port: " . config('database.connections.mysql.port') . "\n";
    echo "  Database: " . config('database.connections.mysql.database') . "\n";
    echo "  Username: " . config('database.connections.mysql.username') . "\n";

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";

    echo "Connection config:\n";
    echo "  Host: " . config('database.connections.mysql.host') . "\n";
    echo "  Port: " . config('database.connections.mysql.port') . "\n";
    echo "  Database: " . config('database.connections.mysql.database') . "\n";
    echo "  Username: " . config('database.connections.mysql.username') . "\n\n";

    echo "Possible solutions:\n";
    echo "  1. Check if MySQL is running: systemctl status mysql\n";
    echo "  2. Check .env database credentials\n";
    echo "  3. Check MySQL permissions\n";
    echo "  4. If using Docker: docker-compose up -d mysql\n";
}

