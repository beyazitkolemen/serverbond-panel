<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\MySQLService;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DatabaseManager;
use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class MySQLServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private MySQLService $service;

    private ConnectionInterface $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = Mockery::mock(ConnectionInterface::class);
        $databaseManager = Mockery::mock(DatabaseManager::class);

        $databaseManager->shouldReceive('connection')
            ->andReturn($this->connection);

        $this->service = new MySQLService($databaseManager);
    }

    public function test_create_database_uses_sanitized_identifier(): void
    {
        $this->connection->shouldReceive('statement')
            ->once()
            ->with("CREATE DATABASE IF NOT EXISTS `invalid_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")
            ->andReturnTrue();

        $this->assertTrue($this->service->createDatabase('invalid-name!'));
    }

    public function test_create_user_binds_password(): void
    {
        $this->connection->shouldReceive('statement')
            ->once()
            ->with("CREATE USER IF NOT EXISTS 'user_example'@'localhost' IDENTIFIED BY ?", ['secret'])
            ->andReturnTrue();

        $this->assertTrue($this->service->createUser('user-example', 'secret'));
    }

    public function test_grant_privileges_flushes_privileges(): void
    {
        $this->connection->shouldReceive('statement')
            ->once()
            ->with("GRANT ALL PRIVILEGES ON `my_db`.* TO 'my_user'@'localhost'")
            ->andReturnTrue();

        $this->connection->shouldReceive('statement')
            ->once()
            ->with('FLUSH PRIVILEGES')
            ->andReturnTrue();

        $this->assertTrue($this->service->grantPrivileges('my_db', 'my_user'));
    }

    public function test_create_database_throws_when_identifier_is_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->createDatabase('!!!');
    }
}
