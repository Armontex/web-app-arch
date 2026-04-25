<?php
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/db.php';

if (!empty($_SESSION['user_id'])) {
  header('Location: /messages.php');
  exit;
}

$error_message = '';
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($name === '' || $email === '' || $password === '') {
    $error_message = 'Заполните все поля.';
  } elseif (mb_strlen($password) < 6) {
    $error_message = 'Пароль должен содержать минимум 6 символов.';
  } else {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
      $error_message = 'Пользователь с таким email уже существует.';
    } else {
      $password_hash = password_hash($password, PASSWORD_DEFAULT);
      $insert = $pdo->prepare(
        'INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)'
      );
      $insert->execute([$name, $email, $password_hash]);

      $_SESSION['user_id'] = (int) $pdo->lastInsertId();
      $_SESSION['user_name'] = $name;

      header('Location: /messages.php');
      exit;
    }
  }
}

$page_title = 'Регистрация';

include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main class="page-shell">
  <section class="auth-layout">
    <div class="form-card card">
      <h1 class="page-title page-title--compact">Регистрация</h1>

      <?php if ($error_message !== ''): ?>
        <div class="notice notice--error">
          <?= htmlspecialchars($error_message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </div>
      <?php endif; ?>

      <form class="form-stack" action="/register.php" method="post">
        <div class="form-field">
          <label class="form-field__label" for="register-name">Имя</label>
          <input class="form-field__control" id="register-name" name="name" type="text"
            value="<?= htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" />
        </div>

        <div class="form-field">
          <label class="form-field__label" for="register-email">Email</label>
          <input class="form-field__control" id="register-email" name="email" type="email"
            value="<?= htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" />
        </div>

        <div class="form-field">
          <label class="form-field__label" for="register-password">Пароль</label>
          <input class="form-field__control" id="register-password" name="password" type="password" />
        </div>

        <button class="btn btn-primary btn-block" type="submit">Зарегистрироваться</button>
      </form>

      <p class="form-card__hint">
        Уже есть аккаунт?
        <a class="text-link" href="/login.php">Войти</a>
      </p>
    </div>
  </section>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
