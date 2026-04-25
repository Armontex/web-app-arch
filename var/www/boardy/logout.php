<?php
require_once __DIR__ . '/partials/session.php';

session_unset();
session_destroy();

setcookie('PHPSESSID', '', [
    'expires' => time() - 3600,
    'path' => $session_cookie_params['path'],
    'secure' => $session_cookie_params['secure'],
    'httponly' => $session_cookie_params['httponly'],
    'samesite' => $session_cookie_params['samesite'],
]);

header('Location: /messages.php');
exit;
