<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

function lotto_result_log($message)
{
    $log_dir = G5_DATA_PATH . '/log';

    if (!is_dir($log_dir)) {
        @mkdir($log_dir, G5_DIR_PERMISSION, true);
    }

    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    @file_put_contents($log_dir . '/lotto_result_sync.log', $line, FILE_APPEND | LOCK_EX);
}

function lotto_result_request($url, $cookie_file, $referer = '', $max_attempts = 3)
{
    $max_attempts = max(1, (int) $max_attempts);

    for ($attempt = 1; $attempt <= $max_attempts; $attempt++) {
        try {
            return lotto_result_request_once($url, $cookie_file, $referer);
        } catch (RuntimeException $e) {
            if ($attempt >= $max_attempts) {
                throw $e;
            }

            lotto_result_log(
                '동행복권 요청 재시도 '
                . $attempt . '/' . $max_attempts
                . ': ' . $e->getMessage()
            );

            sleep(3);
        }
    }

    throw new RuntimeException('동행복권 요청 재시도에 실패했습니다.');
}

function lotto_result_request_once($url, $cookie_file, $referer = '')
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);

        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 40,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; LottoGPT Result Sync/1.0)',
            CURLOPT_COOKIEJAR => $cookie_file,
            CURLOPT_COOKIEFILE => $cookie_file,
            CURLOPT_HTTPHEADER => array(
                'Accept: text/html,application/json;q=0.9,*/*;q=0.8',
                'Accept-Language: ko-KR,ko;q=0.9,en;q=0.7',
            ),
        ));

        if ($referer !== '') {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }

        $body = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($body === false || $error !== '') {
            throw new RuntimeException('동행복권 연결 실패: ' . $error);
        }

        if ($status !== 200) {
            throw new RuntimeException('동행복권 HTTP 응답 오류: ' . $status);
        }

        if ($body === '') {
            throw new RuntimeException('동행복권 응답 내용이 비어 있습니다.');
        }

        return $body;
    }

    // 웹 PHP에 cURL 확장이 없어도 시스템 curl 명령을 사용할 수 있습니다.
    $curl_binary = '/usr/bin/curl';

    if (function_exists('proc_open') && is_executable($curl_binary)) {
        $command = array(
            $curl_binary,
            '--silent',
            '--show-error',
            '--location',
            '--connect-timeout', '5',
            '--max-time', '15',
            '--user-agent', 'Mozilla/5.0 (compatible; LottoGPT Result Sync/1.0)',
            '--header', 'Accept: text/html,application/json;q=0.9,*/*;q=0.8',
            '--header', 'Accept-Language: ko-KR,ko;q=0.9,en;q=0.7',
            '--cookie', $cookie_file,
            '--cookie-jar', $cookie_file,
            '--write-out', "\n%{http_code}",
        );

        if ($referer !== '') {
            $command[] = '--referer';
            $command[] = $referer;
        }

        $command[] = $url;

        $descriptors = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );

        $process = proc_open($command, $descriptors, $pipes);

        if (is_resource($process)) {
            fclose($pipes[0]);

            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);

            fclose($pipes[1]);
            fclose($pipes[2]);

            $exit_code = proc_close($process);

            $output = (string) $output;
            $last_newline = strrpos($output, "\n");

            $body = $last_newline === false
                ? ''
                : substr($output, 0, $last_newline);

            $status = $last_newline === false
                ? 0
                : (int) trim(substr($output, $last_newline + 1));

            if ($exit_code !== 0) {
                throw new RuntimeException(
                    '동행복권 연결 실패: '
                    . ($error !== '' ? trim($error) : 'curl 종료 코드 ' . $exit_code)
                );
            }

            if ($status !== 200) {
                throw new RuntimeException('동행복권 HTTP 응답 오류: ' . $status);
            }

            if ($body === '') {
                throw new RuntimeException('동행복권 응답 내용이 비어 있습니다.');
            }

            return $body;
        }
    }

    if (!filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN)) {
        throw new RuntimeException('외부 URL 요청 기능을 사용할 수 없습니다.');
    }

    $headers = array(
        'User-Agent: Mozilla/5.0 (compatible; LottoGPT Result Sync/1.0)',
        'Accept: text/html,application/json;q=0.9,*/*;q=0.8',
        'Accept-Language: ko-KR,ko;q=0.9,en;q=0.7',
        'Connection: close',
    );

    if ($referer !== '') {
        $headers[] = 'Referer: ' . $referer;
    }

    $saved_cookie = is_file($cookie_file)
        ? trim((string) file_get_contents($cookie_file))
        : '';

    if ($saved_cookie !== '') {
        $headers[] = 'Cookie: ' . $saved_cookie;
    }

    $context = stream_context_create(array(
        'http' => array(
            'method' => 'GET',
            'header' => implode("\r\n", $headers),
            'timeout' => 40,
            'ignore_errors' => true,
            'follow_location' => 1,
            'max_redirects' => 5,
        ),
        'ssl' => array(
            'verify_peer' => true,
            'verify_peer_name' => true,
        ),
    ));

    $body = @file_get_contents($url, false, $context);
    $response_headers = isset($http_response_header) && is_array($http_response_header)
        ? $http_response_header
        : array();

    $status = 0;
    $cookies = array();

    foreach ($response_headers as $header) {
        if (preg_match('#^HTTP/\S+\s+([0-9]{3})#i', $header, $matches)) {
            $status = (int) $matches[1];
        }

        if (stripos($header, 'Set-Cookie:') === 0) {
            $cookie = trim(substr($header, strlen('Set-Cookie:')));
            $cookie = explode(';', $cookie, 2)[0];

            if ($cookie !== '') {
                $cookies[] = $cookie;
            }
        }
    }

    if ($cookies) {
        file_put_contents(
            $cookie_file,
            implode('; ', array_unique($cookies)),
            LOCK_EX
        );
    }

    if ($body === false) {
        $last_error = error_get_last();
        $detail = isset($last_error['message']) ? $last_error['message'] : '알 수 없는 오류';

        throw new RuntimeException('동행복권 연결 실패: ' . $detail);
    }

    if ($status !== 200) {
        throw new RuntimeException('동행복권 HTTP 응답 오류: ' . $status);
    }

    if ($body === '') {
        throw new RuntimeException('동행복권 응답 내용이 비어 있습니다.');
    }

    return $body;
}

