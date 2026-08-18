#!/usr/bin/env bash

set -u

PROJECT_ROOT="/workspaces/new-lotto-site"
PHP_BIN="/usr/bin/php8.4"
PORT="8000"
DB_HOST="127.0.0.1"
DB_NAME="lotto_dev"
DB_USER="root"
LOG_DIR="${PROJECT_ROOT}/data/log"
SERVER_LOG="${LOG_DIR}/dev-server.log"
SERVER_PID_FILE="${LOG_DIR}/dev-server.pid"

cd "${PROJECT_ROOT}" || exit 1
mkdir -p "${LOG_DIR}"

echo "=== Lotto Development Start ==="

if mysql -h "${DB_HOST}" -u "${DB_USER}" "${DB_NAME}" -e "SELECT 1;" >/dev/null 2>&1; then
    echo "MySQL      : running"
else
    echo "MySQL      : starting"
    sudo service mysql start >/dev/null 2>&1 || true

    mysql_ready=0
    for _ in $(seq 1 20); do
        if mysql -h "${DB_HOST}" -u "${DB_USER}" "${DB_NAME}" -e "SELECT 1;" >/dev/null 2>&1; then
            mysql_ready=1
            break
        fi
        sleep 1
    done

    if [ "${mysql_ready}" -ne 1 ]; then
        echo "ERROR: MySQL did not become ready on ${DB_HOST}:3306."
        exit 1
    fi

    echo "MySQL      : running"
fi

if ss -lnt 2>/dev/null | grep -qE "[:.]${PORT}[[:space:]]"; then
    echo "PHP server : already running on port ${PORT}"
else
    echo "PHP server : starting on port ${PORT}"
    nohup "${PHP_BIN}" -S "0.0.0.0:${PORT}" -t "${PROJECT_ROOT}" \
        >"${SERVER_LOG}" 2>&1 &
    server_pid=$!
    echo "${server_pid}" > "${SERVER_PID_FILE}"

    server_ready=0
    for _ in $(seq 1 20); do
        if ss -lnt 2>/dev/null | grep -qE "[:.]${PORT}[[:space:]]"; then
            server_ready=1
            break
        fi
        sleep 1
    done

    if [ "${server_ready}" -ne 1 ]; then
        echo "ERROR: PHP server did not start on port ${PORT}."
        echo "Log: ${SERVER_LOG}"
        exit 1
    fi

    echo "PHP server : running (PID ${server_pid})"
fi

if [ -z "${CODESPACE_NAME:-}" ]; then
    echo "ERROR: CODESPACE_NAME is not available."
    exit 1
fi

if ! command -v gh >/dev/null 2>&1; then
    echo "ERROR: GitHub CLI (gh) is not installed."
    exit 1
fi

echo "Port ${PORT}   : setting public"
if ! gh codespace ports visibility "${PORT}:public" -c "${CODESPACE_NAME}" >/dev/null; then
    echo "ERROR: Could not set port ${PORT} to public."
    exit 1
fi

port_info=$(gh codespace ports \
    -c "${CODESPACE_NAME}" \
    --json sourcePort,visibility,browseUrl \
    --jq ".[] | select(.sourcePort == ${PORT}) | [.visibility, .browseUrl] | @tsv")

port_visibility=$(printf '%s' "${port_info}" | cut -f1)
port_url=$(printf '%s' "${port_info}" | cut -f2)

if [ "${port_visibility}" != "public" ]; then
    echo "ERROR: Port ${PORT} visibility is '${port_visibility:-unknown}', not public."
    exit 1
fi

echo "Port ${PORT}   : public"
echo "URL         : ${port_url}"
echo "Ready."
