<?php

declare(strict_types=1);

namespace App\Actions\Wordpress;

use App\Actions\BaseServerBondService;

class CacheFlushService extends BaseServerBondService
{
    /**
     * WP cache flush (wp-cli)
     */
    public function execute(string $domain): array
    {
        $params = [
            'domain' => $domain,
        ];

        $this->validateParams($params, ['domain']);

        return $this->executeScript($this->getScriptPath('wp', 'cache_flush'), $params);
    }
}