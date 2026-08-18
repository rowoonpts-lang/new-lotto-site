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
        || strlen($key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES
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
    $secret = lottoCardGetSecretConfig();

    $json = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    if ($json === false) {
        throw new RuntimeException('카드정보 암호화 데이터를 만들 수 없습니다.');
    }

    $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    $ciphertext = sodium_crypto_secretbox(
        $json,
        $nonce,
        $secret['key']
    );

    return array(
        'payload' => base64_encode($nonce.$ciphertext),
        'key_version' => $secret['version'],
    );
}

function lottoCardDecryptPayload($encryptedPayload)
{
    $secret = lottoCardGetSecretConfig();

    $decoded = base64_decode((string) $encryptedPayload, true);

    if (
        $decoded === false
        || strlen($decoded) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES
    ) {
        throw new RuntimeException('암호화된 카드정보 형식이 올바르지 않습니다.');
    }

    $nonce = substr(
        $decoded,
        0,
        SODIUM_CRYPTO_SECRETBOX_NONCEBYTES
    );

    $ciphertext = substr(
        $decoded,
        SODIUM_CRYPTO_SECRETBOX_NONCEBYTES
    );

    $json = sodium_crypto_secretbox_open(
        $ciphertext,
        $nonce,
        $secret['key']
    );

    if ($json === false) {
        throw new RuntimeException('카드정보 복호화에 실패했습니다.');
    }

    $payload = json_decode($json, true);

    if (!is_array($payload)) {
        throw new RuntimeException('복호화된 카드정보가 올바르지 않습니다.');
    }

    return $payload;
}
