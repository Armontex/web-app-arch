<?php
require_once __DIR__ . '/partials/session.php';

$client_id = getenv('GITHUB_CLIENT_ID') ?: '';

if ($client_id === '') {
    http_response_code(500);
    die('GITHUB_CLIENT_ID is not configured');
}

$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$params = http_build_query([
    'client_id' => $client_id,
    'redirect_uri' => 'http://localhost/oauth-callback.php',
    'scope' => 'read:user',
    'state' => $state,
]);

header("Location: https://github.com/login/oauth/authorize?{$params}");
exit;
