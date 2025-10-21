<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Site;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class NginxService
{
    protected string $sitesAvailable;
    protected string $sitesEnabled;

    public function __construct()
    {
        $this->sitesAvailable = config('deployment.nginx.sites_available');
        $this->sitesEnabled = config('deployment.nginx.sites_enabled');
    }

    public function generateConfig(Site $site): string
    {
        $publicDir = $site->public_directory ?: 'public';
        $rootPath = rtrim($site->root_directory, '/') . '/' . $site->domain;
        $fullPublicPath = $rootPath . '/' . $publicDir;

        return match($site->type->value) {
            'laravel' => $this->generateLaravelConfig($site, $rootPath, $fullPublicPath),
            'php' => $this->generatePHPConfig($site, $rootPath, $fullPublicPath),
            'static' => $this->generateStaticConfig($site, $rootPath, $fullPublicPath),
            'nodejs' => $this->generateNodeJSConfig($site, $rootPath),
            'python' => $this->generatePythonConfig($site, $rootPath),
            default => $this->generatePHPConfig($site, $rootPath, $fullPublicPath),
        };
    }

    protected function generateLaravelConfig(Site $site, string $rootPath, string $publicPath): string
    {
        $phpVersion = $site->php_version ?: config('deployment.nginx.default_php_version');

        return <<<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name {$site->domain};
    root {$publicPath};

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php index.html index.htm;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \\.php$ {
        fastcgi_pass unix:/var/run/php/php{$phpVersion}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\\.(?!well-known).* {
        deny all;
    }
}
NGINX;
    }

    protected function generatePHPConfig(Site $site, string $rootPath, string $publicPath): string
    {
        $phpVersion = $site->php_version ?: config('deployment.nginx.default_php_version');

        return <<<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name {$site->domain};
    root {$publicPath};

    index index.php index.html index.htm;

    location / {
        try_files \$uri \$uri/ =404;
    }

    location ~ \\.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php{$phpVersion}-fpm.sock;
    }

    location ~ /\\.ht {
        deny all;
    }
}
NGINX;
    }

    protected function generateStaticConfig(Site $site, string $rootPath, string $publicPath): string
    {
        return <<<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name {$site->domain};
    root {$publicPath};

    index index.html index.htm;

    location / {
        try_files \$uri \$uri/ =404;
    }

    location ~* \\.(jpg|jpeg|gif|png|css|js|ico|xml|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
NGINX;
    }

    protected function generateNodeJSConfig(Site $site, string $rootPath): string
    {
        $port = config('deployment.ports.nodejs');

        return <<<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name {$site->domain};

    location / {
        proxy_pass http://localhost:{$port};
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host \$host;
        proxy_cache_bypass \$http_upgrade;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
}
NGINX;
    }

    protected function generatePythonConfig(Site $site, string $rootPath): string
    {
        $port = config('deployment.ports.python');

        return <<<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name {$site->domain};

    location / {
        proxy_pass http://localhost:{$port};
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
}
NGINX;
    }

    public function writeConfig(Site $site, string $config): array
    {
        $configPath = "{$this->sitesAvailable}/{$site->domain}.conf";

        try {
            // Temporary file oluştur
            $tempFile = sys_get_temp_dir() . '/' . $site->domain . '.conf';
            File::put($tempFile, $config);

            // Sudo ile nginx dizinine taşı
            $result = Process::run(['sudo', 'mv', $tempFile, $configPath]);

            if (!$result->successful()) {
                $error = trim($result->errorOutput());

                // Sudo hatası kontrolü
                if (str_contains($error, 'sudo') || str_contains($error, 'permission denied')) {
                    return [
                        'success' => false,
                        'error' => 'Sudo yetkisi yok. Lütfen SUDO-SETUP.md dosyasını inceleyin ve sudo yetkilerini yapılandırın.',
                    ];
                }

                return [
                    'success' => false,
                    'error' => $error ?: 'Config dosyası taşınamadı.',
                ];
            }

            // Sudo ile izinleri ayarla
            $chmodResult = Process::run(['sudo', 'chmod', '644', $configPath]);

            if (!$chmodResult->successful()) {
                return [
                    'success' => false,
                    'error' => 'Config dosyası izinleri ayarlanamadı.',
                ];
            }

            return [
                'success' => true,
                'message' => 'Config dosyası başarıyla yazıldı.',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function enableSite(Site $site): array
    {
        $availablePath = "{$this->sitesAvailable}/{$site->domain}.conf";
        $enabledPath = "{$this->sitesEnabled}/{$site->domain}.conf";

        if (!File::exists($availablePath)) {
            return [
                'success' => false,
                'error' => 'Config dosyası bulunamadı: ' . $availablePath,
            ];
        }

        try {
            if (!File::exists($enabledPath)) {
                // Sudo ile symlink oluştur
                $result = Process::run(['sudo', 'ln', '-sf', $availablePath, $enabledPath]);

                if (!$result->successful()) {
                    $error = trim($result->errorOutput());

                    if (str_contains($error, 'sudo') || str_contains($error, 'permission denied')) {
                        return [
                            'success' => false,
                            'error' => 'Sudo yetkisi yok. Lütfen SUDO-SETUP.md dosyasını inceleyin.',
                        ];
                    }

                    return [
                        'success' => false,
                        'error' => $error ?: 'Symlink oluşturulamadı.',
                    ];
                }
            }

            return [
                'success' => true,
                'message' => 'Site başarıyla aktifleştirildi.',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function disableSite(Site $site): bool
    {
        $enabledPath = "{$this->sitesEnabled}/{$site->domain}.conf";

        if (File::exists($enabledPath)) {
            $result = Process::run(['sudo', 'rm', $enabledPath]);
            return $result->successful();
        }

        return true;
    }

    public function testConfig(): array
    {
        return $this->runCommand(['sudo', 'nginx', '-t']);
    }

    public function reload(): array
    {
        return $this->runCommand(['sudo', 'systemctl', 'reload', 'nginx']);
    }

    public function restart(): array
    {
        return $this->runCommand(['sudo', 'systemctl', 'restart', 'nginx']);
    }

    public function getStatus(): array
    {
        $result = Process::run(['sudo', 'systemctl', 'status', 'nginx']);

        return [
            'running' => $result->successful(),
            'output' => $result->output(),
            'error' => $result->errorOutput(),
        ];
    }

    protected function runCommand(array $command): array
    {
        $result = Process::run($command);

        return [
            'success' => $result->successful(),
            'output' => $result->output(),
            'error' => $result->errorOutput(),
        ];
    }
}

