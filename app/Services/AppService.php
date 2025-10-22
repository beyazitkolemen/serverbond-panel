<?php

namespace App\Services;

use App\Models\Site;
use Illuminate\Support\Str;
use App\Actions\Php\RestartService;
use App\Actions\Nginx\AddSiteService;
use App\Actions\Mysql\CreateUserService;
use App\Actions\Mysql\CreateDatabaseService;
use App\Actions\Mysql\GrantPrivilegesService;

class AppService
{
    public function __construct()
    {
    }

    /**
     * Create app
     * @param Site $site
     * @return void
     */
    public function createApp(Site $site): void
    {
        // Create Nginx Site
        $addSiteService = new AddSiteService();
        $addSiteService->execute($site->domain, $site->type->value, $site->root_directory, $site->php_version->value);
        dd($addSiteService);

        $databaseCredentials = $this->generateDatabaseCredentials($site);
        // Create Database
        $createDatabaseService = new CreateDatabaseService();
        $createDatabaseService->execute($databaseCredentials['database']);

        // Create User
        $createUserService = new CreateUserService();
        $createUserService->execute($databaseCredentials['user'], $databaseCredentials['password'], $databaseCredentials['database'], 'ALL PRIVILEGES');

        // Restart PHP-FPM
        $restartService = new RestartService();
        $restartService->execute($site->php_version->value);




    }
    public function generateDatabaseCredentials(Site $site): array
    {
        $databaseName = 'db_' . Str::random(10);
        $databaseUser = 'user_' . Str::random(10);
        $databasePassword = 'password_' . Str::random(10);

        $site->database_name = $databaseName;
        $site->database_user = $databaseUser;
        $site->database_password = $databasePassword;
        $site->save();

        return [
            'database' => $databaseName,
            'user' => $databaseUser,
            'password' => $databasePassword,
        ];
    }
}
