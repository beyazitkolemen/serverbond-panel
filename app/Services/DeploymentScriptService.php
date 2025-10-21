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

echo "🚀 Laravel Deployment Started"
echo "----------------------------------------"

# Composer
echo "📦 Installing dependencies..."
if [ -f "composer.lock" ]; then
    echo "✓ composer.lock found, installing from lock file"
    composer install --no-dev --optimize-autoloader --no-interaction
    COMPOSER_EXIT=$?
else
    echo "⚠ composer.lock not found, updating dependencies"
    composer update --no-dev --optimize-autoloader --no-interaction
    COMPOSER_EXIT=$?
fi

if [ $COMPOSER_EXIT -ne 0 ]; then
    echo "❌ Composer failed with exit code $COMPOSER_EXIT"
    exit $COMPOSER_EXIT
fi
echo "✓ Composer completed"

# Database migrations
echo ""
echo "🗄️ Running migrations..."
php artisan migrate --force
MIGRATE_EXIT=$?
if [ $MIGRATE_EXIT -ne 0 ]; then
    echo "❌ Migrations failed with exit code $MIGRATE_EXIT"
    exit $MIGRATE_EXIT
fi
echo "✓ Migrations completed"

# Cache temizleme (hata olsa bile devam et)
echo ""
echo "🗑️ Clearing caches..."
php artisan config:clear 2>&1 || echo "⚠ Config clear skipped"
php artisan cache:clear 2>&1 || echo "⚠ Cache clear skipped"
php artisan route:clear 2>&1 || echo "⚠ Route clear skipped"
php artisan view:clear 2>&1 || echo "⚠ View clear skipped"
echo "✓ Cache clearing completed"

# Optimize (hata olsa bile devam et)
echo ""
echo "⚡ Optimizing application..."
php artisan config:cache 2>&1 || echo "⚠ Config cache skipped"
php artisan route:cache 2>&1 || echo "⚠ Route cache skipped"
php artisan view:cache 2>&1 || echo "⚠ View cache skipped"
echo "✓ Optimization completed"

# Storage link (hata olsa bile devam et)
echo ""
echo "🔗 Linking storage..."
php artisan storage:link 2>&1 || echo "⚠ Storage already linked"

# Permissions
echo ""
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache 2>&1 || echo "⚠ Permission setting skipped"
echo "✓ Permissions set"

echo ""
echo "✅ Deployment completed successfully!"
echo "----------------------------------------"
exit 0
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