function lotto_result_extract_latest_draw($html)
{
    if (!preg_match('/id=["\']opt_val["\'][^>]*value=["\']([0-9]+)["\']/i', $html, $matches)) {
        throw new RuntimeException('최신 회차를 결과 페이지에서 찾지 못했습니다.');
    }

    $draw_no = (int) $matches[1];

    if ($draw_no < 1) {
        throw new RuntimeException('최신 회차 값이 올바르지 않습니다.');
    }

    return $draw_no;
}

function lotto_result_validate_item($item, $expected_draw)
{
    $required = array(
        'ltEpsd', 'ltRflYmd',
        'tm1WnNo', 'tm2WnNo', 'tm3WnNo',
        'tm4WnNo', 'tm5WnNo', 'tm6WnNo',
        'bnsWnNo',
        'rnk1WnNope', 'rnk1WnAmt',
        'rnk2WnNope', 'rnk2WnAmt',
        'rnk3WnNope', 'rnk3WnAmt',
        'rnk4WnNope', 'rnk4WnAmt',
        'rnk5WnNope', 'rnk5WnAmt',
    );

    foreach ($required as $key) {
        if (!array_key_exists($key, $item)) {
            throw new RuntimeException('결과 필드 누락: ' . $key);
        }
    }

    if ((int) $item['ltEpsd'] !== (int) $expected_draw) {
        throw new RuntimeException('요청 회차와 응답 회차가 일치하지 않습니다.');
    }

    $numbers = array();

    for ($i = 1; $i <= 6; $i++) {
        $number = (int) $item['tm' . $i . 'WnNo'];

        if ($number < 1 || $number > 45) {
            throw new RuntimeException('당첨번호 범위가 올바르지 않습니다.');
        }

        $numbers[] = $number;
    }

    if (count(array_unique($numbers)) !== 6) {
        throw new RuntimeException('당첨번호가 중복되어 있습니다.');
    }

    $bonus = (int) $item['bnsWnNo'];

    if ($bonus < 1 || $bonus > 45 || in_array($bonus, $numbers, true)) {
        throw new RuntimeException('보너스번호가 올바르지 않습니다.');
    }

    $date_raw = (string) $item['ltRflYmd'];

    if (!preg_match('/^[0-9]{8}$/', $date_raw)) {
        throw new RuntimeException('추첨일 형식이 올바르지 않습니다.');
    }

    $draw_date = substr($date_raw, 0, 4)
        . '-' . substr($date_raw, 4, 2)
        . '-' . substr($date_raw, 6, 2);

    if (!checkdate(
        (int) substr($date_raw, 4, 2),
        (int) substr($date_raw, 6, 2),
        (int) substr($date_raw, 0, 4)
    )) {
        throw new RuntimeException('추첨일 값이 올바르지 않습니다.');
    }

    $rankWinners = array();
    $rankAmounts = array();

    for ($rank = 1; $rank <= 5; $rank++) {
        $winnerCount = (int) $item[
            'rnk' . $rank . 'WnNope'
        ];

        $winnerAmount = (int) $item[
            'rnk' . $rank . 'WnAmt'
        ];

        if ($winnerCount < 0) {
            throw new RuntimeException(
                $rank . '등 당첨자 수가 올바르지 않습니다.'
            );
        }

        if ($winnerAmount < 0) {
            throw new RuntimeException(
                $rank . '등 당첨금이 올바르지 않습니다.'
            );
        }

        $rankWinners[$rank] = $winnerCount;
        $rankAmounts[$rank] = $winnerAmount;
    }

    /*
     * 추첨 직후에는 당첨번호가 먼저 공개되고
     * 당첨자 수/당첨금 상세정보가 늦게 반영될 수 있다.
     *
     * 3~5등은 정상 회차에서 충분한 당첨자가 발생하는
     * 구간이므로 이 값들이 모두 확정되기 전에는
     * 공식 결과 완료로 인정하지 않는다.
     *
     * 1~2등은 이론적으로 당첨자가 없을 수 있으므로
     * 단순히 당첨자 수 > 0을 강제하지 않는다.
     */
    for ($rank = 3; $rank <= 5; $rank++) {
        if (
            $rankWinners[$rank] < 1
            || $rankAmounts[$rank] < 1
        ) {
            throw new RuntimeException(
                '공식 당첨 상세정보가 아직 확정되지 않았습니다.'
            );
        }
    }

    /*
     * 1~2등은 당첨자가 존재하는 경우 반드시
     * 당첨금액도 확정되어 있어야 한다.
     */
    for ($rank = 1; $rank <= 2; $rank++) {
        if (
            $rankWinners[$rank] > 0
            && $rankAmounts[$rank] < 1
        ) {
            throw new RuntimeException(
                '공식 당첨 상세정보가 아직 확정되지 않았습니다.'
            );
        }
    }

    return array(
        'draw_no' => (int) $item['ltEpsd'],
        'draw_date' => $draw_date,
        'num_1' => $numbers[0],
        'num_2' => $numbers[1],
        'num_3' => $numbers[2],
        'num_4' => $numbers[3],
        'num_5' => $numbers[4],
        'num_6' => $numbers[5],
        'bonus_num' => $bonus,
        'rank1_winners' => (int) $item['rnk1WnNope'],
        'rank1_amount' => (int) $item['rnk1WnAmt'],
        'rank2_winners' => (int) $item['rnk2WnNope'],
        'rank2_amount' => (int) $item['rnk2WnAmt'],
        'rank3_winners' => (int) $item['rnk3WnNope'],
        'rank3_amount' => (int) $item['rnk3WnAmt'],
        'rank4_winners' => (int) $item['rnk4WnNope'],
        'rank4_amount' => (int) $item['rnk4WnAmt'],
        'rank5_winners' => (int) $item['rnk5WnNope'],
        'rank5_amount' => (int) $item['rnk5WnAmt'],
    );
}

