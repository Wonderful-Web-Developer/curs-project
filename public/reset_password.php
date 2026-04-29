<?php
require_once 'config/database.php';

$errors = [];
$success = false;
$token = $_GET['token'] ?? '';

if (empty($token)) {
    die('Неверная ссылка');
}

$stmt = $pdo->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
$stmt->execute([$token]);
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset || strtotime($reset['expires_at']) < time()) {
    die('Ссылка устарела или недействительна. Запросите восстановление пароля снова.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 8) {
        $errors[] = 'Пароль должен содержать минимум 8 символов';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Пароли не совпадают';
    }

    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        $stmt->execute([$password_hash, $reset['email']]);

        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);

        $success = true;
    }
}

$page_title = 'Установка нового пароля';
$page_styles = ['auth.css'];
require_once 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-form">
        <h2>Новый пароль</h2>

        <?php if ($success): ?>
            <div class="alert success">
                <p>Пароль успешно изменён! <a href="/login.php">Войдите в систему</a>.</p>
            </div>
        <?php else: ?>
            <?php if (!empty($errors)): ?>
                <div class="alert error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="password">Новый пароль (минимум 8 символов):</label>
                    <input type="password" id="password" name="password" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Подтвердите пароль:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn">Сохранить пароль</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>