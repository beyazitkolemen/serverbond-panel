<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Deployment Paths
    |--------------------------------------------------------------------------
    |
    | Deployment sürecinde kullanılan klasör ve dosya yolları.
    |
    */

    'paths' => [
        'deploy_keys' => storage_path('app/deploy-keys'),
        'script_name' => 'deploy-script.sh',
    ],

    /*
    |--------------------------------------------------------------------------
    | Deployment Settings
    |--------------------------------------------------------------------------
    |
    | Deployment sürecine ait genel ayarlar.
    |
    */

    'timeout' => env('DEPLOYMENT_TIMEOUT', 600), // saniye
    'script_permissions' => 0755,
    'deploy_key_permissions' => 0600,

    /*
    |--------------------------------------------------------------------------
    | Git Settings
    |--------------------------------------------------------------------------
    |
    | Git repository işlemleri için kullanılan ayarlar.
    |
    */

    'git' => [
        'default_branch' => env('GIT_DEFAULT_BRANCH', 'main'),
        'api_timeout' => 3, // saniye
        'user_agent' => 'ServerBond',
    ],

    /*
    |--------------------------------------------------------------------------
    | Nginx Settings
    |--------------------------------------------------------------------------
    |
    | Nginx konfigürasyon ayarları.
    |
    */

    'nginx' => [
        'sites_available' => env('NGINX_SITES_AVAILABLE', '/etc/nginx/sites-available'),
        'sites_enabled' => env('NGINX_SITES_ENABLED', '/etc/nginx/sites-enabled'),
        'default_php_version' => env('DEFAULT_PHP_VERSION', '8.4'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Ports
    |--------------------------------------------------------------------------
    |
    | Farklı uygulama tipleri için varsayılan portlar.
    |
    */

    'ports' => [
        'nodejs' => env('NODEJS_DEFAULT_PORT', 3000),
        'python' => env('PYTHON_DEFAULT_PORT', 8000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Settings
    |--------------------------------------------------------------------------
    |
    | Veritabanı bağlantı ayarları.
    |
    */

    'database' => [
        'connection' => env('DEPLOYMENT_DB_CONNECTION', 'mysql'),
        'host' => env('DEPLOYMENT_DB_HOST', '127.0.0.1'),
        'port' => env('DEPLOYMENT_DB_PORT', '3306'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Directory Permissions
    |--------------------------------------------------------------------------
    |
    | Oluşturulan klasörler için varsayılan izinler.
    |
    */

    'directory_permissions' => 0755,

];


