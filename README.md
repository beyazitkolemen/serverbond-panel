# ServerBond Panel

**ServerBond Agent** için geliştirilmiş, modern ve kullanıcı dostu web tabanlı sunucu yönetim paneli.

## 🚀 Özellikler

### Multi-Site Yönetimi
- ✅ **Çoklu Site Desteği**: Laravel, PHP, Static HTML, Node.js ve Python uygulamaları
- ✅ **Otomatik Nginx Konfigürasyonu**: Her site tipi için optimize edilmiş yapılandırma
- ✅ **Git Entegrasyonu**: Otomatik deployment ve branch yönetimi
- ✅ **SSL/TLS Yönetimi**: Let's Encrypt ve özel sertifika desteği

### Deployment & CI/CD
- ✅ **Tek Tıkla Deployment**: Manuel, otomatik ve webhook tetikleyicileri
- ✅ **Deployment Geçmişi**: Tüm deployment'ların detaylı takibi
- ✅ **Commit Bilgileri**: Her deployment için commit hash, mesaj ve yazar
- ✅ **Gerçek Zamanlı Loglar**: Deployment sürecinin anlık takibi

### Database Yönetimi
- ✅ **Otomatik MySQL Database**: Her site için ayrı database oluşturma
- ✅ **Kullanıcı & Yetki Yönetimi**: Güvenli database kullanıcıları
- ✅ **Database Metrikleri**: Boyut ve kullanım istatistikleri

### Sistem Monitoring
- ✅ **Gerçek Zamanlı Metrikler**: CPU, RAM ve Disk kullanımı
- ✅ **Sunucu Bilgileri**: OS versiyonu, uptime, load average
- ✅ **Site İstatistikleri**: Aktif/inaktif site sayıları
- ✅ **Deployment Başarı Oranı**: Performans takibi

### Environment Yönetimi
- ✅ **`.env` Dosya Düzenleyici**: Her site için ayrı environment değişkenleri
- ✅ **Güvenli Saklama**: Secret değişkenler için şifrelenmiş depolama
- ✅ **Toplu Düzenleme**: Kolay yönetim arayüzü

## 📋 Gereksinimler

- **Ubuntu 24.04 LTS** (ServerBond Agent ile kurulmuş)
- **PHP 8.2+**
- **Composer**
- **Node.js & NPM**
- **MySQL 8.0**
- **Nginx**

## ⚡ Kurulum

### 1. ServerBond Agent Kurulumu

Öncelikle sunucunuza [ServerBond Agent](https://github.com/beyazitkolemen/serverbond-agent) kurulumunu yapın:

\`\`\`bash
curl -fsSL https://raw.githubusercontent.com/beyazitkolemen/serverbond-agent/main/install.sh | sudo bash
\`\`\`

### 2. Panel Kurulumu

\`\`\`bash
# Projeyi klonlayın
git clone https://github.com/beyazitkolemen/serverbond-panel.git
cd serverbond-panel

# Bağımlılıkları yükleyin
composer install
npm install

# Environment dosyasını oluşturun
cp .env.example .env
php artisan key:generate

# Database oluşturun
php artisan migrate --seed

# Asset'leri derleyin
npm run build

# Admin kullanıcısı oluşturun (seeder ile otomatik)
# Email: admin@serverbond.local
# Password: password
\`\`\`

### 3. Nginx Konfigürasyonu

\`\`\`nginx
server {
    listen 80;
    server_name panel.yourdomain.com;
    root /var/www/serverbond-panel/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \\.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\\.(?!well-known).* {
        deny all;
    }
}
\`\`\`

### 4. İzinleri Ayarlayın

\`\`\`bash
sudo chown -R www-data:www-data /var/www/serverbond-panel
sudo chmod -R 755 /var/www/serverbond-panel
sudo chmod -R 775 /var/www/serverbond-panel/storage
sudo chmod -R 775 /var/www/serverbond-panel/bootstrap/cache
\`\`\`

## 🎯 Kullanım

### İlk Giriş

Tarayıcınızda panel adresinize gidin ve aşağıdaki bilgilerle giriş yapın:

- **Email**: `admin@serverbond.local`
- **Şifre**: `password`

> ⚠️ İlk girişten sonra şifrenizi mutlaka değiştirin!

### Yeni Site Ekleme

1. **Sites** menüsünden **Create** butonuna tıklayın
2. Site bilgilerini doldurun:
   - **Name**: Site adı
   - **Domain**: Alan adı (örn: example.com)
   - **Type**: Site tipi (Laravel, PHP, Static, Node.js, Python)
   - **Git Repository**: Repository URL'i
   - **Git Branch**: Branch adı (main, master, develop vs.)
3. **Save** butonuna tıklayın
4. Site oluşturulduktan sonra **Deploy** butonuyla ilk deployment'ı yapın

### Deployment

#### Manuel Deployment
1. Site detay sayfasında **Deploy** butonuna tıklayın
2. Deployment sürecini loglardan takip edin

#### Otomatik Deployment
1. Site düzenleme sayfasında **Auto Deploy** seçeneğini aktifleştirin
2. Her git push'ta otomatik deployment yapılır

#### Webhook Deployment
1. Site için webhook token'ı oluşturun
2. Git repository'nizde webhook ayarlayın
3. Her push'ta otomatik deployment tetiklenir

### SSL Sertifikası Ekleme

1. Site detay sayfasında **SSL Certificate** sekmesine gidin
2. **Let's Encrypt** veya **Custom Certificate** seçin
3. Gerekli bilgileri doldurun ve kaydedin

## 🛠️ Teknoloji Stack

- **Laravel 12** - PHP Framework
- **Filament v4** - Admin Panel
- **Livewire 3** - Reactive Components  
- **Alpine.js** - JavaScript Framework
- **Tailwind CSS** - CSS Framework
- **MySQL 8** - Database
- **Redis** - Cache & Sessions

## 📊 Dashboard Widgets

### Server Stats Widget
- CPU kullanımı (%)
- RAM kullanımı (GB)
- Disk kullanımı (GB)
- Sistem bilgileri

### Sites Stats Widget
- Toplam site sayısı
- Aktif/İnaktif site sayıları
- Deployment istatistikleri
- Başarı oranı

## 🔒 Güvenlik

- ✅ Laravel Sanctum Authentication
- ✅ CSRF Protection
- ✅ XSS Protection
- ✅ SQL Injection Protection
- ✅ Şifrelenmiş hassas veriler (database passwords, API keys)
- ✅ Rate Limiting

## 🤝 Katkıda Bulunma

1. Fork yapın
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Commit yapın (`git commit -m 'feat: Add amazing feature'`)
4. Push yapın (`git push origin feature/amazing-feature`)
5. Pull Request açın

## 📝 Lisans

MIT License - Detaylar için [LICENSE](LICENSE) dosyasına bakın.

## 📧 İletişim

- **GitHub**: [github.com/beyazitkolemen/serverbond-panel](https://github.com/beyazitkolemen/serverbond-panel)
- **Issues**: [github.com/beyazitkolemen/serverbond-panel/issues](https://github.com/beyazitkolemen/serverbond-panel/issues)

## 🙏 Teşekkürler

Bu proje [ServerBond Agent](https://github.com/beyazitkolemen/serverbond-agent) ile birlikte çalışacak şekilde tasarlanmıştır.

---

**ServerBond Panel** - Professional server management made easy! 🚀
