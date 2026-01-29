<?php
/**
 * StyleManager API - JWT Handler
 *
 * Gerencia tokens JWT para autenticação do app mobile.
 */

class StyleManagerJWT {
    private $secretKey;
    private $algorithm = 'HS256';
    private $tokenExpiration = 86400 * 30; // 30 dias
    private $refreshExpiration = 86400 * 90; // 90 dias

    public function __construct() {
        // Chave secreta do ambiente ou fallback
        $this->secretKey = getenv('JWT_SECRET') ?: 'stylemanager_jwt_secret_key_2026_change_in_production';
    }

    /**
     * Gera um token JWT
     */
    public function generate(array $payload, bool $isRefreshToken = false): string {
        $header = [
            'alg' => $this->algorithm,
            'typ' => 'JWT'
        ];

        $now = time();
        $expiration = $isRefreshToken ? $this->refreshExpiration : $this->tokenExpiration;

        $payload = array_merge($payload, [
            'iat' => $now,
            'exp' => $now + $expiration,
            'jti' => bin2hex(random_bytes(16)),
            'type' => $isRefreshToken ? 'refresh' : 'access'
        ]);

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $this->secretKey, true);
        $signatureEncoded = $this->base64UrlEncode($signature);

        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    /**
     * Valida e decodifica um token JWT
     */
    public function validate(string $token): ?array {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        // Verifica assinatura
        $signature = $this->base64UrlDecode($signatureEncoded);
        $expectedSignature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $this->secretKey, true);

        if (!hash_equals($expectedSignature, $signature)) {
            return null;
        }

        // Decodifica payload
        $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);

        if (!$payload) {
            return null;
        }

        // Verifica expiração
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    /**
     * Gera par de tokens (access + refresh)
     */
    public function generateTokenPair(array $userData): array {
        $payload = [
            'user_id' => $userData['id'],
            'user_nome' => $userData['nome'],
            'user_tipo' => $userData['tipo'],
            'user_email' => $userData['email'],
            'api_key' => $userData['api_key'],
            'estabelecimento' => $userData['estabelecimento']
        ];

        return [
            'token' => $this->generate($payload, false),
            'refresh_token' => $this->generate($payload, true),
            'expires_in' => $this->tokenExpiration
        ];
    }

    /**
     * Renova token usando refresh token
     */
    public function refresh(string $refreshToken): ?array {
        $payload = $this->validate($refreshToken);

        if (!$payload) {
            return null;
        }

        // Verifica se é um refresh token
        if (($payload['type'] ?? '') !== 'refresh') {
            return null;
        }

        // Gera novo par de tokens
        return $this->generateTokenPair([
            'id' => $payload['user_id'],
            'nome' => $payload['user_nome'],
            'tipo' => $payload['user_tipo'],
            'email' => $payload['user_email'],
            'api_key' => $payload['api_key'],
            'estabelecimento' => $payload['estabelecimento']
        ]);
    }

    /**
     * Codifica em Base64 URL-safe
     */
    private function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decodifica de Base64 URL-safe
     */
    private function base64UrlDecode(string $data): string {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
