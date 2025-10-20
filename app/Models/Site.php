<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SiteStatus;
use App\Enums\SiteType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'domain',
        'type',
        'root_directory',
        'public_directory',
        'git_repository',
        'git_branch',
        'git_deploy_key',
        'status',
        'php_version',
        'database_name',
        'database_user',
        'database_password',
        'ssl_enabled',
        'auto_deploy',
        'deploy_webhook_token',
        'last_deployed_at',
        'notes',
        'deployment_script',
    ];

    protected $casts = [
        'type' => SiteType::class,
        'status' => SiteStatus::class,
        'ssl_enabled' => 'boolean',
        'auto_deploy' => 'boolean',
        'last_deployed_at' => 'datetime',
    ];

    protected $hidden = [
        'database_password',
        'git_deploy_key',
        'deploy_webhook_token',
    ];

    public function deployments(): HasMany
    {
        return $this->hasMany(Deployment::class)->latest();
    }

    public function latestDeployment(): HasOne
    {
        return $this->hasOne(Deployment::class)->latestOfMany();
    }

    public function sslCertificate(): HasOne
    {
        return $this->hasOne(SslCertificate::class);
    }

    public function envVariables(): HasMany
    {
        return $this->hasMany(EnvVariable::class);
    }

    public function isActive(): bool
    {
        return $this->status === SiteStatus::Active;
    }

    public function isDeploying(): bool
    {
        return $this->status === SiteStatus::Deploying;
    }

    protected static function booted(): void
    {
        static::creating(function (Site $site) {
            // Deployment script yoksa, site tipine göre default oluştur
            if (empty($site->deployment_script)) {
                $site->deployment_script = static::getDefaultDeploymentScript($site->type);
            }
        });
    }

    public static function getDefaultDeploymentScript(SiteType $type): string
    {
        return match ($type) {
            SiteType::Laravel => <<<'BASH'
#!/bin/bash
set -e

echo "🚀 Laravel Deployment Başlıyor..."

# Composer bağımlılıklarını yükle
echo "📦 Composer bağımlılıkları yükleniyor..."
composer install --no-dev --optimize-autoloader --no-interaction

# Cache'leri temizle
echo "🧹 Cache temizleniyor..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Migration'ları çalıştır
echo "🗄️  Migration'lar çalıştırılıyor..."
php artisan migrate --force

# Cache'leri optimize et
echo "⚡ Optimizasyon yapılıyor..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Storage link oluştur
echo "🔗 Storage link oluşturuluyor..."
php artisan storage:link

# Permissions düzenle
echo "🔐 İzinler ayarlanıyor..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "✅ Laravel Deployment Tamamlandı!"
BASH,

            SiteType::PHP => <<<'BASH'
#!/bin/bash
set -e

echo "🚀 PHP Deployment Başlıyor..."

# Composer varsa bağımlılıkları yükle
if [ -f "composer.json" ]; then
    echo "📦 Composer bağımlılıkları yükleniyor..."
    composer install --no-dev --optimize-autoloader --no-interaction
fi

# Permissions düzenle
echo "🔐 İzinler ayarlanıyor..."
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

echo "✅ PHP Deployment Tamamlandı!"
BASH,

            SiteType::Static => <<<'BASH'
#!/bin/bash
set -e

echo "🚀 Static Site Deployment Başlıyor..."

# NPM bağımlılıkları varsa yükle
if [ -f "package.json" ]; then
    echo "📦 NPM bağımlılıkları yükleniyor..."
    npm ci
    
    echo "🏗️  Build ediliyor..."
    npm run build
fi

# Permissions düzenle
echo "🔐 İzinler ayarlanıyor..."
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

echo "✅ Static Site Deployment Tamamlandı!"
BASH,

            SiteType::NodeJS => <<<'BASH'
#!/bin/bash
set -e

echo "🚀 Node.js Deployment Başlıyor..."

# NPM bağımlılıklarını yükle
echo "📦 NPM bağımlılıkları yükleniyor..."
npm ci --production

# PM2 ile uygulamayı yeniden başlat
echo "🔄 Uygulama yeniden başlatılıyor..."
pm2 restart ecosystem.config.js --update-env || pm2 start ecosystem.config.js

echo "✅ Node.js Deployment Tamamlandı!"
BASH,

            SiteType::Python => <<<'BASH'
#!/bin/bash
set -e

echo "🚀 Python Deployment Başlıyor..."

# Virtual environment oluştur veya güncelle
echo "🐍 Virtual environment kontrol ediliyor..."
if [ ! -d "venv" ]; then
    python3 -m venv venv
fi

# Virtual environment'ı aktifleştir
source venv/bin/activate

# Bağımlılıkları yükle
echo "📦 Python bağımlılıkları yükleniyor..."
pip install -r requirements.txt

# Django migration (eğer Django ise)
if [ -f "manage.py" ]; then
    echo "🗄️  Django migration'ları çalıştırılıyor..."
    python manage.py migrate --noinput
    
    echo "📦 Static dosyalar toplanıyor..."
    python manage.py collectstatic --noinput
fi

# Gunicorn'u yeniden başlat
echo "🔄 Uygulama yeniden başlatılıyor..."
sudo systemctl restart gunicorn || echo "Gunicorn yeniden başlatılamadı"

echo "✅ Python Deployment Tamamlandı!"
BASH,
        };
    }
}
