# ServerBond Panel

Modern, güçlü ve kullanıcı dostu web sunucu yönetim paneli. Laravel, Filament ve modern teknolojiler ile geliştirilmiştir.

## 📋 İçindekiler

- [Özellikler](#özellikler)
- [Teknolojiler](#teknolojiler)
- [Kurulum](#kurulum)
- [Kullanım](#kullanım)
- [Mimari](#mimari)
- [Servisler](#servisler)
- [API](#api)

## ✨ Özellikler

### 🚀 Site Yönetimi
- **Çoklu Site Desteği**: Laravel, PHP, Static, Node.js, Python
- **Otomatik Deployment**: Git entegrasyonu ile otomatik dağıtım
- **Branch Yönetimi**: GitHub, GitLab, Bitbucket desteği
- **Custom Deployment Scripts**: Her site için özelleştirilebilir bash scriptleri
- **Environment Yönetimi**: .env dosyalarını panel üzerinden düzenleme

### 🗄️ Database Yönetimi
- **Otomatik Database Oluşturma**: Deployment sırasında otomatik MySQL database
- **Güvenli Şifre Saklama**: Şifreler encrypt edilmiş olarak saklanır
- **Multi-Database**: Her site için ayrı database ve kullanıcı
- **Database Credentials**: Otomatik .env entegrasyonu

### 🔒 SSL & Güvenlik
- **Let's Encrypt**: Otomatik SSL sertifikası
- **Auto-Renewal**: Sertifika otomatik yenileme
- **Webhook Security**: Güvenli deployment webhook'ları
- **SSH Deploy Keys**: Private repository'ler için SSH key desteği

### ☁️ Cloudflare Tunnel
- **Zero Trust Access**: Cloudflare Tunnel entegrasyonu
- **Otomatik Başlatma**: Deployment sonrası tunnel başlatma
- **Systemd Entegrasyonu**: Service olarak çalışma
- **Token Based**: Basit token ile kurulum

### ⚙️ Ayarlar Sistemi
- **Merkezi Yönetim**: Tüm ayarlar tek yerden
- **Gruplandırma**: Mantıksal grup desteği
- **Şifreleme**: Hassas bilgiler için encryption
- **Cache**: Performans için cache desteği
- **Public/Private**: Frontend için public ayarlar

### 📊 Monitoring & Logs
- **Deployment History**: Tüm deployment geçmişi
- **Real-time Logs**: Canlı deployment logları
- **Error Tracking**: Hata takibi ve raporlama
- **Status Monitoring**: Site durumu izleme

## 🛠 Teknolojiler

### Backend
- **Framework**: Laravel 12
- **Admin Panel**: Filament v4
- **Database**: MySQL 8.0
- **Cache**: Redis
- **Queue**: Laravel Queue

### Frontend
- **UI Framework**: Tailwind CSS
- **Components**: Livewire 3.5+
- **JavaScript**: Alpine.js
- **Icons**: Heroicons

### DevOps
- **Web Server**: Nginx
- **Process Manager**: PM2 (Node.js), Supervisor
- **SSL**: Let's Encrypt (Certbot)
- **Tunnel**: Cloudflare Tunnel
- **Container**: Docker (Development)

## 📦 Kurulum

### Gereksinimler

```bash
- PHP 8.2+
- MySQL 8.0+
- Redis
- Nginx
- Composer
- Node.js 18+ & NPM
- Git
```

### 1. Repository'yi Klonlayın

```bash
git clone https://github.com/your-username/serverbond-panel.git
cd serverbond-panel
```

### 2. Bağımlılıkları Yükleyin

```bash
# PHP bağımlılıkları
composer install

# Frontend bağımlılıkları
npm install
```

### 3. Environment Ayarları

```bash
# .env dosyası oluştur
cp .env.example .env

# Uygulama anahtarı oluştur
php artisan key:generate
```

### 4. Database Kurulumu

```bash
# Database oluştur
mysql -u root -p
CREATE DATABASE serverbond;

# Migration'ları çalıştır
php artisan migrate

# Seed verilerini yükle (admin user + default settings)
php artisan db:seed
```

### 5. Asset'leri Derle

```bash
npm run build
```

### 6. Servisleri Başlat

```bash
# Development
npm run dev

# Production
php artisan serve
php artisan queue:work
```

### 7. Admin Giriş

```
URL: http://localhost:8000/admin
Email: admin@serverbond.local
Password: password
```

## 🎯 Kullanım

### Site Oluşturma

1. **Admin Panel'e Giriş Yapın**
2. **Siteler > Yeni Site**
3. **Temel Bilgileri Doldurun**:
   - Site Adı: "Blog Projesi"
   - Domain: "blog.example.com"
   - Site Tipi: Laravel
   - PHP Versiyonu: 8.4

4. **Git Ayarları**:
   - Repository URL'ini girin
   - Branch seçin (otomatik tespit edilir)
   - Deploy key ekleyin (private repo için)

5. **Database Ayarları**:
   - "Database Oluştur" toggle'ını aktif edin
   - Bilgiler otomatik doldurulur

6. **Kaydet ve Deploy Edin**

### Deployment

#### Otomatik Deployment
```bash
# Git push sonrası webhook ile otomatik
git push origin main
```

#### Manuel Deployment
```bash
# Panel üzerinden "Deploy" butonuna tıklayın
```

### Environment Yönetimi

```bash
# Site düzenleme > Gelişmiş > Environment (.env)
# .env dosyasını doğrudan düzenleyin
# Kaydet > Deploy
```

### Cloudflare Tunnel

1. **Cloudflare Dashboard**: Zero Trust > Tunnels
2. **Create Tunnel**: Token'ı kopyalayın
3. **Site Düzenle**: Gelişmiş > Cloudflare Tunnel
4. **Token'ı Yapıştırın**: Toggle'ı aktif edin
5. **Deploy**: Tunnel otomatik başlar

## 🏗 Mimari

### Klasör Yapısı

```
serverbond-panel/
├── app/
│   ├── Console/          # Artisan komutları
│   ├── Enums/            # Enum sınıfları
│   ├── Filament/         # Admin panel
│   │   ├── Resources/    # CRUD kaynakları
│   │   └── Widgets/      # Dashboard widget'ları
│   ├── Http/
│   │   └── Controllers/  # Controller'lar
│   ├── Models/           # Eloquent modeller
│   └── Services/         # Business logic
├── config/               # Konfigürasyon
├── database/
│   ├── migrations/       # Database migration'ları
│   └── seeders/          # Seed dosyaları
├── resources/
│   ├── css/              # Stil dosyaları
│   ├── js/               # JavaScript
│   └── views/            # Blade template'leri
└── routes/               # Route tanımları
```

### Database Schema

#### sites
```sql
- id
- name
- domain
- type (enum: laravel, php, static, nodejs, python)
- status (enum: active, inactive, deploying, error)
- root_directory
- public_directory
- git_repository
- git_branch
- git_deploy_key (encrypted)
- php_version
- database_name
- database_user
- database_password (encrypted)
- ssl_enabled
- auto_deploy
- deploy_webhook_token (encrypted)
- cloudflare_tunnel_token (encrypted)
- cloudflare_tunnel_id
- cloudflare_tunnel_enabled
- deployment_script
- last_deployed_at
- notes
- timestamps
- soft_deletes
```

#### deployments
```sql
- id
- site_id
- user_id
- status (enum: pending, running, success, failed)
- trigger (enum: manual, webhook, auto)
- commit_hash
- commit_message
- commit_author
- output (text)
- error (text)
- started_at
- finished_at
- duration
- timestamps
```

#### settings
```sql
- id
- group
- key (unique)
- value
- type (enum: string, integer, boolean, json, array)
- label
- description
- is_public
- is_encrypted
- order
- timestamps
```

## 🔧 Servisler

### DeploymentService

Deployment süreçlerini yöneten ana servis.

#### Metotlar

```php
/**
 * Site'yi deploy eder
 * 
 * @param Site $site
 * @param DeploymentTrigger $trigger
 * @param int|null $userId
 * @return Deployment
 */
public function deploy(Site $site, DeploymentTrigger $trigger = DeploymentTrigger::Manual, ?int $userId = null): Deployment
```

**Süreç:**
1. Deployment kaydı oluştur
2. Site statusunu "Deploying" yap
3. Git repository güncelle (clone/pull)
4. .env dosyası senkronize et
5. Deployment script çalıştır
6. Cloudflare Tunnel başlat (aktifse)
7. Sonucu kaydet

**Örnek:**
```php
$deploymentService = app(DeploymentService::class);
$deployment = $deploymentService->deploy($site, DeploymentTrigger::Manual, auth()->id());
```

#### Git İşlemleri

```php
protected function updateRepository(Site $site, Deployment $deployment, string $rootPath, array &$output): void
```

**Süreç:**
1. Repository var mı kontrol et
2. Yoksa clone, varsa pull
3. Deploy key kullanımı (SSH)
4. Commit bilgilerini kaydet

#### Environment Senkronizasyonu

```php
protected function synchronizeEnvironmentFile(Site $site, string $rootPath, array &$output): void
```

**Süreç:**
1. Database credentials al/oluştur
2. Mevcut .env veya .env.example oku
3. Database bilgilerini güncelle
4. .env dosyasını yaz

### MySQLService

MySQL database yönetimi.

#### Metotlar

```php
/**
 * Site için database oluştur
 * 
 * @param Site $site
 * @return array ['success' => bool, 'database' => string, 'user' => string, 'password' => string]
 */
public function createDatabaseForSite(Site $site): array
```

**Süreç:**
1. Database adı oluştur (site bilgisinden)
2. Random güvenli şifre oluştur
3. Database oluştur
4. Kullanıcı oluştur
5. İzinleri ver

**Örnek:**
```php
$result = $mySQLService->createDatabaseForSite($site);
if ($result['success']) {
    echo "Database: {$result['database']}";
    echo "User: {$result['user']}";
    echo "Password: {$result['password']}";
}
```

#### Database Silme

```php
public function deleteDatabaseForSite(Site $site): array
```

### NginxService

Nginx konfigürasyon yönetimi.

#### Metotlar

```php
/**
 * Site için nginx config oluştur
 * 
 * @param Site $site
 * @return string
 */
public function generateConfig(Site $site): string
```

**Site Tipleri:**
- **Laravel**: FastCGI-PHP, URL rewriting
- **PHP**: Standart PHP-FPM
- **Static**: HTML/CSS/JS, caching
- **Node.js**: Reverse proxy (PM2)
- **Python**: Reverse proxy (Gunicorn)

**Örnek:**
```php
$config = $nginxService->generateConfig($site);
$nginxService->writeConfig($site, $config);
$nginxService->enableSite($site);
$nginxService->reload();
```

#### Site Yönetimi

```php
public function enableSite(Site $site): bool
public function disableSite(Site $site): bool
public function reload(): array
public function restart(): array
public function getStatus(): array
```

### CloudflareService

Cloudflare Tunnel yönetimi.

#### Metotlar

```php
/**
 * Token ile tunnel başlat
 * 
 * @param Site $site
 * @return array ['success' => bool, 'message' => string]
 */
public function runTunnelWithToken(Site $site): array
```

**Süreç:**
1. cloudflared kurulu mu kontrol et
2. Systemd service dosyası oluştur
3. Token ile service başlat
4. Auto-restart ayarla

**Örnek:**
```php
$result = $cloudflareService->runTunnelWithToken($site);
if ($result['success']) {
    echo "Tunnel başlatıldı!";
}
```

#### Tunnel Yönetimi

```php
public function stopTunnel(Site $site): array
public function getTunnelStatus(Site $site): array
public function isInstalled(): bool
```

**Service Dosyası:**
```ini
[Unit]
Description=Cloudflare Tunnel for example.com

[Service]
ExecStart=/usr/bin/cloudflared tunnel --no-autoupdate run --token {TOKEN}
Restart=on-failure

[Install]
WantedBy=multi-user.target
```

### SettingService

Uygulama ayarları yönetimi.

#### Metotlar

```php
/**
 * Ayar değeri getir
 * 
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
public function get(string $key, mixed $default = null): mixed

/**
 * Ayar değeri set et
 * 
 * @param string $key
 * @param mixed $value
 * @param string|null $group
 * @param string|null $type
 * @return Setting
 */
public function set(string $key, mixed $value, ?string $group = 'general', ?string $type = null): Setting
```

**Örnek:**
```php
// Ayar oku
$siteName = $settingService->get('site_name', 'ServerBond');

// Ayar yaz
$settingService->set('max_deployments', 10, 'deployment', 'integer');

// Grup oku
$emailSettings = $settingService->getGroupAsArray('email');
```

#### Cache Yönetimi

```php
public function clearCache(): void
public function refresh(): Collection
```

**Cache:**
- 1 saat TTL
- Otomatik invalidation (create/update/delete)
- Laravel cache driver kullanımı

### DeploymentScriptService

Deployment script'leri yönetir.

#### Metotlar

```php
/**
 * Site tipi için varsayılan script
 * 
 * @param SiteType $type
 * @return string
 */
public function getDefaultScript(SiteType $type): string
```

**Script Tipleri:**

**Laravel:**
```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

**Node.js:**
```bash
npm ci --production
pm2 restart ecosystem.config.js --update-env || pm2 start ecosystem.config.js
```

**Python:**
```bash
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
python manage.py migrate --noinput
python manage.py collectstatic --noinput
```

#### Validasyon

```php
public function validateScript(string $script): array
```

**Kontroller:**
- Bash shebang (`#!/bin/bash`)
- Tehlikeli komutlar (`rm -rf /`, `dd if=`)
- Syntax kontrolü

## 🔌 Helper Fonksiyonlar

### Setting Helpers

```php
// Ayar oku
setting('site_name'); // 'ServerBond'
setting('deployment_timeout', 600);

// Grup oku
setting_group('email'); // ['smtp_host' => '...', ...]

// Ayar yaz
setting_set('site_name', 'My Panel');

// Public ayarlar
public_settings(); // Frontend için
```

## 🎨 Form Yapısı

### Site Form

**Tab 1: Temel Bilgiler**
- Site Detayları (Ad, Domain, Tip, Durum)
- Dizin Ayarları (Root, Public, PHP version)

**Tab 2: Git & Deployment**
- Git Repository (URL, Branch, Deploy Key)
- Auto Deploy
- Deployment Script

**Tab 3: Gelişmiş**
- Database (Toggle ile aktif/pasif)
- SSL & Güvenlik
- Cloudflare Tunnel
- Environment (.env)
- Notlar

### Özellikler

- **Otomatik Doldurma**: Site adından domain, database bilgileri
- **Dinamik Görünürlük**: Site tipine göre alanlar
- **Collapsible Sections**: Temiz arayüz
- **Live Validation**: Anlık hata kontrolü
- **Reactive Forms**: Değişikliklere tepki

## 🚦 Deployment Akışı

### 1. Hazırlık
```
Site Oluştur → Git Ayarları → Database Yapılandır → Kaydet
```

### 2. Deployment Başlat
```
Deploy Butonu → Deployment Kaydı → Status: Deploying
```

### 3. Repository İşlemleri
```
Check Directory → Clone/Pull → Commit Info → Branch Switch
```

### 4. Environment Hazırlık
```
Database Provision → .env Template → Database Config → .env Write
```

### 5. Script Çalıştırma
```
Script Upload → Make Executable → Execute → Capture Output
```

### 6. Cloudflare Tunnel (Opsiyonel)
```
Check Token → Create Service → Start Tunnel → Systemd Enable
```

### 7. Sonuç
```
Success/Failed → Status Update → Logs Save → Notification
```

## 📊 Config Dosyaları

### deployment.php

```php
return [
    'paths' => [
        'deploy_keys' => storage_path('app/deploy-keys'),
        'script_name' => 'deploy-script.sh',
    ],
    
    'timeout' => env('DEPLOYMENT_TIMEOUT', 600),
    
    'git' => [
        'default_branch' => env('GIT_DEFAULT_BRANCH', 'main'),
        'api_timeout' => 3,
        'user_agent' => 'ServerBond',
    ],
    
    'nginx' => [
        'sites_available' => '/etc/nginx/sites-available',
        'sites_enabled' => '/etc/nginx/sites-enabled',
        'default_php_version' => '8.4',
    ],
    
    'ports' => [
        'nodejs' => 3000,
        'python' => 8000,
    ],
];
```

## 🔐 Güvenlik

### Şifreleme
- Database passwords (Laravel Encryption)
- Git deploy keys (Laravel Encryption)
- Webhook tokens (Laravel Encryption)
- Cloudflare tunnel tokens (Laravel Encryption)

### Validasyon
- Domain format kontrolü
- Repository URL validation
- Database name regex (`[a-zA-Z0-9_]`)
- Script güvenlik kontrolü

### İzinler
- Filament policies
- Role-based access
- User-site relationships

## 🧪 Testing

```bash
# Unit testler
php artisan test --filter=Unit

# Feature testler
php artisan test --filter=Feature

# Specific test
php artisan test --filter=DeploymentServiceTest
```

## 📝 Changelog

### v1.0.0 (2025-10-21)
- ✅ Site yönetimi (Multi-type support)
- ✅ Otomatik deployment
- ✅ Database yönetimi
- ✅ Nginx konfigürasyon
- ✅ SSL desteği
- ✅ Cloudflare Tunnel
- ✅ Settings sistemi
- ✅ Helper fonksiyonlar

## 🤝 Katkıda Bulunma

1. Fork edin
2. Feature branch oluşturun (`git checkout -b feature/amazing`)
3. Commit edin (`git commit -m 'Add amazing feature'`)
4. Push edin (`git push origin feature/amazing`)
5. Pull Request açın

## 📄 Lisans

MIT License - Detaylar için [LICENSE](LICENSE) dosyasına bakın.

## 🙏 Teşekkürler

- [Laravel](https://laravel.com)
- [Filament](https://filamentphp.com)
- [Livewire](https://livewire.laravel.com)
- [Tailwind CSS](https://tailwindcss.com)

## 📞 İletişim

- Website: [serverbond.com](https://serverbond.com)
- Email: info@serverbond.com
- GitHub: [@serverbond](https://github.com/serverbond)

---

**Made with ❤️ by ServerBond Team**
