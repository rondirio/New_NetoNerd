<?php
/**
 * Criptografia simétrica reversível (AES-256-GCM) para dados que precisam
 * ser recuperados em texto puro (ex.: senha de banco de terceiro repassada
 * via API). Não usar para senhas de login — essas continuam com password_hash().
 * Chave em APP_SECRET_KEY (.env), nunca no código.
 */

function encryptSecret(string $plaintext): string
{
    $key = Config::get('APP_SECRET_KEY');
    if (empty($key)) {
        throw new RuntimeException('APP_SECRET_KEY não configurada no .env');
    }

    $binKey = hash('sha256', $key, true);
    $iv = random_bytes(12);
    $tag = '';
    $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $binKey, OPENSSL_RAW_DATA, $iv, $tag);

    return base64_encode($iv . $tag . $ciphertext);
}

function decryptSecret(string $encoded): string|false
{
    $key = Config::get('APP_SECRET_KEY');
    if (empty($key) || $encoded === '') {
        return false;
    }

    $raw = base64_decode($encoded, true);
    if ($raw === false || strlen($raw) < 28) {
        return false;
    }

    $binKey = hash('sha256', $key, true);
    $iv = substr($raw, 0, 12);
    $tag = substr($raw, 12, 16);
    $ciphertext = substr($raw, 28);

    $plaintext = openssl_decrypt($ciphertext, 'aes-256-gcm', $binKey, OPENSSL_RAW_DATA, $iv, $tag);

    return $plaintext;
}