function lotto_result_open_latest()
{
    $result_url = 'https://www.dhlottery.co.kr/lt645/result';
    $cookie_file = tempnam(sys_get_temp_dir(), 'lottogpt_cookie_');

    if ($cookie_file === false) {
        throw new RuntimeException('임시 쿠키 파일을 만들지 못했습니다.');
    }

    try {
        $html = lotto_result_request($result_url, $cookie_file);
        $draw_no = lotto_result_extract_latest_draw($html);

        return array(
            'draw_no' => $draw_no,
            'result_url' => $result_url,
            'cookie_file' => $cookie_file,
        );
    } catch (Throwable $e) {
        @unlink($cookie_file);
        throw $e;
    }
}

function lotto_result_fetch_draw($context)
{
    $draw_no = isset($context['draw_no']) ? (int) $context['draw_no'] : 0;
    $result_url = isset($context['result_url']) ? (string) $context['result_url'] : '';
    $cookie_file = isset($context['cookie_file']) ? (string) $context['cookie_file'] : '';

    if ($draw_no < 1 || $result_url === '' || $cookie_file === '') {
        throw new RuntimeException('결과 조회 정보가 올바르지 않습니다.');
    }

    $query = http_build_query(array(
        'srchDir' => 'center',
        'srchLtEpsd' => $draw_no,
    ));

    $json_url = 'https://www.dhlottery.co.kr/lt645/selectPstLt645InfoNew.do?' . $query;
    $json_body = lotto_result_request($json_url, $cookie_file, $result_url);
    $payload = json_decode($json_body, true);

    if (!is_array($payload) || json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException('결과 응답이 올바른 JSON 형식이 아닙니다.');
    }

    $list = isset($payload['data']['list']) && is_array($payload['data']['list'])
        ? $payload['data']['list']
        : array();

    foreach ($list as $item) {
        if ((int) ($item['ltEpsd'] ?? 0) === $draw_no) {
            $result = lotto_result_validate_item($item, $draw_no);
            $result['source_url'] = $result_url;

            return $result;
        }
    }

    throw new RuntimeException($draw_no . '회 결과가 아직 게시되지 않았습니다.');
}

