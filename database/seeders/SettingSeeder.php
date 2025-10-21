<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'group' => 'general',
                'key' => 'site_name',
                'value' => 'ServerBond',
                'type' => 'string',
                'label' => 'Site Adı',
                'description' => 'Uygulamanın görünen adı',
                'is_public' => true,
                'order' => 1,
            ],
            [
                'group' => 'general',
                'key' => 'site_description',
                'value' => 'Server Yönetim Paneli',
                'type' => 'string',
                'label' => 'Site Açıklaması',
                'description' => 'Uygulamanın kısa açıklaması',
                'is_public' => true,
                'order' => 2,
            ],
            [
                'group' => 'general',
                'key' => 'admin_email',
                'value' => 'admin@serverbond.local',
                'type' => 'string',
                'label' => 'Admin E-posta',
                'description' => 'Sistem yöneticisinin e-posta adresi',
                'is_public' => false,
                'order' => 3,
            ],
            [
                'group' => 'general',
                'key' => 'timezone',
                'value' => 'Europe/Istanbul',
                'type' => 'string',
                'label' => 'Zaman Dilimi',
                'description' => 'Uygulamanın varsayılan zaman dilimi',
                'is_public' => true,
                'order' => 4,
            ],

            // Deployment Settings
            [
                'group' => 'deployment',
                'key' => 'default_deployment_timeout',
                'value' => '600',
                'type' => 'integer',
                'label' => 'Varsayılan Deployment Timeout',
                'description' => 'Deployment işlemleri için maksimum süre (saniye)',
                'is_public' => false,
                'order' => 10,
            ],
            [
                'group' => 'deployment',
                'key' => 'default_git_branch',
                'value' => 'main',
                'type' => 'string',
                'label' => 'Varsayılan Git Branch',
                'description' => 'Yeni siteler için varsayılan git branch',
                'is_public' => false,
                'order' => 11,
            ],
            [
                'group' => 'deployment',
                'key' => 'default_php_version',
                'value' => '8.4',
                'type' => 'string',
                'label' => 'Varsayılan PHP Versiyonu',
                'description' => 'Yeni siteler için varsayılan PHP versiyonu',
                'is_public' => false,
                'order' => 12,
            ],
            [
                'group' => 'deployment',
                'key' => 'auto_deployment_enabled',
                'value' => '0',
                'type' => 'boolean',
                'label' => 'Otomatik Deployment',
                'description' => 'Git webhook\'leri için otomatik deployment aktif mi?',
                'is_public' => false,
                'order' => 13,
            ],

            // Email Settings
            [
                'group' => 'email',
                'key' => 'smtp_host',
                'value' => '',
                'type' => 'string',
                'label' => 'SMTP Host',
                'description' => 'SMTP sunucu adresi',
                'is_public' => false,
                'is_encrypted' => false,
                'order' => 20,
            ],
            [
                'group' => 'email',
                'key' => 'smtp_port',
                'value' => '587',
                'type' => 'integer',
                'label' => 'SMTP Port',
                'description' => 'SMTP sunucu portu',
                'is_public' => false,
                'order' => 21,
            ],
            [
                'group' => 'email',
                'key' => 'smtp_username',
                'value' => '',
                'type' => 'string',
                'label' => 'SMTP Kullanıcı Adı',
                'description' => 'SMTP kimlik doğrulama kullanıcı adı',
                'is_public' => false,
                'is_encrypted' => false,
                'order' => 22,
            ],
            [
                'group' => 'email',
                'key' => 'smtp_password',
                'value' => '',
                'type' => 'string',
                'label' => 'SMTP Şifre',
                'description' => 'SMTP kimlik doğrulama şifresi',
                'is_public' => false,
                'is_encrypted' => true,
                'order' => 23,
            ],
            [
                'group' => 'email',
                'key' => 'mail_from_address',
                'value' => 'noreply@serverbond.local',
                'type' => 'string',
                'label' => 'Gönderen E-posta',
                'description' => 'E-postaların gönderildiği adres',
                'is_public' => false,
                'order' => 24,
            ],
            [
                'group' => 'email',
                'key' => 'mail_from_name',
                'value' => 'ServerBond',
                'type' => 'string',
                'label' => 'Gönderen Adı',
                'description' => 'E-postaların gönderen adı',
                'is_public' => false,
                'order' => 25,
            ],

            // Security Settings
            [
                'group' => 'security',
                'key' => 'max_login_attempts',
                'value' => '5',
                'type' => 'integer',
                'label' => 'Maksimum Giriş Denemesi',
                'description' => 'Hesap kilitlenmeden önce izin verilen maksimum başarısız giriş denemesi',
                'is_public' => false,
                'order' => 30,
            ],
            [
                'group' => 'security',
                'key' => 'session_lifetime',
                'value' => '120',
                'type' => 'integer',
                'label' => 'Oturum Süresi',
                'description' => 'Kullanıcı oturumunun geçerlilik süresi (dakika)',
                'is_public' => false,
                'order' => 31,
            ],
            [
                'group' => 'security',
                'key' => 'require_2fa',
                'value' => '0',
                'type' => 'boolean',
                'label' => 'İki Faktörlü Kimlik Doğrulama Zorunlu',
                'description' => 'Tüm kullanıcılar için 2FA zorunlu mu?',
                'is_public' => false,
                'order' => 32,
            ],

            // Backup Settings
            [
                'group' => 'backup',
                'key' => 'auto_backup_enabled',
                'value' => '1',
                'type' => 'boolean',
                'label' => 'Otomatik Yedekleme',
                'description' => 'Otomatik yedekleme aktif mi?',
                'is_public' => false,
                'order' => 40,
            ],
            [
                'group' => 'backup',
                'key' => 'backup_retention_days',
                'value' => '30',
                'type' => 'integer',
                'label' => 'Yedek Saklama Süresi',
                'description' => 'Yedeklerin saklanacağı gün sayısı',
                'is_public' => false,
                'order' => 41,
            ],

            // Notification Settings
            [
                'group' => 'notifications',
                'key' => 'deployment_notifications',
                'value' => '1',
                'type' => 'boolean',
                'label' => 'Deployment Bildirimleri',
                'description' => 'Deployment tamamlandığında bildirim gönder',
                'is_public' => false,
                'order' => 50,
            ],
            [
                'group' => 'notifications',
                'key' => 'ssl_expiry_notifications',
                'value' => '1',
                'type' => 'boolean',
                'label' => 'SSL Süresi Dolma Bildirimleri',
                'description' => 'SSL sertifikası dolmak üzereyken bildirim gönder',
                'is_public' => false,
                'order' => 51,
            ],
            [
                'group' => 'notifications',
                'key' => 'ssl_expiry_days_before',
                'value' => '7',
                'type' => 'integer',
                'label' => 'SSL Bildirim Gün Sayısı',
                'description' => 'SSL süresi dolmadan kaç gün önce bildirim gönderilsin',
                'is_public' => false,
                'order' => 52,
            ],
        ];

        foreach ($settings as $settingData) {
            Setting::updateOrCreate(
                ['key' => $settingData['key']],
                $settingData
            );
        }
    }
}

