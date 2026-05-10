<?php
$page_title = $page_title ?? 'Boardy';
?>
<!doctype html>
<html lang="ru">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= htmlspecialchars($page_title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> | Boardy</title>
    <link rel="stylesheet" href="/css/style.css?v=11" />
  </head>
  <body>