function lotto_result_close_context($context)
{
    if (!empty($context['cookie_file'])) {
        @unlink($context['cookie_file']);
    }
}

function lotto_result_table_name()
{
    global $g5;

    $prefix = isset($g5['table_prefix']) ? $g5['table_prefix'] : 'g5_';

    return $prefix . 'lotto_result';
}

function lotto_result_get_latest_saved()
{
    $table = lotto_result_table_name();

    return sql_fetch(
        " select * from `{$table}` order by draw_no desc limit 1 ",
        false
    );
}

function lotto_result_save($result)
{
    $table = lotto_result_table_name();
    $draw_no = (int) $result['draw_no'];

    $existing = sql_fetch(
        " select draw_no from `{$table}` where draw_no = '{$draw_no}' ",
        false
    );

    if (isset($existing['draw_no']) && (int) $existing['draw_no'] === $draw_no) {
        return 'exists';
    }

    $source_url = sql_real_escape_string((string) $result['source_url']);

    $sql = " insert into `{$table}` set
                draw_no = '{$draw_no}',
                draw_date = '" . sql_real_escape_string($result['draw_date']) . "',
                num_1 = '" . (int) $result['num_1'] . "',
                num_2 = '" . (int) $result['num_2'] . "',
                num_3 = '" . (int) $result['num_3'] . "',
                num_4 = '" . (int) $result['num_4'] . "',
                num_5 = '" . (int) $result['num_5'] . "',
                num_6 = '" . (int) $result['num_6'] . "',
                bonus_num = '" . (int) $result['bonus_num'] . "',
                rank1_winners = '" . (int) $result['rank1_winners'] . "',
                rank1_amount = '" . (int) $result['rank1_amount'] . "',
                rank2_winners = '" . (int) $result['rank2_winners'] . "',
                rank2_amount = '" . (int) $result['rank2_amount'] . "',
                rank3_winners = '" . (int) $result['rank3_winners'] . "',
                rank3_amount = '" . (int) $result['rank3_amount'] . "',
                rank4_winners = '" . (int) $result['rank4_winners'] . "',
                rank4_amount = '" . (int) $result['rank4_amount'] . "',
                rank5_winners = '" . (int) $result['rank5_winners'] . "',
                rank5_amount = '" . (int) $result['rank5_amount'] . "',
                source_url = '{$source_url}',
                fetched_at = '" . date('Y-m-d H:i:s') . "' ";

    if (!sql_query($sql, false)) {
        throw new RuntimeException('당첨결과를 DB에 저장하지 못했습니다.');
    }

    return 'inserted';
}
