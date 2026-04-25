<?php
require_once __DIR__ . '/partials/session.php';

if (empty($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/db.php';

$error_message = '';
$post_text = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_text = trim($_POST['text'] ?? '');

    if ($post_text === '') {
        $error_message = 'Введите текст объявления.';
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO posts (title, body, author_id) VALUES (?, ?, ?)'
        );
        $stmt->execute(['Сообщение', $post_text, $_SESSION['user_id']]);

        header('Location: /messages.php');
        exit;
    }
}

$page_title = 'Новый пост';

include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main class="page-shell">
  <section class="form-layout">
    <div class="form-card form-card-wide card">
      <h1 class="page-title page-title--compact">Новый пост</h1>

      <?php if ($error_message !== ''): ?>
        <div class="notice notice--error">
          <?= htmlspecialchars($error_message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </div>
      <?php endif; ?>

      <form class="form-stack" action="/submit.php" method="post">
        <div class="form-field">
          <label class="form-field__label" for="post-text">Текст</label>
          <textarea
            class="form-field__control form-field__control--textarea"
            id="post-text"
            name="text"
            rows="5"
            placeholder="Напишите ваше объявление..."
          ><?= htmlspecialchars($post_text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
        </div>

        <div class="form-actions">
          <button class="btn btn-primary" type="submit">Опубликовать</button>
          <a class="text-link text-link--inline" href="/messages.php">Отмена</a>
        </div>
      </form>
    </div>
  </section>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
