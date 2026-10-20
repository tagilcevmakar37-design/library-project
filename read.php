<?php
require_once __DIR__ . '/config/db.php';
session_start();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: books.php'); exit; }

$stmt = $pdo->prepare('SELECT id, title, author, content FROM books WHERE id = ?');
$stmt->execute([$id]);
$book = $stmt->fetch();
if (!$book) { http_response_code(404); die('Книга не найдена'); }

$pageTitle = $book['title'] . ' — чтение';
$user = $_SESSION['user'] ?? null;

if ($user) {
    $stmt = $pdo->prepare('INSERT INTO user_library (user_id, book_id, status) VALUES (?, ?, "reading")
        ON DUPLICATE KEY UPDATE added_at = added_at');
    $stmt->execute([$user['id'], $id]);
}

require __DIR__ . '/includes/header.php';
?>
<div class="reader">
    <div class="reader-head">
        <a href="book.php?id=<?= $book['id'] ?>" class="btn btn-outline">← К описанию</a>
        <div class="reader-title">
            <b><?= htmlspecialchars($book['title']) ?></b>
            <span><?= htmlspecialchars($book['author']) ?></span>
        </div>
        <?php if ($user): ?>
            <span class="reader-progress" id="progressLabel">0%</span>
        <?php endif; ?>
    </div>

    <article class="reader-text" id="readerText">
        <?php foreach (preg_split('/\n\s*\n/', trim($book['content'])) as $p): ?>
            <p><?= nl2br(htmlspecialchars(trim($p))) ?></p>
        <?php endforeach; ?>
    </article>
</div>

<?php if ($user): ?>
<script>
(function () {
    const bookId = <?= (int)$book['id'] ?>;
    const label = document.getElementById('progressLabel');
    let lastSent = 0;

    function calcProgress() {
        const h = document.documentElement;
        const total = h.scrollHeight - h.clientHeight;
        if (total <= 0) return 100;
        return Math.min(100, Math.round(h.scrollTop / total * 100));
    }

    function save(force) {
        const p = calcProgress();
        label.textContent = p + '%';
        const now = Date.now();
        if (!force && now - lastSent < 3000) return;
        lastSent = now;
        const fd = new FormData();
        fd.append('book_id', bookId);
        fd.append('progress', p);
        fetch('save_progress.php', { method: 'POST', body: fd });
    }

    window.addEventListener('scroll', () => save(false), { passive: true });
    window.addEventListener('beforeunload', () => {
        const fd = new FormData();
        fd.append('book_id', bookId);
        fd.append('progress', calcProgress());
        navigator.sendBeacon('save_progress.php', fd);
    });
    save(true);
})();
</script>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>