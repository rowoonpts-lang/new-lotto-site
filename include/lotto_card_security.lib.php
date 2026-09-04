<?php

function lottoCardGetSecretConfig()
{
    $secretFile = G5_DATA_PATH.'/lotto_card_secret.php';

    if (!is_file($secretFile)) {
        throw new RuntimeException('카드정보 암호화 키 파일이 없습니다.');
    }

    $config = include $secretFile;

    if (
        !is_array($config)
        || empty($config['key'])
        || empty($config['version'])
    ) {
        throw new RuntimeException('카드정보 암호화 키 설정이 올바르지 않습니다.');
    }

    $key = base64_decode((string) $config['key'], true);

    if (
        $key === false
        || strlen($key) !== 32
    ) {
        throw new RuntimeException('카드정보 암호화 키 길이가 올바르지 않습니다.');
    }

    return array(
        'version' => (int) $config['version'],
        'key' => $key,
    );
}

function lottoCardEncryptPayload(array $payload)
{
    if (
        !function_exists('openssl_encrypt')
        || !function_exists('random_bytes')
    ) {
        throw new RuntimeException(
            '카드정보 암호화 기능을 사용할 수 없습니다.'
        );
    }

    $secret = lottoCardGetSecretConfig();

    $json = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    if ($json === false) {
        throw new RuntimeException(
            '카드정보 암호화 데이터를 만들 수 없습니다.'
        );
    }

    $iv = random_bytes(12);
    $tag = '';

    $ciphertext = openssl_encrypt(
        $json,
        'aes-256-gcm',
        $secret['key'],
        OPENSSL_RAW_DATA,
        $iv,
        $tag,
        '',
        16
    );

    if ($ciphertext === false || strlen($tag) !== 16) {
        throw new RuntimeException('카드정보 암호화에 실패했습니다.');
    }

    return array(
        'payload' => 'gcm1:'.base64_encode(
            $iv.$tag.$ciphertext
        ),
        'key_version' => $secret['version'],
    );
}

function lottoCardDecryptPayload($encryptedPayload)
{
    if (!function_exists('openssl_decrypt')) {
        throw new RuntimeException(
            '카드정보 복호화 기능을 사용할 수 없습니다.'
        );
    }

    $secret = lottoCardGetSecretConfig();
    $encryptedPayload = trim((string) $encryptedPayload);

    if (strpos($encryptedPayload, 'gcm1:') !== 0) {
        throw new RuntimeException(
            '암호화된 카드정보 형식이 올바르지 않습니다.'
        );
    }

    $decoded = base64_decode(
        substr($encryptedPayload, 5),
        true
    );

    if ($decoded === false || strlen($decoded) <= 28) {
        throw new RuntimeException(
            '암호화된 카드정보 형식이 올바르지 않습니다.'
        );
    }

    $iv = substr($decoded, 0, 12);
    $tag = substr($decoded, 12, 16);
    $ciphertext = substr($decoded, 28);

    $json = openssl_decrypt(
        $ciphertext,
        'aes-256-gcm',
        $secret['key'],
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );

    if ($json === false) {
        throw new RuntimeException('카드정보 복호화에 실패했습니다.');
    }

    $payload = json_decode($json, true);

    if (!is_array($payload)) {
        throw new RuntimeException(
            '복호화된 카드정보가 올바르지 않습니다.'
        );
    }

    return $payload;
}
