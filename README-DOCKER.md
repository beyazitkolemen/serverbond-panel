# Docker Kurulum Rehberi

## Gerekli Ortam Değişkenleri (.env)

`.env` dosyanızı oluşturmak için aşağıdaki komutu çalıştırın:

```bash
cp .env.example .env
```

Eğer `.env.example` dosyası yoksa, aşağıdaki içeriği `.env` dosyanıza ekleyin:

### Veritabanı Ayarları (MySQL)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=serverbond
DB_USERNAME=serverbond
DB_PASSWORD=secret
```

### Redis Ayarları

```env
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
```

### Cache ve Queue Ayarları

```env
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=database
```

### phpMyAdmin Port (Opsiyonel)

```env
PHPMYADMIN_PORT=8080
```

### Uygulama Ayarları

```env
APP_NAME=ServerBond
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost
APP_LOCALE=tr
```

## Kurulum Adımları

### 1. Docker Container'ları Başlatma

```bash
# Container'ları arka planda başlat
docker-compose up -d

# Logları görüntüle
docker-compose logs -f

# Sadece belirli bir servisin loglarını görüntüle
docker-compose logs -f mysql
docker-compose logs -f redis
```

### 2. Laravel Uygulama Anahtarı Oluşturma

```bash
php artisan key:generate
```

### 3. Veritabanı Migration'larını Çalıştırma

```bash
# Migration'ları çalıştır
php artisan migrate

# Seed'leri de çalıştırmak için
php artisan migrate --seed
```

### 4. Admin Kullanıcı Oluşturma

```bash
php artisan db:seed --class=AdminUserSeeder
```

## Kullanışlı Docker Komutları

### Container Yönetimi

```bash
# Container'ları durdur
docker-compose down

# Container'ları durdur ve volume'ları sil
docker-compose down -v

# Container'ları yeniden başlat
docker-compose restart

# Belirli bir servisi yeniden başlat
docker-compose restart mysql
docker-compose restart redis
```

### Container İçine Giriş

```bash
# MySQL container'ına bağlan
docker exec -it serverbond_mysql mysql -u serverbond -psecret serverbond

# Redis container'ına bağlan
docker exec -it serverbond_redis redis-cli
```

### Log İzleme

```bash
# Tüm servislerin loglarını izle
docker-compose logs -f

# Sadece MySQL loglarını izle
docker-compose logs -f mysql

# Son 100 satırı göster
docker-compose logs --tail=100
```

### Durum Kontrolü

```bash
# Çalışan container'ları listele
docker-compose ps

# Container'ların sağlık durumunu kontrol et
docker-compose ps
```

## Servis Erişim Bilgileri

### MySQL
- **Host**: 127.0.0.1 (localhost)
- **Port**: 3306
- **Database**: serverbond
- **Username**: serverbond
- **Password**: secret

### Redis
- **Host**: 127.0.0.1 (localhost)
- **Port**: 6379
- **Password**: (boş - isteğe bağlı olarak ayarlayabilirsiniz)

### phpMyAdmin
- **URL**: http://localhost:8080
- **Username**: serverbond
- **Password**: secret

## Önemli Notlar

1. **Production Ortamı**: Production ortamında mutlaka güçlü şifreler kullanın!
   
2. **Port Çakışması**: Eğer yerel makinenizde MySQL veya Redis çalışıyorsa, `.env` dosyasındaki portları değiştirin:
   ```env
   DB_PORT=3307
   REDIS_PORT=6380
   PHPMYADMIN_PORT=8081
   ```

3. **Redis Şifresi**: Güvenlik için Redis şifresi ayarlamak isterseniz:
   ```env
   REDIS_PASSWORD=your-secure-password
   ```

4. **Veri Kalıcılığı**: Docker volume'lar sayesinde container'ları silseniz bile verileriniz korunur. Volume'ları da silmek için `docker-compose down -v` komutunu kullanın.

5. **Performance**: MySQL için ayrılan bellek ve diğer ayarlar `docker/mysql/my.cnf` dosyasından özelleştirilebilir.

## Sorun Giderme

### Port zaten kullanımda hatası
```bash
# Çalışan servisi kontrol edin
sudo lsof -i :3306
sudo lsof -i :6379

# .env dosyasında farklı portlar kullanın
```

### Container başlatılamıyor
```bash
# Container'ları temizle
docker-compose down -v

# Docker cache'i temizle
docker system prune -a

# Yeniden başlat
docker-compose up -d
```

### MySQL bağlantı hatası
```bash
# Container'ın çalıştığından emin olun
docker-compose ps

# MySQL loglarını kontrol edin
docker-compose logs mysql

# Health check durumunu kontrol edin
docker inspect serverbond_mysql | grep -A 10 Health
```

### Redis bağlantı hatası
```bash
# Redis'in çalıştığını kontrol edin
docker exec -it serverbond_redis redis-cli ping

# Şifre ayarlıysa
docker exec -it serverbond_redis redis-cli -a your-password ping
```

## Ek Bilgiler

### Cache Temizleme
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Queue Worker Çalıştırma
```bash
# Queue worker'ı başlat
php artisan queue:work

# Belirli bir queue'yu çalıştır
php artisan queue:work redis --queue=default
```

### Backup Alma

#### MySQL Backup
```bash
# Backup al
docker exec serverbond_mysql mysqldump -u serverbond -psecret serverbond > backup.sql

# Backup'ı geri yükle
docker exec -i serverbond_mysql mysql -u serverbond -psecret serverbond < backup.sql
```

#### Redis Backup
```bash
# Redis verilerini kaydet
docker exec serverbond_redis redis-cli SAVE

# Backup dosyasını kopyala
docker cp serverbond_redis:/data/dump.rdb ./redis-backup.rdb
```

