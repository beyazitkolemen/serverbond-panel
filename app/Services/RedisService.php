<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Site;
use Illuminate\Contracts\Redis\Factory as RedisFactory;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Str;
use Throwable;

class RedisService
{
    public function __construct(
        private readonly RedisFactory $redis,
    ) {
    }

    public function ping(string $connection = 'default'): array
    {
        try {
            $response = $this->connection($connection)->ping();

            return [
                'success' => true,
                'response' => $response,
            ];
        } catch (Throwable $exception) {
            return [
                'success' => false,
                'error' => $exception->getMessage(),
            ];
        }
    }

    public function flushDatabase(string $connection = 'default'): array
    {
        try {
            $this->connection($connection)->flushdb();

            return [
                'success' => true,
            ];
        } catch (Throwable $exception) {
            return [
                'success' => false,
                'error' => $exception->getMessage(),
            ];
        }
    }

    public function clearSiteCache(Site $site, string $connection = 'default'): array
    {
        $pattern = $this->buildSitePattern($site);

        try {
            $redisConnection = $this->connection($connection);
            $keys = $redisConnection->keys($pattern);

            if (empty($keys)) {
                return [
                    'success' => true,
                    'deleted' => 0,
                    'pattern' => $pattern,
                ];
            }

            $deleted = $this->deleteKeys($redisConnection, $keys);

            return [
                'success' => true,
                'deleted' => $deleted,
                'pattern' => $pattern,
            ];
        } catch (Throwable $exception) {
            return [
                'success' => false,
                'error' => $exception->getMessage(),
                'pattern' => $pattern,
            ];
        }
    }

    protected function connection(string $connection): Connection
    {
        return $this->redis->connection($connection);
    }

    protected function buildSitePattern(Site $site): string
    {
        $prefix = Str::slug($site->domain, '_');

        if ($prefix === '') {
            $prefix = 'site';
        }

        return $prefix . ':*';
    }

    protected function deleteKeys(Connection $connection, array $keys): int
    {
        $deleted = 0;

        foreach (array_chunk($keys, 500) as $chunk) {
            $deleted += (int) $connection->del(...$chunk);
        }

        return $deleted;
    }
}
