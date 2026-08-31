<?php

include_once __DIR__ . '/_common.php';
include_once G5_PATH . '/include/lotto_result.lib.php';
include_once G5_PATH . '/include/lotto_filter.lib.php';
include_once G5_PATH . '/include/lotto_member_result.lib.php';
include_once G5_PATH . '/include/lotto_distribution.lib.php';
include_once G5_PATH . '/include/lotto_sms.lib.php';

date_default_timezone_set('Asia/Seoul');

set_time_limit(0);
ignore_user_abort(true);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

function lottoCronRespond($statusCode, $payload)
{
    http_response_code((int) $statusCode);

    echo json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_PRETTY_PRINT
    );

    exit;
}

if (sql_query("set time_zone = '+09:00'", false) === false) {
    lottoCronRespond(
        500,
        array(
            'success' => false,
            'status' => 'database_timezone_failed',
            'message' => 'DB 세션 시간대를 한국시간으로 설정하지 못했습니다.',
        )
    );
}

$configFile = G5_DATA_PATH . '/lotto_cron_config.php';

if (!is_file($configFile)) {
    lottoCronRespond(
        500,
        array(
            'success' => false,
            'status' => 'config_missing',
            'message' => '자동실행 설정 파일이 없습니다.',
        )
    );
}

include_once $configFile;

if (
    !defined('LOTTO_CRON_TOKEN')
    || trim((string) LOTTO_CRON_TOKEN) === ''
) {
    lottoCronRespond(
        500,
        array(
            'success' => false,
            'status' => 'config_invalid',
            'message' => '자동실행 인증 설정이 올바르지 않습니다.',
        )
    );
}

$providedToken = isset($_POST['token'])
    ? trim((string) $_POST['token'])
    : '';

if (
    $providedToken === ''
    || !hash_equals(
        (string) LOTTO_CRON_TOKEN,
        $providedToken
    )
) {
    lottoCronRespond(
        401,
        array(
            'success' => false,
            'status' => 'unauthorized',
            'message' => '인증에 실패했습니다.',
        )
    );
}

$job = isset($_GET['job'])
    ? trim((string) $_GET['job'])
    : 'health';

$allowedJobs = array(
    'health',
    'sms_sync',
    'result',
    'recover',
    'weekly',
);

if (!in_array($job, $allowedJobs, true)) {
    lottoCronRespond(
        400,
        array(
            'success' => false,
            'status' => 'invalid_job',
            'message' => '지원하지 않는 자동실행 작업입니다.',
        )
    );
}

$now = new DateTimeImmutable(
    'now',
    new DateTimeZone('Asia/Seoul')
);

if ($job === 'sms_sync') {
    $smsSync = lottoSmsSyncCombinationResults();

    lottoCronRespond(
        !empty($smsSync['success']) ? 200 : 500,
        $smsSync
    );
}

if ($job === 'health') {
    $dbCheck = sql_fetch(
        "select 1 as ok",
        false
    );

    lottoCronRespond(
        200,
        array(
            'success' => true,
            'status' => 'ok',
            'job' => 'health',
            'database' => (
                isset($dbCheck['ok'])
                && (int) $dbCheck['ok'] === 1
            ),
            'server_time' => $now->format(
                'Y-m-d H:i:s'
            ),
            'timezone' => 'Asia/Seoul',
        )
    );
}

/*
 * 정상 result 작업은 토요일/일요일에만 실행한다.
 * 정확한 실행 시각은 GitHub Actions 스케줄에서 관리한다.
 *
 * weekly 작업은 스케줄러 지연에 대비해 현재 요일로 제한하지 않는다.
 * 실제 배분 주간은 최신 당첨일을 기준으로 계산한다.
 * recover는 장애 복구용이다.
 */
$weekDay = (int) $now->format('w');

if (
    $job === 'result'
    && $weekDay !== 6
    && $weekDay !== 0
) {
    lottoCronRespond(
        200,
        array(
            'success' => true,
            'status' => 'skipped',
            'job' => 'result',
            'message' => '토요일 또는 일요일이 아니므로 실행하지 않았습니다.',
            'server_time' => $now->format(
                'Y-m-d H:i:s'
            ),
        )
    );
}

$requestedDrawNo = 0;

if ($job === 'recover') {
    $requestedDrawNo = isset($_GET['draw'])
        ? (int) $_GET['draw']
        : 0;

    if ($requestedDrawNo < 1) {
        lottoCronRespond(
            400,
            array(
                'success' => false,
                'status' => 'draw_required',
                'message' => '복구할 회차가 필요합니다.',
            )
        );
    }
}

$lockName = 'lotto_weekly_http_cron';

