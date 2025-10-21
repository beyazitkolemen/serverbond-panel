<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Site;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class CloudflareService
{
    protected string $configPath = '/etc/cloudflared';
    protected string $servicePath = '/etc/systemd/system';

    /**
     * Cloudflared kurulu mu kontrol et
     */
    public function isInstalled(): bool
    {
        $result = Process::run('which cloudflared');
        return $result->successful();
    }

    /**
     * Site için tunnel başlat
     */
    public function startTunnel(Site $site): array
    {
        if (!$this->isInstalled()) {
            return [
                'success' => false,
                'error' => 'cloudflared kurulu değil',
            ];
        }

        if (empty($site->cloudflare_tunnel_token)) {
            return [
                'success' => false,
                'error' => 'Cloudflare tunnel token bulunamadı',
            ];
        }

        try {
            // Config dosyası oluştur
            $configPath = $this->createTunnelConfig($site);

            // Systemd service oluştur
            $this->createSystemdService($site, $configPath);

            // Service'i başlat
            $this->enableAndStartService($site);

            return [
                'success' => true,
                'message' => 'Cloudflare tunnel başlatıldı',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Site için tunnel durdur
     */
    public function stopTunnel(Site $site): array
    {
        try {
            $serviceName = $this->getServiceName($site);

            // Service'i durdur ve devre dışı bırak
            Process::run(['sudo', 'systemctl', 'stop', $serviceName]);
            Process::run(['sudo', 'systemctl', 'disable', $serviceName]);

            // Config ve service dosyalarını sil
            $this->cleanupTunnelFiles($site);

            return [
                'success' => true,
                'message' => 'Cloudflare tunnel durduruldu',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Tunnel durumunu kontrol et
     */
    public function getTunnelStatus(Site $site): array
    {
        $serviceName = $this->getServiceName($site);
        $result = Process::run(['sudo', 'systemctl', 'is-active', $serviceName]);

        return [
            'running' => $result->successful(),
            'status' => trim($result->output()),
        ];
    }

    /**
     * Tunnel config dosyası oluştur
     */
    protected function createTunnelConfig(Site $site): string
    {
        $configDir = $this->configPath . '/sites';
        $configFile = $configDir . '/' . $this->getConfigFileName($site);

        // Config dizini yoksa oluştur
        if (!File::exists($configDir)) {
            File::makeDirectory($configDir, 0755, true);
        }

        // Config içeriği
        $config = [
            'tunnel' => $site->cloudflare_tunnel_id ?? $site->domain,
            'credentials-file' => $configDir . '/' . $this->getConfigFileName($site) . '.json',
        ];

        // Credentials dosyası oluştur (token'dan)
        $this->createCredentialsFile($site, $config['credentials-file']);

        // Config dosyasını yaz
        File::put($configFile, "tunnel: {$config['tunnel']}\n");
        File::append($configFile, "credentials-file: {$config['credentials-file']}\n");

        return $configFile;
    }

    /**
     * Credentials dosyası oluştur
     */
    protected function createCredentialsFile(Site $site, string $path): void
    {
        // Token'ı base64 decode et ve JSON olarak kaydet
        // Cloudflare tunnel token formatına göre düzenle
        $credentials = [
            'AccountTag' => '',
            'TunnelSecret' => $site->cloudflare_tunnel_token,
            'TunnelID' => $site->cloudflare_tunnel_id ?? $site->domain,
        ];

        File::put($path, json_encode($credentials, JSON_PRETTY_PRINT));
        chmod($path, 0600);
    }

    /**
     * Systemd service dosyası oluştur
     */
    protected function createSystemdService(Site $site, string $configPath): void
    {
        $serviceName = $this->getServiceName($site);
        $serviceFile = $this->servicePath . '/' . $serviceName . '.service';

        $serviceContent = <<<SERVICE
[Unit]
Description=Cloudflare Tunnel for {$site->domain}
After=network.target

[Service]
Type=simple
User=root
ExecStart=/usr/bin/cloudflared tunnel --config {$configPath} --no-autoupdate run
Restart=on-failure
RestartSec=5s

[Install]
WantedBy=multi-user.target
SERVICE;

        // Temporary file oluştur
        $tempFile = sys_get_temp_dir() . '/' . $serviceName . '.service';
        File::put($tempFile, $serviceContent);

        // Sudo ile systemd dizinine taşı
        $result = Process::run(['sudo', 'mv', $tempFile, $serviceFile]);

        if (!$result->successful()) {
            throw new \RuntimeException('Systemd service dosyası oluşturulamadı: ' . $result->errorOutput());
        }

        // Sudo ile izinleri ayarla
        Process::run(['sudo', 'chmod', '644', $serviceFile]);

        // Systemd'yi yenile
        Process::run(['sudo', 'systemctl', 'daemon-reload']);
    }

    /**
     * Service'i enable ve start et
     */
    protected function enableAndStartService(Site $site): void
    {
        $serviceName = $this->getServiceName($site);

        Process::run(['sudo', 'systemctl', 'enable', $serviceName]);
        Process::run(['sudo', 'systemctl', 'start', $serviceName]);
    }

    /**
     * Tunnel dosyalarını temizle
     */
    protected function cleanupTunnelFiles(Site $site): void
    {
        $serviceName = $this->getServiceName($site);
        $serviceFile = $this->servicePath . '/' . $serviceName . '.service';
        $configFile = $this->configPath . '/sites/' . $this->getConfigFileName($site);
        $credentialsFile = $configFile . '.json';

        // Sudo ile service dosyasını sil
        if (File::exists($serviceFile)) {
            Process::run(['sudo', 'rm', $serviceFile]);
        }

        // Config dosyalarını sil (bunlar genelde /etc/cloudflared altında)
        if (File::exists($configFile)) {
            File::delete($configFile);
        }

        if (File::exists($credentialsFile)) {
            File::delete($credentialsFile);
        }

        // Systemd'yi yenile
        Process::run(['sudo', 'systemctl', 'daemon-reload']);
    }

    /**
     * Service adını döndür
     */
    protected function getServiceName(Site $site): string
    {
        return 'cloudflared-' . str_replace('.', '-', $site->domain);
    }

    /**
     * Config dosya adını döndür
     */
    protected function getConfigFileName(Site $site): string
    {
        return str_replace('.', '-', $site->domain);
    }

    /**
     * Tunnel ile token'dan başlat (quick setup)
     */
    public function runTunnelWithToken(Site $site): array
    {
        if (!$this->isInstalled()) {
            return [
                'success' => false,
                'error' => 'cloudflared kurulu değil',
            ];
        }

        if (empty($site->cloudflare_tunnel_token)) {
            return [
                'success' => false,
                'error' => 'Cloudflare tunnel token bulunamadı',
            ];
        }

        try {
            $serviceName = $this->getServiceName($site);
            $serviceFile = $this->servicePath . '/' . $serviceName . '.service';

            // Token ile direkt çalışan service oluştur
            $serviceContent = <<<SERVICE
[Unit]
Description=Cloudflare Tunnel for {$site->domain}
After=network.target

[Service]
Type=simple
User=root
ExecStart=/usr/bin/cloudflared tunnel --no-autoupdate run --token {$site->cloudflare_tunnel_token}
Restart=on-failure
RestartSec=5s

[Install]
WantedBy=multi-user.target
SERVICE;

            File::put($serviceFile, $serviceContent);
            chmod($serviceFile, 0644);

            // Systemd'yi yenile
            Process::run(['systemctl', 'daemon-reload']);

            // Service'i başlat
            $this->enableAndStartService($site);

            return [
                'success' => true,
                'message' => 'Cloudflare tunnel başlatıldı',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}

