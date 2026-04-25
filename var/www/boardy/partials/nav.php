<?php
// Ожидается, что вызвавшая страница сделала session_start() до include этого файла.
$is_logged = !empty($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? '';
$current_path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$is_messages = $current_path === '/' || $current_path === '/index.php' || $current_path === '/messages.php';
$is_submit = $current_path === '/submit.php';
$is_login = $current_path === '/login.php';
$is_register = $current_path === '/register.php';
?>
<header class="site-header">
  <div class="site-header__inner">
    <nav class="site-nav site-nav--left" aria-label="Главное меню">
      <a class="site-logo" href="/">Boardy</a>
      <a class="site-nav__link<?= $is_messages ? ' site-nav__link--active' : '' ?>" href="/messages.php">Все посты</a>

      <?php if ($is_logged): ?>
        <a class="site-nav__link<?= $is_submit ? ' site-nav__link--active' : '' ?>" href="/submit.php">Добавить пост</a>
      <?php endif; ?>
    </nav>

    <nav class="site-nav site-nav--right" aria-label="Пользовательское меню">
      <?php if ($is_logged): ?>
        <span class="site-nav__user">
          Привет, <?= htmlspecialchars($user_name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>!
        </span>
        <a class="site-nav__link" href="/logout.php">Выйти</a>
      <?php else: ?>
        <a class="site-nav__link<?= $is_login ? ' site-nav__link--active' : '' ?>" href="/login.php">Вход</a>
        <a class="site-nav__link<?= $is_register ? ' site-nav__link--active' : '' ?>" href="/register.php">Регистрация</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