$lockRow = sql_fetch(
    "select get_lock(
        '" . sql_real_escape_string($lockName) . "',
        0
    ) as acquired",
    false
);

$lockAcquired = isset($lockRow['acquired'])
    && (int) $lockRow['acquired'] === 1;

if (!$lockAcquired) {
    lottoCronRespond(
        409,
        array(
            'success' => false,
            'status' => 'already_running',
            'message' => '다른 로또 자동 작업이 이미 실행 중입니다.',
        )
    );
}

$response = array(
    'success' => false,
    'status' => 'processing',
    'job' => $job,
    'started_at' => $now->format('Y-m-d H:i:s'),
    'result' => null,
    'member_result' => null,
    'winner_sms_queue' => null,
    'winner_sms_sync' => null,
    'filter' => null,
    'distribution' => null,
);

try {
    /*
     * 1. 최신 공식 당첨결과 동기화
     */
    $saved = lotto_result_get_latest_saved();

    $savedDrawNo = isset($saved['draw_no'])
        ? (int) $saved['draw_no']
        : 0;

    $context = lotto_result_open_latest();

    try {
        $remoteDrawNo = isset($context['draw_no'])
            ? (int) $context['draw_no']
            : 0;

        if ($remoteDrawNo < 1) {
            throw new RuntimeException(
                '동행복권 최신 회차를 확인하지 못했습니다.'
            );
        }

        if (
            $job === 'recover'
            && $requestedDrawNo !== $remoteDrawNo
        ) {
            throw new RuntimeException(
                '복구 요청 회차와 현재 공식 최신 회차가 다릅니다. '
                . '요청 '
                . $requestedDrawNo
                . '회, 공식 최신 '
                . $remoteDrawNo
                . '회'
            );
        }

        if ($savedDrawNo >= $remoteDrawNo) {
            $response['result'] = array(
                'status' => 'exists',
                'draw_no' => $remoteDrawNo,
                'message' => $remoteDrawNo
                    . '회 공식 결과가 이미 저장되어 있습니다.',
            );
        } else {
            $officialResult = lotto_result_fetch_draw(
                $context
            );

            $saveStatus = lotto_result_save(
                $officialResult
            );

            if (
                $saveStatus !== 'inserted'
                && $saveStatus !== 'exists'
            ) {
                throw new RuntimeException(
                    '공식 당첨결과 저장 상태가 올바르지 않습니다.'
                );
            }

            $response['result'] = array(
                'status' => $saveStatus,
                'draw_no' => $remoteDrawNo,
                'draw_date' => $officialResult['draw_date'],
                'message' => $remoteDrawNo
                    . '회 공식 결과를 처리했습니다.',
            );
        }
    } finally {
        lotto_result_close_context($context);
    }

    /*
     * 저장 후 실제 최신 회차를 다시 DB에서 확인한다.
     */
    $latest = lotto_result_get_latest_saved();

    $sourceDrawNo = isset($latest['draw_no'])
        ? (int) $latest['draw_no']
        : 0;

    if ($sourceDrawNo < 1) {
        throw new RuntimeException(
            '저장된 최신 당첨 회차를 확인하지 못했습니다.'
        );
    }

    $sourceDrawDate = isset($latest['draw_date'])
        ? trim((string) $latest['draw_date'])
        : '';

    $sourceDrawDateObject = DateTimeImmutable::createFromFormat(
        '!Y-m-d',
        $sourceDrawDate,
        new DateTimeZone('Asia/Seoul')
    );

    if (
        !$sourceDrawDateObject
        || $sourceDrawDateObject->format('Y-m-d')
            !== $sourceDrawDate
    ) {
        throw new RuntimeException(
            '저장된 최신 당첨일을 확인하지 못했습니다.'
        );
    }

    /*
     * 주간 배분 기준일은 실제 요청 도착 시각이 아니라
     * 최신 추첨일의 다음 날(일요일)을 사용한다.
     *
     * GitHub Actions 또는 외부 스케줄러가 늦게 호출되더라도
     * 올바른 월~금 주간으로 배분하기 위한 기준이다.
     */
    $weeklyBaseDate = $sourceDrawDateObject
        ->modify('+1 day');

    if (
        $job === 'recover'
        && $sourceDrawNo !== $requestedDrawNo
    ) {
        throw new RuntimeException(
            '복구 대상 회차가 최신 저장 회차와 일치하지 않습니다.'
        );
    }

    /*
     * 2. 해당 회차 회원 배분번호 당첨결과 계산
     *
     * 아직 회원 배분 데이터가 없는 테스트 환경에서는
     * 오류로 중단하지 않고 skipped 처리한다.
     */
    $memberCountRow = sql_fetch(
        "select count(*) as cnt
         from l_member_combination
         where draw_no = '{$sourceDrawNo}'",
        false
    );

    $memberCombinationCount = isset(
        $memberCountRow['cnt']
    )
        ? (int) $memberCountRow['cnt']
        : 0;

    if ($memberCombinationCount > 0) {
        $memberResult = lottoMemberResultProcessDraw(
            $sourceDrawNo
        );

        if (
            !isset($memberResult['success'])
            || !$memberResult['success']
        ) {
            throw new RuntimeException(
                '회원 당첨결과 계산 실패: '
                . (
                    isset($memberResult['error'])
                        ? $memberResult['error']
                        : '알 수 없는 오류'
                )
            );
        }

        $response['member_result'] = array(
            'status' => 'completed',
            'draw_no' => $sourceDrawNo,
            'combination_count' => isset(
                $memberResult['combination_count']
            )
                ? (int) $memberResult['combination_count']
                : 0,
            'checked_count' => isset(
                $memberResult['checked_count']
            )
                ? (int) $memberResult['checked_count']
                : 0,
            'winning_count' => isset(
                $memberResult['winning_count']
            )
                ? (int) $memberResult['winning_count']
                : 0,
        );
    } else {
        $response['member_result'] = array(
            'status' => 'skipped',
            'draw_no' => $sourceDrawNo,
            'combination_count' => 0,
            'message' => '회원 배분 조합이 없어 결과 계산을 건너뜁니다.',
        );
    }

    /*
     * 3. 당첨회원 SMS 처리
     *
     * 관리자 SMS 설정의 발신번호를 사용한다.
     * 이미 queued/sent 상태인 문자는 재등록하지 않는다.
     */
    if ($memberCombinationCount > 0) {
        try {
            $smsConfig = lottoSmsGetConfig();

            $smsSender = isset($smsConfig['sender_phone'])
                ? lottoSmsNormalizePhone($smsConfig['sender_phone'])
                : '';

            if ($smsSender === '') {
                $response['winner_sms_queue'] = array(
                    'success' => false,
                    'status' => 'skipped',
                    'draw_no' => $sourceDrawNo,
                    'error' => 'SMS 관리에 발신번호가 설정되어 있지 않습니다.',
                );

                $response['winner_sms_sync'] = array(
                    'success' => false,
                    'status' => 'skipped',
                    'draw_no' => $sourceDrawNo,
                    'error' => '발신번호가 없어 문자 결과 동기화를 건너뜁니다.',
                );
            } else {
                $smsQueue = lottoSmsQueuePendingWinners(
                    $sourceDrawNo,
                    $smsSender
                );

                $response['winner_sms_queue'] = $smsQueue;

                if (
                    isset($smsQueue['success'])
                    && $smsQueue['success']
                ) {
                    $smsSync = lottoSmsSyncWinnerResults(
                        $sourceDrawNo
                    );

                    $response['winner_sms_sync'] = $smsSync;
                } else {
                    $response['winner_sms_sync'] = array(
                        'success' => false,
                        'status' => 'skipped',
                        'draw_no' => $sourceDrawNo,
                        'error' => '문자 큐 등록 실패로 결과 동기화를 건너뜁니다.',
                    );
                }
            }
        } catch (Throwable $smsError) {
            $response['winner_sms_queue'] = array(
                'success' => false,
                'status' => 'failed',
                'draw_no' => $sourceDrawNo,
                'error' => $smsError->getMessage(),
            );

            $response['winner_sms_sync'] = array(
                'success' => false,
                'status' => 'skipped',
                'draw_no' => $sourceDrawNo,
                'error' => '문자 처리 오류로 결과 동기화를 건너뜁니다.',
            );
        }
    } else {
        $response['winner_sms_queue'] = array(
            'status' => 'skipped',
            'draw_no' => $sourceDrawNo,
            'message' => '회원 배분 조합이 없어 문자 큐 등록을 건너뜁니다.',
        );

        $response['winner_sms_sync'] = array(
            'status' => 'skipped',
            'draw_no' => $sourceDrawNo,
            'message' => '회원 배분 조합이 없어 문자 결과 동기화를 건너뜁니다.',
        );
    }

    /*
     * result 작업은 공식 결과 저장과 회원 당첨결과 계산까지만 처리한다.
     *
     * 토요일 21:00 / 21:30 / 22:00 및
     * 일요일 10:00 재시도에서 사용한다.
     *
     * 다음 회차 필터와 회원 배분은 일요일 weekly 작업에서만 처리한다.
     */
    if ($job === 'result') {
        $response['success'] = true;
        $response['status'] = 'completed';
        $response['finished_at'] = (
            new DateTimeImmutable(
                'now',
                new DateTimeZone('Asia/Seoul')
            )
        )->format('Y-m-d H:i:s');

        lottoCronRespond(
            200,
            $response
        );
    }

    /*
     * 4. 최신 당첨 회차의 다음 회차 필터 생성
     */
    $targetDrawNo = $sourceDrawNo + 1;

    $existingRun = sql_fetch(
        "select
            lfr_id,
            status,
            candidate_count
         from l_filter_run
         where draw_no = '{$targetDrawNo}'
         limit 1",
        false
    );

    if (
        isset($existingRun['lfr_id'])
        && (int) $existingRun['lfr_id'] > 0
        && isset($existingRun['status'])
        && $existingRun['status'] === 'filtered'
        && isset($existingRun['candidate_count'])
        && (int) $existingRun['candidate_count'] > 0
    ) {
        $response['filter'] = array(
            'status' => 'exists',
            'draw_no' => $targetDrawNo,
            'candidate_count' => (int)
                $existingRun['candidate_count'],
            'message' => $targetDrawNo
                . '회 필터 결과가 이미 존재합니다.',
        );
    } else {
        $sumRange = lottoFilterGetSumRange();

        $sumMin = isset($sumRange['min'])
            ? (int) $sumRange['min']
            : 100;

        $sumMax = isset($sumRange['max'])
            ? (int) $sumRange['max']
            : 190;

        if (
            $sumMin < 21
            || $sumMax > 255
            || $sumMin > $sumMax
        ) {
            throw new RuntimeException(
                '저장된 총합 필터 범위가 올바르지 않습니다.'
            );
        }

        $startedFilterAt = microtime(true);

        $filterResult = lottoFilterExecuteRun(
            $targetDrawNo,
            $sourceDrawNo,
            'cron',
            $sumMin,
            $sumMax
        );

        $elapsed = microtime(true)
            - $startedFilterAt;

        if (
            !isset($filterResult['success'])
            || !$filterResult['success']
        ) {
            throw new RuntimeException(
                '필터 실행 실패: '
                . (
                    isset($filterResult['error'])
                        ? $filterResult['error']
                        : '알 수 없는 오류'
                )
            );
        }

        $response['filter'] = array(
            'status' => 'filtered',
            'draw_no' => $targetDrawNo,
            'source_draw_no' => $sourceDrawNo,
            'candidate_count' => isset(
                $filterResult['candidate_count']
            )
                ? (int) $filterResult['candidate_count']
                : 0,
            'sum_min' => $sumMin,
            'sum_max' => $sumMax,
            'elapsed_seconds' => round(
                $elapsed,
                4
            ),
        );
    }

    /*
     * 4. 정상 weekly 작업에서만
     *    다음 회차 유료회원 월~금 조합을 일괄 배분한다.
     *
     * recover는 결과/필터 복구 전용이므로 배분하지 않는다.
     */
    if ($job === 'weekly') {
        $distributionResult = lottoDistributionRunWeeklyPaid(
            $targetDrawNo,
            $weeklyBaseDate,
            false,
            'cron'
        );

        if (
            !isset($distributionResult['success'])
            || !$distributionResult['success']
        ) {
            throw new RuntimeException(
                '주간 유료회원 배분 실패: '
                . (
                    isset($distributionResult['error'])
                        ? $distributionResult['error']
                        : '알 수 없는 오류'
                )
            );
        }

        $response['distribution'] = array(
            'status' => 'completed',
            'draw_no' => $targetDrawNo,
            'week_start' => $distributionResult['week_start'],
            'week_end' => $distributionResult['week_end'],
            'eligible_members' => (int)
                $distributionResult['eligible_members'],
            'processed_members' => (int)
                $distributionResult['processed_members'],
            'skipped_members' => (int)
                $distributionResult['skipped_members'],
            'combination_count' => (int)
                $distributionResult['combination_count'],
            'saturday' => 'excluded',
            'free_member' => 'excluded',
        );
    }

    $response['success'] = true;
    $response['status'] = 'completed';
    $response['finished_at'] = (
        new DateTimeImmutable(
            'now',
            new DateTimeZone('Asia/Seoul')
        )
    )->format('Y-m-d H:i:s');
} catch (Throwable $e) {
    $response['success'] = false;
    $response['status'] = 'failed';
    $response['message'] = $e->getMessage();
} finally {
    if ($lockAcquired) {
        sql_fetch(
            "select release_lock(
                '" . sql_real_escape_string(
                    $lockName
                ) . "'
            ) as released",
            false
        );
    }
}

lottoCronRespond(
    $response['success'] ? 200 : 500,
    $response
);
