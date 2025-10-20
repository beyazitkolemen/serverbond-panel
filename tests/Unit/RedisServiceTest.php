<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Site;
use App\Services\RedisService;
use Illuminate\Contracts\Redis\Factory as RedisFactory;
use Illuminate\Redis\Connections\Connection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class RedisServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private RedisService $service;

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = Mockery::mock(Connection::class);
        $factory = Mockery::mock(RedisFactory::class);

        $factory->shouldReceive('connection')
            ->andReturn($this->connection);

        $this->service = new RedisService($factory);
    }

    public function test_flush_database_returns_success(): void
    {
        $this->connection->shouldReceive('flushdb')
            ->once()
            ->andReturnTrue();

        $result = $this->service->flushDatabase();

        $this->assertTrue($result['success']);
    }

    public function test_clear_site_cache_deletes_matching_keys(): void
    {
        $site = new Site();
        $site->domain = 'example.com';

        $this->connection->shouldReceive('keys')
            ->once()
            ->with('example_com:*')
            ->andReturn(['example_com:1', 'example_com:2']);

        $this->connection->shouldReceive('del')
            ->once()
            ->with('example_com:1', 'example_com:2')
            ->andReturn(2);

        $result = $this->service->clearSiteCache($site);

        $this->assertTrue($result['success']);
        $this->assertSame(2, $result['deleted']);
        $this->assertSame('example_com:*', $result['pattern']);
    }

    public function test_clear_site_cache_handles_empty_prefix(): void
    {
        $site = new Site();
        $site->domain = '';

        $this->connection->shouldReceive('keys')
            ->once()
            ->with('site:*')
            ->andReturn([]);

        $result = $this->service->clearSiteCache($site);

        $this->assertTrue($result['success']);
        $this->assertSame(0, $result['deleted']);
        $this->assertSame('site:*', $result['pattern']);
    }
}
