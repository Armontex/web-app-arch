<?php
session_start();
require_once __DIR__ . '/db.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: /messages.php');
    exit;
}

$error_message = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error_message = 'Заполните email и пароль.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, name, password_hash FROM users WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        $password_valid = $user && (
            password_verify($password, $user['password_hash']) ||
            hash_equals((string) $user['password_hash'], $password)
        );

        if (!$password_valid) {
            $error_message = 'Неверный email или пароль.';
        } else {
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_name'] = $user['name'];

            header('Location: /messages.php');
            exit;
        }
    }
}

$page_title = 'Вход';

include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main class="page-shell">
  <section class="auth-layout">
    <div class="form-card card">
      <h1 class="page-title page-title--compact">Вход</h1>

      <?php if ($error_message !== ''): ?>
        <div class="notice notice--error">
          <?= htmlspecialchars($error_message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </div>
      <?php endif; ?>

      <form class="form-stack" action="/login.php" method="post">
        <div class="form-field">
          <label class="form-field__label" for="login-email">Email</label>
          <input
            class="form-field__control"
            id="login-email"
            name="email"
            type="email"
            value="<?= htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
          />
        </div>

        <div class="form-field">
          <label class="form-field__label" for="login-password">Пароль</label>
          <input
            class="form-field__control"
            id="login-password"
            name="password"
            type="password"
          />
        </div>

        <button class="btn btn-primary btn-block" type="submit">Войти</button>
      </form>

      <p class="form-card__hint">
        Нет аккаунта?
        <a class="text-link" href="/register.php">Регистрация</a>
      </p>
    </div>
  </section>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
