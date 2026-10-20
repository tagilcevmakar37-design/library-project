<?php
require_once __DIR__ . '/config/db.php';
session_start();

$pageTitle = 'Каталог — Читальня';

$q = trim($_GET['q'] ?? '');
$genre = trim($_GET['genre'] ?? '');

$sql = 'SELECT id, title, author, genre, year, description FROM books WHERE 1=1';
$params = [];

if ($q !== '') {
    $sql .= ' AND (title LIKE ? OR author LIKE ?)';
    $params[] = "%$q%"; $params[] = "%$q%";
}
if ($genre !== '') {
    $sql .= ' AND genre = ?';
    $params[] = $genre;
}
$sql .= ' ORDER BY title ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

$genres = $pdo->query('SELECT DISTINCT genre FROM books WHERE genre IS NOT NULL ORDER BY genre')->fetchAll(PDO::FETCH_COLUMN);

require __DIR__ . '/includes/header.php';
?>
<h1 style="margin-bottom:20px;">Каталог книг</h1>

<form method="get" action="books.php" class="filter-bar">
    <input type="text" name="q" placeholder="Поиск по названию или автору"
           value="<?= htmlspecialchars($q) ?>">
    <select name="genre">
        <option value="">Все жанры</option>
        <?php foreach ($genres as $g): ?>
            <option value="<?= htmlspecialchars($g) ?>" <?= $genre === $g ? 'selected' : '' ?>>
                <?= htmlspecialchars($g) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn">Найти</button>
    <?php if ($q || $genre): ?>
        <a href="books.php" class="btn-reset">Сбросить</a>
    <?php endif; ?>
</form>

<p class="muted">Найдено книг: <b><?= count($books) ?></b></p>

<?php if (!$books): ?>
    <div class="alert alert-error">Ничего не найдено. Попробуйте изменить запрос.</div>
<?php else: ?>
    <div class="books-grid">
        <?php foreach ($books as $b): ?>
            <a href="book.php?id=<?= $b['id'] ?>" class="book-card">
                <div class="book-cover" style="background: <?= cover_gradient($b['id']) ?>">
                    <span><?= htmlspecialchars(mb_substr($b['title'], 0, 1)) ?></span>
                </div>
                <div class="book-info">
                    <h3><?= htmlspecialchars($b['title']) ?></h3>
                    <p class="author"><?= htmlspecialchars($b['author']) ?></p>
                    <p class="meta"><?= htmlspecialchars($b['genre']) ?>, <?= (int)$b['year'] ?></p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
require __DIR__ . '/includes/footer.php';

function cover_gradient($id) {
    $palette = ['#8b6f47,#5c4530','#7a4f3f,#4a2e25','#6b7a4f,#3f4a2a',
                '#4f6b7a,#2a3f4a','#7a4f6b,#4a2a3f','#7a6b4f,#4a3f2a'];
    return 'linear-gradient(135deg, ' . $palette[$id % count($palette)] . ')';
}
?>