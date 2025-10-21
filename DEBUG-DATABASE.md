# Database .env Yazma Sorunu - Debug Rehberi

## 🔍 Sorun

.env dosyasındaki database değerleri boş geliyor:
```env
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

## 🛠 Eklenen Debug Log'ları

Deployment sırasında şu log'lar artık yazılacak:

### 1. MySQLService Log'ları

```
[info] MySQLService: Using existing credentials
[info] MySQLService: Generated new credentials
[info] MySQLService: Saved credentials to site
```

**Kontrol Edilecek:**
- Database adı, user ve password'ün uzunluğu
- Credentials'ın düzgün oluşturulup oluşturulmadığı

### 2. EnvironmentService Log'ları

```
[info] EnvironmentService: Applying database config
  - has_database: true/false
  - has_username: true/false
  - has_password: true/false
  - database: [değer]
  - username: [değer]
  - password_length: [uzunluk]

[info] EnvironmentService: Database config applied successfully
[warning] EnvironmentService: Database config skipped - missing credentials
```

### 3. setEnvValue Log'ları

```
[debug] ENV: Updated DB_DATABASE
[debug] ENV: Updated DB_USERNAME
[debug] ENV: Updated DB_PASSWORD
[debug] ENV: Added DB_PASSWORD to DB section
[debug] ENV: Added DB_PASSWORD to end
```

## 📋 Test Adımları

### 1. Log Dosyasını Temizle
```bash
echo "" > storage/logs/laravel.log
```

### 2. Site Oluştur ve Deploy Et
1. Admin panel'de yeni site oluştur
2. Site'yi deploy et
3. Deployment log'unu oku

### 3. Log'ları Kontrol Et
```bash
tail -100 storage/logs/laravel.log | grep -E "(MySQLService|EnvironmentService|ENV:)"
```

### 4. .env Dosyasını Kontrol Et
```bash
cat /srv/serverbond/sites/[domain]/.env | grep DB_
```

## 🔧 Olası Sorunlar ve Çözümler

### Sorun 1: MySQLService Success False Dönüyor

**Log'da göreceksiniz:**
```
[info] MySQLService: Generated new credentials
// Ama sonrasında success log'u yok
```

**Çözüm:**
- MySQL bağlantı bilgilerini kontrol edin
- MySQL servisinin çalıştığından emin olun
```bash
systemctl status mysql
mysql -u root -p
```

### Sorun 2: Credentials Boş Geliyor

**Log'da göreceksiniz:**
```
[info] EnvironmentService: Applying database config
  - database: EMPTY
  - username: EMPTY
```

**Çözüm:**
- MySQLService'in return değerini kontrol edin
- Site update işleminin başarılı olup olmadığını kontrol edin

### Sorun 3: setEnvValue Çalışmıyor

**Log'da göreceksiniz:**
```
[debug] ENV: Updated DB_DATABASE = ""
[debug] ENV: Updated DB_USERNAME = ""
```

**Çözüm:**
- Credentials'ın normalizeEnvValue'ya doğru gelip gelmediğini kontrol edin
- Pattern matching'in çalışıp çalışmadığını kontrol edin

### Sorun 4: .env Dosyası Yazılamıyor

**Çözüm:**
```bash
# Klasör izinlerini kontrol et
ls -la /srv/serverbond/sites/[domain]/

# .env dosyasının oluşturulup oluşturulmadığını kontrol et
ls -la /srv/serverbond/sites/[domain]/.env

# İzinleri düzelt
chmod 755 /srv/serverbond/sites/[domain]/
chmod 644 /srv/serverbond/sites/[domain]/.env
```

## 🎯 Beklenen Log Akışı (Başarılı)

```
[info] Provisioning MySQL database...
[info] MySQLService: Generated new credentials
  - db: sb_mysite_db
  - user: sb_mysite_user
  - pass_length: 32
[info] MySQLService: Saved credentials to site
  - site_id: 1
[info] ✓ Database ready: sb_mysite_user@sb_mysite_db (password: 32 chars)
[info] EnvironmentService: Applying database config
  - has_database: true
  - has_username: true
  - has_password: true
  - database: sb_mysite_db
  - username: sb_mysite_user
  - password_length: 32
[debug] ENV: Updated DB_DATABASE
  - value: sb_mysite_db
[debug] ENV: Updated DB_USERNAME
  - value: sb_mysite_user
[debug] ENV: Updated DB_PASSWORD
  - value: [32 chars]
[info] EnvironmentService: Database config applied successfully
[info] .env file synchronized successfully
```

## 🚀 Hızlı Test

```bash
# 1. Log temizle
echo "" > storage/logs/laravel.log

# 2. Test site oluştur
# Admin panel'den:
# - Ad: Test Site
# - Domain: test.local
# - Tip: Laravel

# 3. Deploy et ve log'u oku
tail -f storage/logs/laravel.log

# 4. Sonuç kontrol
cat /srv/serverbond/sites/test.local/.env | grep DB_
```

## 📞 Sonuç Raporlama

Lütfen log çıktısını paylaşın:
```bash
tail -100 storage/logs/laravel.log | grep -E "(MySQLService|EnvironmentService|ENV:)" > debug-output.txt
```

Bu şekilde sorunu tam olarak tespit edebiliriz.

