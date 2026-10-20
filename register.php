<?php
require_once __DIR__ . '/config/db.php';

$pageTitle = 'Регистрация — Читальня';
$errors = [];
$success = '';
$old = ['username' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    $old['username'] = $username;
    $old['email']    = $email;

    if ($username === '') {
        $errors[] = 'Введите имя пользователя.';
    } elseif (mb_strlen($username) < 3 || mb_strlen($username) > 50) {
        $errors[] = 'Имя пользователя должно быть от 3 до 50 символов.';
    } elseif (!preg_match('/^[a-zA-Zа-яА-Я0-9_ ]+$/u', $username)) {
        $errors[] = 'Имя может содержать только буквы, цифры и «_».';
    }

    if ($email === '') {
        $errors[] = 'Введите email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Некорректный email.';
    }

    if ($password === '') {
        $errors[] = 'Введите пароль.';
    } elseif (mb_strlen($password) < 6) {
        $errors[] = 'Пароль должен содержать минимум 6 символов.';
    }

    if ($password !== $confirm) {
        $errors[] = 'Пароли не совпадают.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            $errors[] = 'Пользователь с таким email или именем уже существует.';
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
        $stmt->execute([$username, $email, $hash]);
        $success = 'Регистрация прошла успешно! Теперь вы можете войти в личный кабинет.';
        $old = ['username' => '', 'email' => ''];
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="form-card">
    <h1>Регистрация</h1>
    <p class="subtitle">Создайте аккаунт, чтобы читать книги онлайн</p>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $e): ?>
                <div>• <?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" action="register.php" novalidate>
        <div class="form-group">
            <label for="username">Имя пользователя</label>
            <input type="text" id="username" name="username"
                   value="<?= htmlspecialchars($old['username']) ?>"
                   placeholder="Например, ivan_reader" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($old['email']) ?>"
                   placeholder="you@example.com" required>
        </div>

        <div class="form-group">
            <label for="password">Пароль</label>
            <input type="password" id="password" name="password"
                   placeholder="Минимум 6 символов" required>
        </div>

        <div class="form-group">
            <label for="confirm_password">Повторите пароль</label>
            <input type="password" id="confirm_password" name="confirm_password"
                   placeholder="Ещё раз" required>
        </div>

        <button type="submit" class="btn">Зарегистрироваться</button>
    </form>

    <p class="form-footer">Уже есть аккаунт? <a href="login.php">Войти</a></p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>