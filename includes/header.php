<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$__user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Читальня' ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a href="index.php" class="logo">📚 Читальня</a>
        <nav>
            <a href="books.php">Каталог</a>
            <a href="index.php">О библиотеке</a>
            <?php if ($__user): ?>
                <a href="profile.php">Мои книги</a>
                <a href="logout.php">Выйти (<?= htmlspecialchars($__user['username']) ?>)</a>
            <?php else: ?>
                <a href="register.php">Регистрация</a>
                <a href="login.php">Войти</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">