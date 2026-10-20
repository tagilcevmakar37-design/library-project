<?php
require_once __DIR__ . '/config/db.php';
session_start();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: books.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM books WHERE id = ?');
$stmt->execute([$id]);
$book = $stmt->fetch();
if (!$book) { http_response_code(404); die('Книга не найдена'); }

$pageTitle = $book['title'] . ' — Читальня';
$user = $_SESSION['user'] ?? null;
$inLibrary = null; $isFavorite = false; $progress = 0;

if ($user) {
    $stmt = $pdo->prepare('SELECT status, progress, is_favorite FROM user_library WHERE user_id = ? AND book_id = ?');
    $stmt->execute([$user['id'], $id]);
    $row = $stmt->fetch();
    if ($row) { $inLibrary = $row['status']; $isFavorite = (bool)$row['is_favorite']; $progress = (int)$row['progress']; }
}

// Обработка добавления в избранное
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user && isset($_POST['action'])) {
    if ($_POST['action'] === 'toggle_favorite') {
        $stmt = $pdo->prepare('INSERT INTO user_library (user_id, book_id, is_favorite) VALUES (?, ?, 1)
            ON DUPLICATE KEY UPDATE is_favorite = 1 - is_favorite');
        $stmt->execute([$user['id'], $id]);
        header("Location: book.php?id=$id"); exit;
    }
    if ($_POST['action'] === 'mark_finished') {
        $stmt = $pdo->prepare('INSERT INTO user_library (user_id, book_id, status, progress) VALUES (?, ?, "finished", 100)
            ON DUPLICATE KEY UPDATE status = "finished", progress = 100');
        $stmt->execute([$user['id'], $id]);
        header("Location: book.php?id=$id"); exit;
    }
}

require __DIR__ . '/includes/header.php';

function cover_gradient($id) {
    $p = ['#8b6f47,#5c4530','#7a4f3f,#4a2e25','#6b7a4f,#3f4a2a','#4f6b7a,#2a3f4a','#7a4f6b,#4a2a3f','#7a6b4f,#4a3f2a'];
    return 'linear-gradient(135deg, ' . $p[$id % count($p)] . ')';
}
?>
<div class="book-page">
    <div class="book-cover-large" style="background: <?= cover_gradient($book['id']) ?>">
        <span><?= htmlspecialchars(mb_substr($book['title'], 0, 1)) ?></span>
    </div>
    <div class="book-page-info">
        <h1><?= htmlspecialchars($book['title']) ?></h1>
        <p class="book-author"><?= htmlspecialchars($book['author']) ?></p>
        <p class="book-meta">
            <span><?= htmlspecialchars($book['genre']) ?></span>
            <span><?= (int)$book['year'] ?> г.</span>
        </p>
        <p class="book-description"><?= nl2br(htmlspecialchars($book['description'])) ?></p>

        <div class="book-actions">
            <a href="read.php?id=<?= $book['id'] ?>" class="btn">📖 Читать</a>
            <?php if ($user): ?>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="action" value="toggle_favorite">
                    <button type="submit" class="btn btn-outline">
                        <?= $isFavorite ? '★ В избранном' : '☆ В избранное' ?>
                    </button>
                </form>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="action" value="mark_finished">
                    <button type="submit" class="btn btn-outline" <?= $inLibrary === 'finished' ? 'disabled' : '' ?>>
                        <?= $inLibrary === 'finished' ? '✓ Прочитано' : 'Отметить прочитанным' ?>
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <?php if ($user && $inLibrary === 'reading' && $progress > 0): ?>
            <p class="muted">Вы остановились на <b><?= $progress ?>%</b> — продолжите чтение.</p>
        <?php endif; ?>

        <?php if (!$user): ?>
            <p class="muted" style="margin-top:16px;">
                <a href="login.php">Войдите</a> или <a href="register.php">зарегистрируйтесь</a>,
                чтобы добавить книгу в избранное и сохранять прогресс.
            </p>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>