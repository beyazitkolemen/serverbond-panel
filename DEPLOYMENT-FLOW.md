# 🚀 Deployment Akışı

## Veritabanı Oluşturma Sırası

### 1. Deployment Başlatılır

```php
DeploymentService::deploy($site)
```

### 2. Deployment Süreci

```
DeploymentService::runDeployment()
│
├─ 1. Git Repository Update (Opsiyonel)
│  └─ updateRepository()
│     ├─ Clone (ilk deployment)
│     └─ Pull (sonraki deployment'lar)
│
├─ 2. Environment File Sync ⭐ BURADA VERITABANI OLUŞUR
│  └─ synchronizeEnvironmentFile()
│     │
│     ├─ A. provisionDatabase() ⭐⭐ VERITABANI OLUŞTURMA
│     │  │
│     │  ├─ Site'de bilgi var mı kontrol et
│     │  │  ├─ VAR → Mevcut bilgileri kullan (MySQL'e bağlanma)
│     │  │  └─ YOK → MySQLService::createDatabaseForSite()
│     │  │
│     │  └─ MySQLService::createDatabaseForSite()
│     │     │
│     │     ├─ 1. MySQL bağlantı testi
│     │     ├─ 2. Credentials oluştur (slug'dan)
│     │     ├─ 3. CREATE DATABASE
│     │     ├─ 4. CREATE USER
│     │     ├─ 5. GRANT PRIVILEGES
│     │     ├─ 6. Site'ye kaydet
│     │     └─ 7. Credentials döndür
│     │
│     ├─ B. Site refresh (yeni credentials için)
│     │
│     ├─ C. resolveBaseEnvironmentContent()
│     │  ├─ Mevcut .env varsa oku
│     │  ├─ .env.example varsa oku
│     │  └─ Template oluştur
│     │
│     └─ D. applyDatabaseConfiguration()
│        └─ .env dosyasına MySQL bilgilerini yaz
│
├─ 3. Deployment Script Çalıştır
│  └─ runDeploymentScript()
│     ├─ composer install
│     ├─ php artisan migrate
│     ├─ cache clear/optimize
│     └─ permissions
│
└─ 4. Cloudflare Tunnel (Opsiyonel)
   └─ startCloudfareTunnel()
```

## 📊 Detaylı Veritabanı Oluşturma Akışı

### Adım 1: Site'de Bilgi Kontrolü

```php
// EnvironmentService::provisionDatabase()

if ($site->database_name && $site->database_user && $site->database_password) {
    // ✅ Mevcut bilgileri kullan
    return [
        'database' => $site->database_name,
        'username' => $site->database_user,
        'password' => $site->database_password,
    ];
}

// ❌ Bilgi yok, MySQL'de oluştur
$result = $this->mySQLService->createDatabaseForSite($site);
```

### Adım 2: MySQL'de Oluşturma

```php
// MySQLService::createDatabaseForSite()

// A. Slug oluştur
$slug = str_replace(['.', '-'], '_', $site->domain);
// deneme1.test → deneme1_test

// B. Credentials oluştur
$dbName = 'sb_' . $slug . '_db';      // sb_deneme1_test_db
$dbUser = 'sb_' . $slug . '_user';    // sb_deneme1_test_user
$dbPassword = Str::random(32);        // abc123...xyz789

// C. MySQL komutları çalıştır
CREATE DATABASE IF NOT EXISTS `sb_deneme1_test_db`;
CREATE USER IF NOT EXISTS 'sb_deneme1_test_user'@'%' IDENTIFIED BY 'abc123...';
GRANT ALL PRIVILEGES ON `sb_deneme1_test_db`.* TO 'sb_deneme1_test_user'@'%';
FLUSH PRIVILEGES;

// D. Site'ye kaydet
$site->update([
    'database_name' => 'sb_deneme1_test_db',
    'database_user' => 'sb_deneme1_test_user',
    'database_password' => 'abc123...xyz789', // plain text
]);

// E. Credentials döndür
return [
    'success' => true,
    'database' => 'sb_deneme1_test_db',
    'user' => 'sb_deneme1_test_user',
    'password' => 'abc123...xyz789',
];
```

### Adım 3: .env Dosyasına Yazma

```php
// EnvironmentService::applyDatabaseConfiguration()

// Satır satır işle
foreach ($lines as $line) {
    if (preg_match('/^DB_CONNECTION=/i', $line)) {
        $line = 'DB_CONNECTION=mysql';
    }
    elseif (preg_match('/^#\s*DB_HOST=/i', $line)) {
        $line = 'DB_HOST=127.0.0.1';  // # kaldır
    }
    elseif (preg_match('/^#\s*DB_DATABASE=/i', $line)) {
        $line = 'DB_DATABASE=sb_deneme1_test_db';  // # kaldır, değer ekle
    }
    // ... diğer DB_ key'ler
}

// Sonuç .env:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sb_deneme1_test_db
DB_USERNAME=sb_deneme1_test_user
DB_PASSWORD=abc123...xyz789
```

## ⏱ Zaman Çizelgesi

```
T+0s   : Deployment başlat
T+1s   : Git pull/clone
T+3s   : MySQL bağlantı testi
T+4s   : CREATE DATABASE ⭐
T+5s   : CREATE USER ⭐
T+6s   : GRANT PRIVILEGES ⭐
T+7s   : Site'ye credentials kaydet ⭐
T+8s   : .env dosyası oluştur/güncelle
T+10s  : Deployment script (composer, migrate, etc)
T+60s  : Deployment tamamlandı
```

## 🔍 Hangi Durumda Ne Olur?

### Durum 1: Yeni Site (İlk Deployment)
```
Site'de database bilgisi: YOK
↓
MySQL'de oluştur
↓
Site'ye kaydet
↓
.env'e yaz
```

### Durum 2: Mevcut Site (2. Deployment)
```
Site'de database bilgisi: VAR
↓
MySQL'e bağlanma (atla)
↓
Mevcut bilgileri kullan
↓
.env'e yaz
```

### Durum 3: MySQL Başarısız
```
MySQL bağlantı hatası
↓
Exception fırlat
↓
EnvironmentService catch eder
↓
.env'i template ile oluştur (boş DB bilgileri)
↓
Deployment devam eder (warning ile)
```

## 📝 Özet

**Veritabanı Oluşturma:**
- **Ne zaman:** Deployment sırasında, .env oluşturulmadan hemen önce
- **Nerede:** `EnvironmentService::synchronizeEnvironmentFile()`
- **Kim:** `MySQLService::createDatabaseForSite()`
- **Kaç kere:** İlk deployment'ta 1 kere (sonra mevcut bilgileri kullanır)

**Sıralama:**
1. Git işlemleri
2. **→ MySQL veritabanı oluştur** ⭐
3. .env dosyası yaz
4. Deployment script
5. Cloudflare tunnel

