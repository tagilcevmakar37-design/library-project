<?php
require_once __DIR__ . '/config/db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) { echo json_encode(['ok' => false]); exit; }

$bookId = (int)($_POST['book_id'] ?? 0);
$progress = max(0, min(100, (int)($_POST['progress'] ?? 0)));
if (!$bookId) { echo json_encode(['ok' => false]); exit; }

$stmt = $pdo->prepare("
    INSERT INTO user_library (user_id, book_id, status, progress)
    VALUES (?, ?, 'reading', ?)
    ON DUPLICATE KEY UPDATE
        progress = GREATEST(progress, VALUES(progress)),
        status = IF(VALUES(progress) >= 95, 'finished', status)
");
$stmt->execute([$_SESSION['user']['id'], $bookId, $progress]);
echo json_encode(['ok' => true, 'progress' => $progress]);