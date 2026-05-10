<?php
require_once __DIR__ . '/../partials/session.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated'], JSON_UNESCAPED_UNICODE);
    exit;
}

function base64url_encode(string $value): string
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function generate_jwt(int $user_id, string $user_name): string
{
    $secret_key = 'your-secret-key-change-me';

    $header = base64url_encode(json_encode([
        'alg' => 'HS256',
        'typ' => 'JWT',
    ]));

    $payload = base64url_encode(json_encode([
        'user_id' => $user_id,
        'name' => $user_name,
        'exp' => time() + 3600,
    ], JSON_UNESCAPED_UNICODE));

    $signature = base64url_encode(hash_hmac(
        'sha256',
        "{$header}.{$payload}",
        $secret_key,
        true,
    ));

    return "{$header}.{$payload}.{$signature}";
}

echo json_encode([
    'token' => generate_jwt((int) $_SESSION['user_id'], (string) $_SESSION['user_name']),
], JSON_UNESCAPED_UNICODE);
