<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ServerBond Agent Configuration
    |--------------------------------------------------------------------------
    |
    | Bu konfigürasyon ServerBond Agent script'lerinin çalıştırılması için
    | gerekli path'leri ve ayarları içerir.
    |
    */

    'base_dir' => env('SERVERBOND_BASE_DIR', '/opt/serverbond-agent/scripts'),
    'lib_path' => env('SERVERBOND_LIB_PATH', '/opt/serverbond-agent/scripts/lib.sh'),
    'log_path' => env('SERVERBOND_LOG_PATH', '/opt/serverbond-agent/logs/script_exec.log'),
    
    /*
    |--------------------------------------------------------------------------
    | Script Categories
    |--------------------------------------------------------------------------
    |
    | Her kategori için script path'leri ve açıklamaları
    |
    */
    
    'categories' => [
        'system' => [
            'name' => 'Sistem Yönetimi',
            'description' => 'OS güncellemeleri, güvenlik, monitör ve sistem yönetimi',
            'icon' => 'heroicon-o-cog-6-tooth',
        ],
        'meta' => [
            'name' => 'Agent Yönetimi',
            'description' => 'Agent sağlık kontrolü, sürüm ve yetenekler',
            'icon' => 'heroicon-o-cpu-chip',
        ],
        'nginx' => [
            'name' => 'Nginx',
            'description' => 'Web sunucu yönetimi, site konfigürasyonu ve SSL',
            'icon' => 'heroicon-o-globe-alt',
        ],
        'php' => [
            'name' => 'PHP-FPM',
            'description' => 'PHP yönetimi ve konfigürasyonu',
            'icon' => 'heroicon-o-code-bracket',
        ],
        'redis' => [
            'name' => 'Redis',
            'description' => 'Redis cache sunucu yönetimi',
            'icon' => 'heroicon-o-bolt',
        ],
        'mysql' => [
            'name' => 'MySQL',
            'description' => 'Veritabanı yönetimi ve işlemleri',
            'icon' => 'heroicon-o-circle-stack',
        ],
        'node' => [
            'name' => 'Node.js',
            'description' => 'Node.js, NVM ve PM2 yönetimi',
            'icon' => 'heroicon-o-cube',
        ],
        'static' => [
            'name' => 'Static Site',
            'description' => 'Statik site yönetimi ve deployment',
            'icon' => 'heroicon-o-document-text',
        ],
        'wordpress' => [
            'name' => 'WordPress',
            'description' => 'WordPress site yönetimi ve eklentiler',
            'icon' => 'heroicon-o-squares-2x2',
        ],
        'laravel' => [
            'name' => 'Laravel',
            'description' => 'Laravel proje yönetimi ve deployment',
            'icon' => 'heroicon-o-sparkles',
        ],
        'deploy' => [
            'name' => 'Deployment',
            'description' => 'Genel deployment işlemleri',
            'icon' => 'heroicon-o-rocket-launch',
        ],
        'supervisor' => [
            'name' => 'Supervisor',
            'description' => 'Queue ve worker yönetimi',
            'icon' => 'heroicon-o-queue-list',
        ],
        'ssl' => [
            'name' => 'SSL',
            'description' => 'SSL sertifika yönetimi',
            'icon' => 'heroicon-o-shield-check',
        ],
        'user' => [
            'name' => 'Kullanıcı & SSH',
            'description' => 'Sistem kullanıcıları ve SSH yönetimi',
            'icon' => 'heroicon-o-user',
        ],
        'maintenance' => [
            'name' => 'Bakım & Yedekleme',
            'description' => 'Bakım modu ve yedekleme işlemleri',
            'icon' => 'heroicon-o-wrench-screwdriver',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Script Paths
    |--------------------------------------------------------------------------
    |
    | Her script için tam path'ler
    |
    */
    
    'scripts' => [
        // System scripts
        'system.update_os' => 'system/update_os.sh',
        'system.reboot' => 'system/reboot.sh',
        'system.status' => 'system/status.sh',
        'system.hostname' => 'system/hostname.sh',
        'system.ufw_configure' => 'system/ufw_configure.sh',
        'system.fail2ban_install' => 'system/fail2ban_install.sh',
        'system.logs' => 'system/logs.sh',
        'system.timezone' => 'system/timezone.sh',

        // Meta scripts
        'meta.health' => 'meta/health.sh',
        'meta.version' => 'meta/version.sh',
        'meta.capabilities' => 'meta/capabilities.sh',
        'meta.update' => 'meta/update.sh',
        'meta.diagnostics' => 'meta/diagnostics.sh',

        // Nginx scripts
        'nginx.install' => 'nginx/install.sh',
        'nginx.start' => 'nginx/start.sh',
        'nginx.stop' => 'nginx/stop.sh',
        'nginx.restart' => 'nginx/restart.sh',
        'nginx.reload' => 'nginx/reload.sh',
        'nginx.config_test' => 'nginx/config_test.sh',
        'nginx.add_site' => 'nginx/add_site.sh',
        'nginx.remove_site' => 'nginx/remove_site.sh',
        'nginx.list_sites' => 'nginx/list_sites.sh',
        'nginx.enable_ssl' => 'nginx/enable_ssl.sh',
        'nginx.disable_ssl' => 'nginx/disable_ssl.sh',
        'nginx.rebuild_conf' => 'nginx/rebuild_conf.sh',

        // PHP scripts
        'php.install_stack' => 'php/install_stack.sh',
        'php.restart' => 'php/restart.sh',
        'php.config_edit' => 'php/config_edit.sh',
        'php.info' => 'php/info.sh',

        // Redis scripts
        'redis.install' => 'redis/install.sh',
        'redis.start' => 'redis/start.sh',
        'redis.stop' => 'redis/stop.sh',
        'redis.restart' => 'redis/restart.sh',
        'redis.info' => 'redis/info.sh',
        'redis.flush_all' => 'redis/flush_all.sh',

        // MySQL scripts
        'mysql.install' => 'mysql/install.sh',
        'mysql.start' => 'mysql/start.sh',
        'mysql.stop' => 'mysql/stop.sh',
        'mysql.restart' => 'mysql/restart.sh',
        'mysql.create_database' => 'mysql/create_database.sh',
        'mysql.delete_database' => 'mysql/delete_database.sh',
        'mysql.create_user' => 'mysql/create_user.sh',
        'mysql.delete_user' => 'mysql/delete_user.sh',
        'mysql.import_sql' => 'mysql/import_sql.sh',
        'mysql.export_sql' => 'mysql/export_sql.sh',
        'mysql.status' => 'mysql/status.sh',

        // Node.js scripts
        'node.install_nvm' => 'node/install_nvm.sh',
        'node.use_version' => 'node/use_version.sh',
        'node.pm2_install' => 'node/pm2_install.sh',
        'node.pm2_app_add' => 'node/pm2_app_add.sh',
        'node.pm2_app_remove' => 'node/pm2_app_remove.sh',
        'node.pm2_app_restart' => 'node/pm2_app_restart.sh',
        'node.pm2_list' => 'node/pm2_list.sh',

        // Static site scripts
        'static.create_site' => 'static/create_site.sh',
        'static.deploy_artifact' => 'static/deploy_artifact.sh',

        // WordPress scripts
        'wp.install_stack' => 'wp/install_stack.sh',
        'wp.new_site' => 'wp/new_site.sh',
        'wp.enable_ssl' => 'wp/enable_ssl.sh',
        'wp.plugin_install' => 'wp/plugin_install.sh',
        'wp.theme_install' => 'wp/theme_install.sh',
        'wp.cache_flush' => 'wp/cache_flush.sh',

        // Laravel scripts
        'laravel.env_write' => 'laravel/env_write.sh',
        'laravel.deploy_pipeline' => 'laravel/deploy_pipeline.sh',
        'laravel.artisan' => 'laravel/artisan.sh',
        'laravel.queue_restart' => 'laravel/queue_restart.sh',
        'laravel.schedule_run' => 'laravel/schedule_run.sh',

        // Deployment scripts
        'deploy.clone_repo' => 'deploy/clone_repo.sh',
        'deploy.git_pull' => 'deploy/git_pull.sh',
        'deploy.composer_install' => 'deploy/composer_install.sh',
        'deploy.npm_build' => 'deploy/npm_build.sh',
        'deploy.custom_script_write' => 'deploy/custom_script_write.sh',
        'deploy.custom_script_run' => 'deploy/custom_script_run.sh',
        'deploy.json_pipeline_run' => 'deploy/json_pipeline_run.sh',

        // Supervisor scripts
        'supervisor.install' => 'supervisor/install.sh',
        'supervisor.reload' => 'supervisor/reload.sh',
        'supervisor.restart' => 'supervisor/restart.sh',
        'supervisor.add_program' => 'supervisor/add_program.sh',
        'supervisor.remove_program' => 'supervisor/remove_program.sh',

        // SSL scripts
        'ssl.install_certbot' => 'ssl/install_certbot.sh',
        'ssl.create_ssl' => 'ssl/create_ssl.sh',
        'ssl.renew_ssl' => 'ssl/renew_ssl.sh',
        'ssl.list_certs' => 'ssl/list_certs.sh',
        'ssl.remove_ssl' => 'ssl/remove_ssl.sh',

        // User & SSH scripts
        'user.add_user' => 'user/add_user.sh',
        'user.delete_user' => 'user/delete_user.sh',
        'user.ssh_key_add' => 'user/ssh_key_add.sh',
        'user.ssh_key_remove' => 'user/ssh_key_remove.sh',
        'user.list_users' => 'user/list_users.sh',

        // Maintenance scripts
        'maintenance.enable_mode' => 'maintenance/enable_mode.sh',
        'maintenance.disable_mode' => 'maintenance/disable_mode.sh',
        'maintenance.backup_files' => 'maintenance/backup_files.sh',
        'maintenance.backup_db' => 'maintenance/backup_db.sh',
        'maintenance.restore_db' => 'maintenance/restore_db.sh',
    ],

    /*
    |--------------------------------------------------------------------------
    | Execution Settings
    |--------------------------------------------------------------------------
    |
    | Script çalıştırma ayarları
    |
    */
    
    'execution' => [
        'timeout' => env('SERVERBOND_TIMEOUT', 300), // 5 dakika
        'user' => env('SERVERBOND_USER', 'root'),
        'working_directory' => env('SERVERBOND_WORKING_DIR', '/opt/serverbond-agent'),
        'output_format' => 'json',
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Güvenlik ayarları
    |
    */
    
    'security' => [
        'allowed_commands' => [
            'systemctl',
            'nginx',
            'php-fpm',
            'mysql',
            'redis-server',
            'pm2',
            'supervisorctl',
            'certbot',
            'ufw',
            'fail2ban-client',
            'apt',
            'dpkg',
        ],
        'dangerous_scripts' => [
            'system.reboot',
            'mysql.delete_database',
            'redis.flush_all',
            'user.delete_user',
        ],
    ],
];