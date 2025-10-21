#!/usr/bin/env bash
set -euo pipefail

usage() {
    cat <<'USAGE'
Usage: create_nginx_site.sh --name <site_name> --root <document_root> --domains "example.com www.example.com" [options]

Options:
  -n, --name <site_name>        Logical name for the site (used for the nginx config file).
  -r, --root <document_root>    Absolute path to the site's document root.
  -d, --domains <domains>       Space or comma separated list of domain names for server_name.
  -p, --php-sock <path>         PHP-FPM socket path. Defaults to ${PHP_FPM_SOCK:-/run/php/php8.1-fpm.sock}.
  -l, --log-dir <path>          Directory for access and error logs. Defaults to ${NGINX_LOG_DIR:-/var/log/nginx}.
      --force                   Overwrite existing configuration and symlink if they exist.
      --skip-reload             Do not reload nginx after creating the site.
  -h, --help                    Show this help message.

Environment variables:
  PHP_FPM_SOCK                  Default PHP-FPM socket path.
  NGINX_LOG_DIR                 Default directory for nginx logs.
USAGE
}

main() {
    if [[ $EUID -ne 0 ]]; then
        echo "[ERROR] This script must be run as root." >&2
        exit 1
    fi

    local site_name=""
    local document_root=""
    local domains=""
    local php_sock="${PHP_FPM_SOCK:-/run/php/php8.1-fpm.sock}"
    local log_dir="${NGINX_LOG_DIR:-/var/log/nginx}"
    local force_overwrite=0
    local skip_reload=0

    while [[ $# -gt 0 ]]; do
        case "$1" in
            -n|--name)
                site_name="$2"
                shift 2
                ;;
            -r|--root)
                document_root="$2"
                shift 2
                ;;
            -d|--domains)
                domains="$2"
                shift 2
                ;;
            -p|--php-sock)
                php_sock="$2"
                shift 2
                ;;
            -l|--log-dir)
                log_dir="$2"
                shift 2
                ;;
            --force)
                force_overwrite=1
                shift
                ;;
            --skip-reload)
                skip_reload=1
                shift
                ;;
            -h|--help)
                usage
                exit 0
                ;;
            *)
                echo "[ERROR] Unknown option: $1" >&2
                usage
                exit 1
                ;;
        esac
    done

    if [[ -z "$site_name" || -z "$document_root" || -z "$domains" ]]; then
        echo "[ERROR] --name, --root and --domains are required." >&2
        usage
        exit 1
    fi

    if [[ ! -d "$document_root" ]]; then
        echo "[ERROR] Document root '$document_root' does not exist." >&2
        exit 1
    fi

    if ! command -v nginx >/dev/null 2>&1; then
        echo "[ERROR] nginx command not found." >&2
        exit 1
    fi

    mkdir -p "$log_dir"

    local sanitized_domains
    sanitized_domains=$(echo "$domains" | tr ',' ' ' | xargs)

    if [[ -z "$sanitized_domains" ]]; then
        echo "[ERROR] Failed to parse domains list." >&2
        exit 1
    fi

    local config_path="/etc/nginx/sites-available/${site_name}.conf"
    local enabled_path="/etc/nginx/sites-enabled/${site_name}.conf"

    if [[ -e "$config_path" && $force_overwrite -eq 0 ]]; then
        echo "[ERROR] Configuration '$config_path' already exists. Use --force to overwrite." >&2
        exit 1
    fi

    cat >"$config_path" <<CONFIG
server {
    listen 80;
    listen [::]:80;
    server_name $sanitized_domains;

    root $document_root;
    index index.php index.html index.htm;

    access_log ${log_dir%/}/$site_name.access.log;
    error_log ${log_dir%/}/$site_name.error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:$php_sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
CONFIG

    if [[ -L "$enabled_path" || -e "$enabled_path" ]]; then
        if [[ $force_overwrite -eq 1 ]]; then
            rm -f "$enabled_path"
        else
            echo "[ERROR] Enabled configuration '$enabled_path' already exists. Use --force to overwrite." >&2
            exit 1
        fi
    fi

    ln -s "$config_path" "$enabled_path"

    nginx -t

    if [[ $skip_reload -eq 0 ]]; then
        local script_dir
        script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
        "$script_dir/restart_nginx.sh" --reload
    fi

    echo "[INFO] Nginx site '$site_name' created successfully."
}

main "$@"
