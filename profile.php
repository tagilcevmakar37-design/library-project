<?php
require_once __DIR__ . '/config/db.php';
session_start();

if (!isset($_SESSION['user'])) { header('Location: login.php'); exit; }
$user = $_SESSION['user'];
$pageTitle = 'Мои книги — Читальня';

$stmt = $pdo->prepare('
    SELECT b.id, b.title, b.author, b.genre, b.year,
           ul.status, ul.progress, ul.is_favorite
    FROM user_library ul
    JOIN books b ON b.id = ul.book_id
    WHERE ul.user_id = ?
    ORDER BY ul.added_at DESC
');
$stmt->execute([$user['id']]);
$all = $stmt->fetchAll();

$favorites = array_filter($all, fn($r) => (bool)$r['is_favorite']);
$reading   = array_filter($all, fn($r) => $r['status'] === 'reading' && !$r['is_favorite']);
$finished  = array_filter($all, fn($r) => $r['status'] === 'finished');

function cover_gradient($id) {
    $p = ['#8b6f47,#5c4530','#7a4f3f,#4a2e25','#6b7a4f,#3f4a2a','#4f6b7a,#2a3f4a','#7a4f6b,#4a2a3f','#7a6b4f,#4a3f2a'];
    return 'linear-gradient(135deg, ' . $p[$id % count($p)] . ')';
}
function render_grid($items, $showProgress = false) {
    if (!$items) { echo '<p class="muted">Пока пусто.</p>'; return; }
    echo '<div class="books-grid">';
    foreach ($items as $b) {
        echo '<a href="book.php?id=' . $b['id'] . '" class="book-card">';
        echo '<div class="book-cover" style="background:' . cover_gradient($b['id']) . '">';
        echo '<span>' . htmlspecialchars(mb_substr($b['title'], 0, 1)) . '</span></div>';
        echo '<div class="book-info"><h3>' . htmlspecialchars($b['title']) . '</h3>';
        echo '<p class="author">' . htmlspecialchars($b['author']) . '</p>';
        if ($showProgress) echo '<p class="meta">Прогресс: ' . (int)$b['progress'] . '%</p>';
        echo '</div></a>';
    }
    echo '</div>';
}
require __DIR__ . '/includes/header.php';
?>
<h1>Привет, <?= htmlspecialchars($user['username']) ?>!</h1>
<p class="muted" style="margin-bottom:30px;">Это ваш личный кабинет. Здесь собраны все ваши книги.</p>

<h2 class="section-title">★ Избранное (<?= count($favorites) ?>)</h2>
<?php render_grid($favorites); ?>

<h2 class="section-title">📖 Читаю сейчас (<?= count($reading) ?>)</h2>
<?php render_grid($reading, true); ?>

<h2 class="section-title">✓ Прочитано (<?= count($finished) ?>)</h2>
<?php render_grid($finished); ?>

<?php require __DIR__ . '/includes/footer.php'; ?>