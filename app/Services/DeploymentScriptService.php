<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SiteType;

class DeploymentScriptService
{
    /**
     * Site tipine göre varsayılan deployment script'ini döndürür
     */
    public function getDefaultScript(SiteType $type): string
    {
        return match ($type) {
            SiteType::Laravel => $this->getLaravelScript(),
            SiteType::PHP => $this->getPhpScript(),
            SiteType::Static => $this->getStaticScript(),
            SiteType::NodeJS => $this->getNodeJsScript(),
            SiteType::Python => $this->getPythonScript(),
        };
    }

    /**
     * Laravel deployment script
     */
    protected function getLaravelScript(): string
    {
        return <<<'BASH'
#!/bin/bash
set -e

echo "🚀 Laravel Deployment Started"

# Composer
echo "📦 Installing dependencies..."
if [ -f "composer.lock" ]; then
    echo "✓ composer.lock found, installing from lock file"
    composer install --no-dev --optimize-autoloader --no-interaction
else
    echo "⚠ composer.lock not found, updating dependencies"
    composer update --no-dev --optimize-autoloader --no-interaction
fi

# Cache
echo "🗑️ Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Database
echo "🗄️ Running migrations..."
php artisan migrate --force

# Optimize
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Storage
echo "🔗 Linking storage..."
php artisan storage:link || echo "⚠ Storage link already exists"

# Permissions
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache

echo "✅ Deployment completed successfully!"
BASH;
    }

    /**
     * PHP deployment script
     */
    protected function getPhpScript(): string
    {
        return <<<'BASH'
#!/bin/bash
set -e

echo "🚀 PHP Deployment Started"

# Composer (if exists)
if [ -f "composer.json" ]; then
    echo "📦 Installing composer dependencies..."
    if [ -f "composer.lock" ]; then
        composer install --no-dev --optimize-autoloader --no-interaction
    else
        composer update --no-dev --optimize-autoloader --no-interaction
    fi
fi

# Permissions
echo "🔐 Setting permissions..."
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

echo "✅ Deployment completed successfully!"
BASH;
    }

    /**
     * Static site deployment script
     */
    protected function getStaticScript(): string
    {
        return <<<'BASH'
#!/bin/bash
set -e

echo "🚀 Static Site Deployment Started"

# NPM (if exists)
if [ -f "package.json" ]; then
    echo "📦 Installing npm dependencies..."
    npm ci || npm install
    echo "🔨 Building assets..."
    npm run build
fi

# Permissions
echo "🔐 Setting permissions..."
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

echo "✅ Deployment completed successfully!"
BASH;
    }

    /**
     * Node.js deployment script
     */
    protected function getNodeJsScript(): string
    {
        return <<<'BASH'
#!/bin/bash
set -e

echo "🚀 Node.js Deployment Started"

# NPM
echo "📦 Installing npm dependencies..."
npm ci --production || npm install --production

# PM2 restart
echo "🔄 Restarting application..."
pm2 restart ecosystem.config.js --update-env || pm2 start ecosystem.config.js

echo "✅ Deployment completed successfully!"
BASH;
    }

    /**
     * Python deployment script
     */
    protected function getPythonScript(): string
    {
        return <<<'BASH'
#!/bin/bash
set -e

echo "🚀 Python Deployment Started"

# Virtual Environment
if [ ! -d "venv" ]; then
    echo "🐍 Creating virtual environment..."
    python3 -m venv venv
fi
source venv/bin/activate

# Dependencies
echo "📦 Installing pip dependencies..."
pip install -r requirements.txt

# Django (if exists)
if [ -f "manage.py" ]; then
    echo "🗄️ Running migrations..."
    python manage.py migrate --noinput
    echo "📁 Collecting static files..."
    python manage.py collectstatic --noinput
fi

# Restart
echo "🔄 Restarting application..."
sudo systemctl restart gunicorn || true

echo "✅ Deployment completed successfully!"
BASH;
    }

    /**
     * Custom script validation
     */
    public function validateScript(string $script): array
    {
        $errors = [];

        // Boş script kontrolü
        if (trim($script) === '') {
            return ['success' => true];
        }

        // Bash shebang kontrolü
        if (!str_starts_with(trim($script), '#!/bin/bash') && !str_starts_with(trim($script), '#!/bin/sh')) {
            $errors[] = 'Script #!/bin/bash veya #!/bin/sh ile başlamalıdır';
        }

        // Tehlikeli komutlar kontrolü
        $dangerousCommands = [
            'rm -rf /',
            'dd if=',
            'mkfs.',
            ':(){ :|:& };:',
            'mv / ',
            'chmod 777 /',
        ];

        foreach ($dangerousCommands as $dangerous) {
            if (str_contains($script, $dangerous)) {
                $errors[] = "Tehlikeli komut tespit edildi: {$dangerous}";
            }
        }

        return [
            'success' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Script önizleme için syntax highlight
     */
    public function highlightScript(string $script): string
    {
        // Basit bir syntax highlight (opsiyonel)
        // Frontend'de kullanılabilir
        return $script;
    }
}

