# Settings (Ayarlar) Sistemi

ServerBond Panel için esnek ve güçlü bir ayarlar yönetim sistemi.

## Özellikler

- ✅ Key-value tabanlı ayar yapısı
- ✅ Grup desteği (general, email, deployment, security, vb.)
- ✅ Tip desteği (string, integer, boolean, json, array)
- ✅ Şifreleme desteği (hassas bilgiler için)
- ✅ Public/Private ayar ayrımı
- ✅ Cache desteği (performans için)
- ✅ Filament admin paneli entegrasyonu
- ✅ Helper fonksiyonlar

## Kurulum

### 1. Migration Çalıştır

```bash
php artisan migrate
```

### 2. Varsayılan Ayarları Yükle

```bash
php artisan db:seed --class=SettingSeeder
```

### 3. Composer Autoload

```bash
composer dump-autoload
```

## Kullanım

### Helper Fonksiyonlar ile

#### Ayar Okuma

```php
// Basit kullanım
$siteName = setting('site_name');

// Varsayılan değer ile
$timeout = setting('deployment_timeout', 600);

// Grup olarak al
$emailSettings = setting_group('email');
// ['smtp_host' => '...', 'smtp_port' => 587, ...]
```

#### Ayar Yazma

```php
// Ayar oluştur/güncelle
setting_set('site_name', 'ServerBond Panel');

// Grup ve tip belirterek
setting_set('max_deployments', 10, 'deployment', 'integer');
```

#### Public Ayarlar

```php
// Tüm public ayarları al (frontend için)
$publicSettings = public_settings();
```

### Service Sınıfı ile

```php
use App\Services\SettingService;

class MyController extends Controller
{
    public function __construct(
        private readonly SettingService $settingService
    ) {}

    public function index()
    {
        // Tüm ayarları getir
        $all = $this->settingService->all();

        // Tek bir ayar
        $value = $this->settingService->get('site_name', 'Default');

        // Grup olarak
        $emailSettings = $this->settingService->getGroup('email');
        $emailArray = $this->settingService->getGroupAsArray('email');

        // Ayar varsa kontrol et
        if ($this->settingService->has('site_name')) {
            // ...
        }

        // Ayar oluştur/güncelle
        $this->settingService->set('site_name', 'New Name');

        // Grup güncelleme
        $this->settingService->setGroup('email', [
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
        ]);

        // Ayar sil
        $this->settingService->delete('old_setting');

        // Cache temizle
        $this->settingService->clearCache();

        // Yeniden yükle
        $this->settingService->refresh();
    }
}
```

### Model ile Doğrudan Kullanım

```php
use App\Models\Setting;

// Yeni ayar oluştur
Setting::create([
    'group' => 'general',
    'key' => 'site_name',
    'value' => 'ServerBond',
    'type' => 'string',
    'label' => 'Site Adı',
    'description' => 'Uygulamanın görünen adı',
    'is_public' => true,
    'order' => 1,
]);

// Formatted value ile al
$setting = Setting::where('key', 'site_name')->first();
$value = $setting->formatted_value; // Tip dönüşümü yapılmış değer

// Formatted value set et
$setting->setFormattedValue('New Value');
$setting->save();

// Scope'lar
$generalSettings = Setting::group('general')->get();
$publicSettings = Setting::public()->get();
$orderedSettings = Setting::ordered()->get();
```

## Ayar Tipleri

### String
Metin değerler için:
```php
setting_set('site_name', 'ServerBond', 'general', 'string');
```

### Integer
Sayısal değerler için:
```php
setting_set('max_deployments', 10, 'deployment', 'integer');
```

### Boolean
Doğru/Yanlış değerler için:
```php
setting_set('auto_deployment', true, 'deployment', 'boolean');
// Veritabanında '1' veya '0' olarak saklanır
```

### JSON
JSON objeleri için:
```php
setting_set('api_config', ['key' => 'value', 'timeout' => 30], 'api', 'json');
```

