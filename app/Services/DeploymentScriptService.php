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

# Composer
composer install --no-dev --optimize-autoloader --no-interaction

# Cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Database
php artisan migrate --force

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Storage
php artisan storage:link

# Permissions
chmod -R 775 storage bootstrap/cache
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

# Composer (if exists)
if [ -f "composer.json" ]; then
    composer install --no-dev --optimize-autoloader --no-interaction
fi

# Permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
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

# NPM (if exists)
if [ -f "package.json" ]; then
    npm ci
    npm run build
fi

# Permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
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

# NPM
npm ci --production

# PM2 restart
pm2 restart ecosystem.config.js --update-env || pm2 start ecosystem.config.js
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

# Virtual Environment
if [ ! -d "venv" ]; then
    python3 -m venv venv
fi
source venv/bin/activate

# Dependencies
pip install -r requirements.txt

# Django (if exists)
if [ -f "manage.py" ]; then
    python manage.py migrate --noinput
    python manage.py collectstatic --noinput
fi

# Restart
sudo systemctl restart gunicorn || true
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

