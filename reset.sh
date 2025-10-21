#!/bin/bash
set -e

echo "🔄 ServerBond Panel Reset"
echo ""

# Git reset
echo "→ Resetting git..."
git fetch origin
git reset --hard origin/$(git branch --show-current)
git clean -fd

# Composer
echo "→ Installing composer..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# NPM
echo "→ Installing npm..."
npm install

# Migration
echo "→ Running migrations..."
php artisan migrate --force

# Cache
echo "→ Clearing cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize
echo "→ Optimizing..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
# Build
echo "→ Building assets..."
npm run build

echo ""
echo "✅ Done!"
echo ""

