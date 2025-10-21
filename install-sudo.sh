#!/bin/bash

echo "🔧 ServerBond Panel - Sudo Kurulumu"
echo "===================================="
echo ""

# Kullanıcı adını tespit et
CURRENT_USER=$(whoami)
echo "✓ Mevcut kullanıcı: $CURRENT_USER"
echo ""

# Web server kullanıcısını tespit et
WEB_USER="www-data"
if [ -f "/etc/debian_version" ]; then
    WEB_USER="www-data"
elif [ -f "/etc/redhat-release" ]; then
    WEB_USER="nginx"
fi

echo "📋 Tespit edilen web server kullanıcısı: $WEB_USER"
echo ""
echo "⚠️  Eğer farklı bir kullanıcı kullanıyorsanız (örn: sail), aşağıdaki komutta değiştirin:"
echo "   read -p 'Web server kullanıcısı [$WEB_USER]: ' INPUT"
read -p "Web server kullanıcısı [$WEB_USER]: " INPUT
WEB_USER=${INPUT:-$WEB_USER}

echo ""
echo "✓ Kullanılacak kullanıcı: $WEB_USER"
echo ""

# Sudoers dosyası oluştur
SUDOERS_FILE="/etc/sudoers.d/serverbond"

echo "📝 Sudoers dosyası oluşturuluyor: $SUDOERS_FILE"
echo ""

# Sudoers içeriği
sudo tee $SUDOERS_FILE > /dev/null <<EOF
# ServerBond Panel - Nginx Yönetimi
$WEB_USER ALL=(ALL) NOPASSWD: /bin/mv /tmp/*.conf /etc/nginx/sites-available/
$WEB_USER ALL=(ALL) NOPASSWD: /bin/chmod 644 /etc/nginx/sites-available/*.conf
$WEB_USER ALL=(ALL) NOPASSWD: /bin/ln -sf /etc/nginx/sites-available/*.conf /etc/nginx/sites-enabled/
$WEB_USER ALL=(ALL) NOPASSWD: /bin/rm /etc/nginx/sites-enabled/*.conf
$WEB_USER ALL=(ALL) NOPASSWD: /usr/sbin/nginx -t
$WEB_USER ALL=(ALL) NOPASSWD: /bin/systemctl reload nginx
$WEB_USER ALL=(ALL) NOPASSWD: /bin/systemctl restart nginx
$WEB_USER ALL=(ALL) NOPASSWD: /bin/systemctl status nginx

# ServerBond Panel - Cloudflare Tunnel Yönetimi
$WEB_USER ALL=(ALL) NOPASSWD: /bin/systemctl start cloudflared-*
$WEB_USER ALL=(ALL) NOPASSWD: /bin/systemctl stop cloudflared-*
$WEB_USER ALL=(ALL) NOPASSWD: /bin/systemctl enable cloudflared-*
$WEB_USER ALL=(ALL) NOPASSWD: /bin/systemctl disable cloudflared-*
$WEB_USER ALL=(ALL) NOPASSWD: /bin/systemctl is-active cloudflared-*
$WEB_USER ALL=(ALL) NOPASSWD: /bin/systemctl daemon-reload
$WEB_USER ALL=(ALL) NOPASSWD: /bin/mv /tmp/cloudflared-*.service /etc/systemd/system/
$WEB_USER ALL=(ALL) NOPASSWD: /bin/chmod 644 /etc/systemd/system/cloudflared-*.service
$WEB_USER ALL=(ALL) NOPASSWD: /bin/rm /etc/systemd/system/cloudflared-*.service
EOF

# İzinleri ayarla
sudo chmod 0440 $SUDOERS_FILE

echo "✓ Sudoers dosyası oluşturuldu"
echo ""

# Syntax kontrolü
echo "🔍 Sudoers syntax kontrolü yapılıyor..."
if sudo visudo -c; then
    echo ""
    echo "✅ Kurulum başarılı!"
    echo ""
    echo "📋 Test komutları:"
    echo "   sudo -u $WEB_USER sudo nginx -t"
    echo "   sudo -u $WEB_USER sudo systemctl reload nginx"
    echo ""
else
    echo ""
    echo "❌ HATA: Sudoers dosyası syntax hatası içeriyor!"
    echo "   Dosya siliniyor..."
    sudo rm $SUDOERS_FILE
    exit 1
fi

