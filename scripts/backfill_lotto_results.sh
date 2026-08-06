#!/usr/bin/env bash

set -u

BASE_URL="http://127.0.0.1:8000"
LOG_FILE="data/log/lotto_result_backfill_console.log"

success_count=0
failure_count=0

echo "[$(date '+%Y-%m-%d %H:%M:%S')] 과거 회차 수집 시작" | tee -a "$LOG_FILE"

while true; do
    oldest=$(sudo mysql -Nse \
        "SELECT MIN(draw_no) FROM lotto_dev.g5_lotto_result;" 2>>"$LOG_FILE")

    if ! [[ "$oldest" =~ ^[0-9]+$ ]]; then
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] DB 최소 회차 확인 실패" \
            | tee -a "$LOG_FILE"
        sleep 60
        continue
    fi

    if [ "$oldest" -le 1 ]; then
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] 1회까지 수집 완료" \
            | tee -a "$LOG_FILE"
        break
    fi

    echo "[$(date '+%Y-%m-%d %H:%M:%S')] 현재 최소 ${oldest}회" \
        | tee -a "$LOG_FILE"

    response=$(curl \
        --connect-timeout 10 \
        --max-time 30 \
        -sS \
        "${BASE_URL}/api/lotto/backfill_results.php?batches=1" 2>&1)

    echo "$response" | tee -a "$LOG_FILE"

    if printf '%s' "$response" | grep -q '"success":true'; then
        success_count=$((success_count + 1))
        failure_count=0

        echo "성공 ${success_count}회 — 20초 후 다음 요청" \
            | tee -a "$LOG_FILE"

        sleep 20
        continue
    fi

    failure_count=$((failure_count + 1))

    if [ "$failure_count" -eq 1 ]; then
        wait_seconds=60
    elif [ "$failure_count" -eq 2 ]; then
        wait_seconds=120
    else
        wait_seconds=300
    fi

    echo "연속 실패 ${failure_count}회 — ${wait_seconds}초 후 재시도" \
        | tee -a "$LOG_FILE"

    sleep "$wait_seconds"
done
