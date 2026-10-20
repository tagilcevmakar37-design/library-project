<?php
require_once __DIR__ . '/config/db.php';
session_start();

if (isset($_SESSION['user'])) { header('Location: profile.php'); exit; }

$pageTitle = 'Вход — Читальня';
$errors = []; $old = ['login' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $old['login'] = $login;

    if ($login === '') $errors[] = 'Введите email или имя пользователя.';
    if ($password === '') $errors[] = 'Введите пароль.';

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1');
        $stmt->execute([$login, $login]);
        $u = $stmt->fetch();

        if ($u && password_verify($password, $u['password_hash'])) {
            $_SESSION['user'] = ['id' => $u['id'], 'username' => $u['username'], 'email' => $u['email']];
            header('Location: profile.php'); exit;
        } else {
            $errors[] = 'Неверный логин или пароль.';
        }
    }
}
require __DIR__ . '/includes/header.php';
?>
<div class="form-card">
    <h1>Вход</h1>
    <p class="subtitle">Войдите, чтобы продолжить чтение</p>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $e): ?><div>• <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <div class="form-group">
            <label>Email или имя пользователя</label>
            <input type="text" name="login" value="<?= htmlspecialchars($old['login']) ?>" required>
        </div>
        <div class="form-group">
            <label>Пароль</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn">Войти</button>
    </form>
    <p class="form-footer">Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>