#!/usr/bin/env bash

set -u

PROJECT_DIR="/workspaces/new-lotto-site"
PHP_BIN="/usr/bin/php8.4"
PORT="8000"
LOG_FILE="/tmp/lotto-php-server.log"

echo "=== Lotto Codespace startup ==="

# 1. MySQL
if sudo service mysql status >/dev/null 2>&1; then
    echo "MySQL: already running"
else
    echo "MySQL: starting..."
    sudo service mysql start
fi

# 2. PHP development server
if ss -lnt 2>/dev/null | grep -q ":${PORT} "; then
    echo "PHP server: port ${PORT} already listening"
else
    echo "PHP server: starting on port ${PORT}..."

    cd "$PROJECT_DIR" || exit 1

    nohup "$PHP_BIN" \
        -S "0.0.0.0:${PORT}" \
        -t "$PROJECT_DIR" \
        > "$LOG_FILE" 2>&1 &

    sleep 2
fi

# 3. Confirm server
if ss -lnt 2>/dev/null | grep -q ":${PORT} "; then
    echo "PHP server: running"
else
    echo "ERROR: PHP server did not start."
    tail -30 "$LOG_FILE" 2>/dev/null || true
    exit 1
fi

# 4. Make Codespaces port public
if [ -n "${CODESPACE_NAME:-}" ] && command -v gh >/dev/null 2>&1; then
    echo "Codespaces port: setting ${PORT} to public..."

    gh codespace ports visibility "${PORT}:public" \
        -c "$CODESPACE_NAME" || {
            echo "WARNING: could not make port public."
            exit 1
        }

    echo
    echo "Public URL:"
    echo "https://${CODESPACE_NAME}-${PORT}.app.github.dev"
else
    echo "WARNING: Codespaces environment not detected."
fi
