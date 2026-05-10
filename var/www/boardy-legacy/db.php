<?php
$db_host = getenv('DB__HOST') ?: 'db';
$db_name = getenv('DB__NAME') ?: 'boardy';
$db_user = getenv('DB__USER') ?: 'boardy';
$db_pass = getenv('DB__PASSWORD') ?: 'boardy';

$dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    die('Ошибка подключения к базе данных');
}
