#!/usr/bin/env bash
set -euo pipefail

usage() {
    cat <<'USAGE'
Usage: restart_nginx.sh [--reload|--restart] [--service <name>]

Options:
      --reload           Reload nginx configuration without dropping connections.
      --restart          Fully restart nginx (default action).
      --service <name>   Override nginx service name. Defaults to ${NGINX_SERVICE:-nginx}.
  -h, --help             Show this help message.

Environment variables:
  NGINX_SERVICE         Default nginx service name.
USAGE
}

main() {
    if [[ $EUID -ne 0 ]]; then
        echo "[ERROR] This script must be run as root." >&2
        exit 1
    fi

    local action="restart"
    local service_name="${NGINX_SERVICE:-nginx}"

    while [[ $# -gt 0 ]]; do
        case "$1" in
            --reload)
                action="reload"
                shift
                ;;
            --restart)
                action="restart"
                shift
                ;;
            --service)
                service_name="$2"
                shift 2
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

    if command -v systemctl >/dev/null 2>&1; then
        systemctl "$action" "$service_name"
    elif command -v service >/dev/null 2>&1; then
        service "$service_name" "$action"
    else
        echo "[ERROR] Neither systemctl nor service command is available." >&2
        exit 1
    fi

    echo "[INFO] nginx service '$service_name' ${action}ed successfully."
}

main "$@"
