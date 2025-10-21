# Sudo Yetkisi Kurulum Talimatları

ServerBond Panel'in Nginx ve Cloudflare Tunnel yönetebilmesi için Laravel kullanıcısının bazı sistem komutlarını sudo ile çalıştırabilmesi gerekiyor.

## Kurulum

### 1. Sudoers Dosyası Oluştur

```bash
sudo visudo -f /etc/sudoers.d/serverbond
```

### 2. Aşağıdaki İçeriği Ekleyin

**⚠️ Önemli**: `www-data` yerine kendi web server kullanıcınızı yazın:
- Ubuntu/Debian Nginx: `www-data`
- Laravel Sail: `sail`  
- Docker: `www-data` veya `sail`

```bash
# ServerBond Panel - Nginx Yönetimi
www-data ALL=(ALL) NOPASSWD: /bin/mv /tmp/*.conf /etc/nginx/sites-available/
www-data ALL=(ALL) NOPASSWD: /bin/chmod 644 /etc/nginx/sites-available/*.conf
www-data ALL=(ALL) NOPASSWD: /bin/ln -sf /etc/nginx/sites-available/*.conf /etc/nginx/sites-enabled/
www-data ALL=(ALL) NOPASSWD: /bin/rm /etc/nginx/sites-enabled/*.conf
www-data ALL=(ALL) NOPASSWD: /usr/sbin/nginx -t
www-data ALL=(ALL) NOPASSWD: /bin/systemctl reload nginx
www-data ALL=(ALL) NOPASSWD: /bin/systemctl restart nginx
www-data ALL=(ALL) NOPASSWD: /bin/systemctl status nginx

# ServerBond Panel - Cloudflare Tunnel Yönetimi
www-data ALL=(ALL) NOPASSWD: /bin/systemctl start cloudflared-*
www-data ALL=(ALL) NOPASSWD: /bin/systemctl stop cloudflared-*
www-data ALL=(ALL) NOPASSWD: /bin/systemctl enable cloudflared-*
www-data ALL=(ALL) NOPASSWD: /bin/systemctl disable cloudflared-*
www-data ALL=(ALL) NOPASSWD: /bin/systemctl is-active cloudflared-*
www-data ALL=(ALL) NOPASSWD: /bin/systemctl daemon-reload
www-data ALL=(ALL) NOPASSWD: /bin/mv /tmp/cloudflared-*.service /etc/systemd/system/
www-data ALL=(ALL) NOPASSWD: /bin/chmod 644 /etc/systemd/system/cloudflared-*.service
www-data ALL=(ALL) NOPASSWD: /bin/rm /etc/systemd/system/cloudflared-*.service
```

### 3. Dosya İzinlerini Ayarla

```bash
sudo chmod 0440 /etc/sudoers.d/serverbond
```

### 4. Syntax Kontrolü

```bash
sudo visudo -c
```

Çıktı şu şekilde olmalı:
```
/etc/sudoers: parsed OK
/etc/sudoers.d/serverbond: parsed OK
```

## Kullanıcı Adını Bulma

Eğer web server kullanıcı adınızdan emin değilseniz:

```bash
# PHP-FPM kullanıyorsanız
ps aux | grep php-fpm | grep -v grep | head -1

# Nginx kullanıyorsanız  
ps aux | grep nginx | grep worker | head -1

# Laravel Sail kullanıyorsanız
whoami  # Container içinde çalıştırın
```

## Test Etme

### Nginx Test

```bash
# Kullanıcı olarak test edin (www-data örneği)
sudo -u www-data sudo nginx -t
```

Başarılı çıktı:
```
nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
nginx: configuration file /etc/nginx/nginx.conf test is successful
```

### Cloudflare Tunnel Test

```bash
sudo -u www-data sudo systemctl daemon-reload
```

Hata vermemeli.

## Güvenlik Notları

### ✅ Güvenli Yapılandırma

- **Wildcard Sınırlaması**: Sadece belirli pattern'ler (`*.conf`, `cloudflared-*`) izin veriliyor
- **NOPASSWD**: Otomasyon için şifre sormuyor (gerekli)
- **Spesifik Komutlar**: Sadece belirli komutlar çalıştırılabiliyor
- **0440 İzinleri**: Dosya sadece root tarafından düzenlenebilir

### ⚠️ Riskler

- Kullanıcı nginx config dosyaları oluşturabilir (yanlış config ile nginx çökebilir)
- Kullanıcı nginx reload/restart yapabilir (servis kesintisi oluşturabilir)
- Kullanıcı cloudflare tunnel service'leri yönetebilir

Bu riskler ServerBond Panel'in çalışması için gereklidir.

## Sorun Giderme

### Permission Denied Hatası

```bash
# 1. Sudoers dosyasını kontrol edin
sudo cat /etc/sudoers.d/serverbond

# 2. Kullanıcı adını doğrulayın
whoami

# 3. Syntax hatasını kontrol edin
sudo visudo -c

# 4. Log'ları kontrol edin
sudo tail -f /var/log/auth.log  # Ubuntu/Debian
sudo tail -f /var/log/secure     # CentOS/RHEL
```

### "Sorry, user www-data is not allowed to execute..."

Bu hata, sudoers dosyasındaki komut ile çalıştırılan komutun birebir eşleşmediği anlamına gelir.

```bash
# Gerçek komut:
/usr/sbin/nginx -t

# Sudoers'da tanımlı:
/bin/nginx -t  # ❌ YANLIŞ PATH

# Düzeltme: which ile gerçek path'i bulun
which nginx
# Çıktı: /usr/sbin/nginx

# Sudoers'ı güncelleyin
sudo visudo -f /etc/sudoers.d/serverbond
```

### Docker/Sail Ortamları

Docker container'ı içinde sudo genelde yüklü olmayabilir:

```bash
# Container içinde
apt-get update && apt-get install -y sudo

# Veya docker-compose.yml'de
RUN apt-get update && apt-get install -y sudo
```

## Alternatif Yaklaşımlar

### Yöntem 1: Web Server Kullanıcısını Root Yapma (ÖNERİLMEZ)

```bash
# ❌ GÜVENSİZ - Kullanmayın
usermod -aG root www-data
```

### Yöntem 2: Dosya İzinlerini Gevşetme (ÖNERİLMEZ)

```bash
# ❌ GÜVENSİZ - Kullanmayın
chmod 777 /etc/nginx/sites-available
chmod 777 /etc/nginx/sites-enabled
```

### Yöntem 3: Sudo ile Sınırlı Yetkiler (✅ ÖNERİLEN)

Yukarıdaki kurulum talimatları bu yöntemi kullanır.

## Daha Fazla Bilgi

- [Sudo Dokümantasyonu](https://www.sudo.ws/docs/man/1.8.27/sudoers.man/)
- [Nginx Dokümantasyonu](https://nginx.org/en/docs/)
- [Systemd Dokümantasyonu](https://www.freedesktop.org/software/systemd/man/systemctl.html)

