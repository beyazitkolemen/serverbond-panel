<?php

declare(strict_types=1);

namespace App\Actions\Wordpress;

use App\Actions\BaseServerBondService;

class NewSiteService extends BaseServerBondService
{
    /**
     * New WordPress site setup (db+user+wp-config+install)
     */
    public function execute(
        string $domain,
        string $dbName,
        string $dbUser,
        string $dbPass,
        string $adminUser,
        string $adminPass,
        string $adminEmail,
        string $title
    ): array {
        $this->validateParams([
            'domain', 'db_name', 'db_user', 'db_pass',
            'admin_user', 'admin_pass', 'admin_email', 'title'
        ], [
            'domain', 'db_name', 'db_user', 'db_pass',
            'admin_user', 'admin_pass', 'admin_email', 'title'
        ]);
        
        return $this->executeScript($this->getScriptPath('wp', 'new_site'), [
            'domain' => $domain,
            'db_name' => $dbName,
            'db_user' => $dbUser,
            'db_pass' => $dbPass,
            'admin_user' => $adminUser,
            'admin_pass' => $adminPass,
            'admin_email' => $adminEmail,
            'title' => $title
        ]);
    }
}