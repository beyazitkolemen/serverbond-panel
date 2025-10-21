#!/usr/bin/env bash
set -euo pipefail

usage() {
    cat <<'USAGE'
Usage: artisan.sh [--working-dir <path>] [--php <path>] [--] [artisan arguments]

Options:
      --working-dir <path>   Directory containing the Laravel artisan file. Defaults to the project root (one level up from this script).
      --php <path>           PHP executable to use. Defaults to the 'php' command in PATH.
  -h, --help                 Show this help message.
      --                     All subsequent arguments are passed directly to 'php artisan'.
USAGE
}

main() {
    local script_dir
    script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    local default_workdir="$(cd "$script_dir/.." && pwd)"

    local working_dir="$default_workdir"
    local php_cmd="php"
    local artisan_args=()

    while [[ $# -gt 0 ]]; do
        case "$1" in
            --working-dir)
                working_dir="$2"
                shift 2
                ;;
            --php)
                php_cmd="$2"
                shift 2
                ;;
            -h|--help)
                usage
                exit 0
                ;;
            --)
                shift
                artisan_args+=("$@")
                break
                ;;
            *)
                artisan_args+=("$1")
                shift
                ;;
        esac
    done

    if [[ ! -d "$working_dir" ]]; then
        echo "[ERROR] Working directory '$working_dir' does not exist." >&2
        exit 1
    fi

    if [[ ! -f "$working_dir/artisan" ]]; then
        echo "[ERROR] artisan file not found in '$working_dir'." >&2
        exit 1
    fi

    if ! command -v "$php_cmd" >/dev/null 2>&1; then
        echo "[ERROR] PHP executable '$php_cmd' not found." >&2
        exit 1
    fi

    pushd "$working_dir" >/dev/null
    if [[ ${#artisan_args[@]} -gt 0 ]]; then
        "$php_cmd" artisan "${artisan_args[@]}"
    else
        "$php_cmd" artisan
    fi
    popd >/dev/null
}

main "$@"