### Array
Diziler için:
```php
setting_set('allowed_ips', ['192.168.1.1', '10.0.0.1'], 'security', 'array');
```

## Şifreleme

Hassas bilgiler için şifreleme kullanın:

```php
$setting = Setting::create([
    'key' => 'api_secret',
    'value' => 'my-secret-key',
    'is_encrypted' => true,
]);

// Otomatik olarak şifrelenerek saklanır
// Okunurken otomatik olarak çözülür
$value = $setting->formatted_value; // 'my-secret-key'
```

## Cache Yönetimi

Ayarlar varsayılan olarak 1 saat boyunca cache'lenir. Cache'i yönetmek için:

```php
// Cache temizle
app(SettingService::class)->clearCache();

// Veya helper ile
setting()->clearCache();
```

Filament admin panelinde ayarları düzenlerken cache otomatik olarak temizlenir.

## Filament Admin Paneli

Ayarlar, Filament admin panelinde **Sistem > Ayarlar** menüsünden yönetilebilir.

### Özellikler:
- ✅ Arama ve filtreleme
- ✅ Gruplara göre filtreleme
- ✅ Tip bazlı filtreleme
- ✅ Public/Private ayar filtreleme
- ✅ Toplu silme
- ✅ Cache temizleme butonu

## Varsayılan Ayar Grupları

### General (Genel)
- `site_name`: Site adı
- `site_description`: Site açıklaması
- `admin_email`: Admin e-posta
- `timezone`: Zaman dilimi

### Deployment
- `default_deployment_timeout`: Deployment timeout
- `default_git_branch`: Varsayılan git branch
- `default_php_version`: Varsayılan PHP versiyonu
- `auto_deployment_enabled`: Otomatik deployment

### Email
- `smtp_host`: SMTP sunucu
- `smtp_port`: SMTP port
- `smtp_username`: SMTP kullanıcı adı
- `smtp_password`: SMTP şifre (şifreli)
- `mail_from_address`: Gönderen e-posta
- `mail_from_name`: Gönderen adı

### Security (Güvenlik)
- `max_login_attempts`: Maksimum giriş denemesi
- `session_lifetime`: Oturum süresi
- `require_2fa`: 2FA zorunlu mu?

### Backup (Yedekleme)
- `auto_backup_enabled`: Otomatik yedekleme
- `backup_retention_days`: Yedek saklama süresi

### Notifications (Bildirimler)
- `deployment_notifications`: Deployment bildirimleri
- `ssl_expiry_notifications`: SSL dolma bildirimleri
- `ssl_expiry_days_before`: SSL bildirim gün sayısı

## API Kullanımı

Frontend'de public ayarları kullanmak için:

```php
// API endpoint örneği
Route::get('/api/settings/public', function () {
    return response()->json(public_settings());
});
```

```javascript
// JavaScript'te kullanım
fetch('/api/settings/public')
    .then(response => response.json())
    .then(settings => {
        console.log(settings.site_name);
        console.log(settings.timezone);
    });
```

## Best Practices

1. **Hassas bilgileri şifreleyin**: API anahtarları, şifreler vb. için `is_encrypted: true` kullanın
2. **Public ayarları sınırlayın**: Sadece gerçekten gerekli olan ayarları public yapın
3. **Grupları mantıklı kullanın**: İlgili ayarları aynı grupta toplayın
4. **Cache'i unutmayın**: Çok sık değişmeyen ayarlar için cache kullanın
5. **Tip belirtin**: Değerlerin doğru tipte saklanması için tip belirtin

## Örnek Senaryolar

### Deployment Timeout'u Değiştirme

```php
// Config'den oku
$timeout = setting('default_deployment_timeout', config('deployment.timeout'));

// Güncelle
setting_set('default_deployment_timeout', 900);
```

### E-posta Ayarlarını Kullanma

```php
$emailSettings = setting_group('email');

Mail::mailer('smtp')->send(new MyMail($emailSettings));
```

### Feature Flag Olarak Kullanma

```php
if (setting('auto_deployment_enabled', false)) {
    // Otomatik deployment yap
}
```

