<?php
session_start();
require_once __DIR__ . '/db.php';

$stmt = $pdo->query(
    'SELECT posts.body, users.name, posts.created_at
     FROM posts
     JOIN users ON posts.author_id = users.id
     ORDER BY posts.created_at DESC'
);
$posts = $stmt->fetchAll();

$page_title = 'Все посты';

function boardy_format_relative_time(string $raw_time): string
{
    $created_at = strtotime($raw_time);
    if ($created_at === false) {
        return $raw_time;
    }

    $diff = time() - $created_at;

    if ($diff < 60) {
        return 'только что';
    }

    if ($diff < 3600) {
        $minutes = max(1, (int) floor($diff / 60));
        return $minutes . ' мин назад';
    }

    if ($diff < 86400) {
        $hours = max(1, (int) floor($diff / 3600));
        return $hours . ' час назад';
    }

    if ($diff < 172800) {
        return 'вчера';
    }

    return date('d.m.Y H:i', $created_at);
}

include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main class="page-shell">
  <section class="content-stack">
    <h1 class="page-title">Все посты</h1>

    <?php if (empty($posts)): ?>
      <div class="card empty-state">Постов пока нет.</div>
    <?php else: ?>
      <div class="post-list">
        <?php foreach ($posts as $post): ?>
          <article class="post-card card">
            <div class="post-card__meta">
              <h2 class="post-card__author">
                <?= htmlspecialchars($post['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
              </h2>
              <time class="post-card__time" datetime="<?= htmlspecialchars($post['created_at'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <?= htmlspecialchars(boardy_format_relative_time($post['created_at']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
              </time>
            </div>

            <p class="post-card__text"><?= nl2br(htmlspecialchars($post['body'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')) ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
