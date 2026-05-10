<?php
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/db.php';

$client_id = getenv('GITHUB_CLIENT_ID') ?: '';
$client_secret = getenv('GITHUB_CLIENT_SECRET') ?: '';

if ($client_id === '' || $client_secret === '') {
    http_response_code(500);
    die('GitHub OAuth is not configured');
}

if (($_GET['state'] ?? '') !== ($_SESSION['oauth_state'] ?? '')) {
    http_response_code(400);
    die('Invalid state');
}

unset($_SESSION['oauth_state']);

$code = $_GET['code'] ?? '';
if ($code === '') {
    http_response_code(400);
    die('Missing code');
}

function github_request(string $url, array $headers, ?array $post_data = null): array
{
    $curl = curl_init($url);

    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_USERAGENT => 'Boardy local OAuth',
    ]);

    if ($post_data !== null) {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));
    }

    $response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);

    if ($response === false || $status >= 400) {
        http_response_code(502);
        die('GitHub request failed: ' . ($error ?: $response));
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        http_response_code(502);
        die('Invalid GitHub response');
    }

    return $decoded;
}

$token_response = github_request(
    'https://github.com/login/oauth/access_token',
    ['Accept: application/json'],
    [
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'code' => $code,
        'redirect_uri' => 'http://localhost/oauth-callback.php',
    ],
);

$access_token = $token_response['access_token'] ?? '';
if ($access_token === '') {
    http_response_code(502);
    die('GitHub did not return access_token');
}

$profile = github_request(
    'https://api.github.com/user',
    [
        'Accept: application/json',
        'Authorization: Bearer ' . $access_token,
    ],
);

$github_id = (string) ($profile['id'] ?? '');
$login = (string) ($profile['login'] ?? '');
$name = trim((string) ($profile['name'] ?? '')) ?: $login;
$email = trim((string) ($profile['email'] ?? ''));

if ($github_id === '' || $login === '') {
    http_response_code(502);
    die('GitHub profile is incomplete');
}

if ($email === '') {
    $email = $login . '@users.noreply.github.com';
}

$stmt = $pdo->prepare('SELECT id, name FROM users WHERE github_id = ? LIMIT 1');
$stmt->execute([$github_id]);
$user = $stmt->fetch();

if (!$user) {
    $stmt = $pdo->prepare(
        'INSERT INTO users (name, email, password_hash, github_id) VALUES (?, ?, NULL, ?)'
    );
    $stmt->execute([$name, $email, $github_id]);

    $user = [
        'id' => (int) $pdo->lastInsertId(),
        'name' => $name,
    ];
}

$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['oauth_success'] = true;

header('Location: /messages.php');
exit;
