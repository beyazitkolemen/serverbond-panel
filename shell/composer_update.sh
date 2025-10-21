#!/usr/bin/env bash
set -euo pipefail

usage() {
    cat <<'USAGE'
Usage: composer_update.sh [--working-dir <path>] [--composer <path>] [--] [composer options]

Options:
      --working-dir <path>   Directory containing composer.json. Defaults to the Laravel project root (one level up from this script).
      --composer <path>      Composer executable to use. Defaults to the 'composer' command in PATH.
  -h, --help                 Show this help message.
      --                     All subsequent arguments are passed directly to 'composer update'.
USAGE
}

main() {
    local script_dir
    script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    local default_workdir="$(cd "$script_dir/.." && pwd)"

    local working_dir="$default_workdir"
    local composer_cmd="composer"
    local composer_args=()

    while [[ $# -gt 0 ]]; do
        case "$1" in
            --working-dir)
                working_dir="$2"
                shift 2
                ;;
            --composer)
                composer_cmd="$2"
                shift 2
                ;;
            -h|--help)
                usage
                exit 0
                ;;
            --)
                shift
                composer_args+=("$@")
                break
                ;;
            *)
                composer_args+=("$1")
                shift
                ;;
        esac
    done

    if [[ ! -d "$working_dir" ]]; then
        echo "[ERROR] Working directory '$working_dir' does not exist." >&2
        exit 1
    fi

    if [[ ! -f "$working_dir/composer.json" ]]; then
        echo "[ERROR] No composer.json found in '$working_dir'." >&2
        exit 1
    fi

    if ! command -v "$composer_cmd" >/dev/null 2>&1; then
        echo "[ERROR] Composer executable '$composer_cmd' not found." >&2
        exit 1
    fi

    pushd "$working_dir" >/dev/null
    if [[ ${#composer_args[@]} -gt 0 ]]; then
        "$composer_cmd" update --no-interaction "${composer_args[@]}"
    else
        "$composer_cmd" update --no-interaction
    fi
    popd >/dev/null

    echo "[INFO] Composer dependencies updated successfully in '$working_dir'."
}

main "$@"
