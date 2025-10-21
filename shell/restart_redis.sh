#!/usr/bin/env bash
set -euo pipefail

usage() {
    cat <<'USAGE'
Usage: restart_redis.sh [--action <action>] [--service <name>]

Options:
      --action <action>   Service action to perform (restart, start, stop, status). Default: restart.
      --service <name>    Override Redis service name. Defaults to ${REDIS_SERVICE:-redis-server}.
  -h, --help              Show this help message.

Environment variables:
  REDIS_SERVICE          Default Redis service name.
USAGE
}

main() {
    if [[ $EUID -ne 0 ]]; then
        echo "[ERROR] This script must be run as root." >&2
        exit 1
    fi

    local action="restart"
    local service_name="${REDIS_SERVICE:-redis-server}"

    while [[ $# -gt 0 ]]; do
        case "$1" in
            --action)
                action="$2"
                shift 2
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

    echo "[INFO] Redis service '$service_name' ${action}ed successfully."
}

main "$@"
