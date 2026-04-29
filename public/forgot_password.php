<?php
require_once 'config/database.php';

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id, email FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);

            $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$email, $token, $expires_at]);

            $reset_link = "https://{$_SERVER['HTTP_HOST']}/reset_password.php?token=" . urlencode($token);

            $subject = "Восстановление пароля – Аквапарк «Апельсин»";
            $message = "Здравствуйте!\n\n"
                . "Для сброса пароля перейдите по ссылке:\n"
                . $reset_link . "\n\n"
                . "Ссылка действительна 1 час.\n\n"
                . "Если вы не запрашивали восстановление, просто проигнорируйте это письмо.";
            $headers = "From: noreply@akvapark-apelsin.ru\r\n";

            $mail_sent = @mail($email, $subject, $message, $headers);
            
            if ($mail_sent) {
                $success_message = 'Инструкции по восстановлению пароля отправлены на ваш email.';
            } else {
                $success_message = "Инструкции отправлены на email. Отладочная ссылка: <a href=\"{$reset_link}\">{$reset_link}</a>";
            }
        } else {
            $success_message = 'Если указанный email зарегистрирован, инструкции по восстановлению отправлены.';
        }
    }
}

$page_title = 'Восстановление пароля';
$page_styles = ['auth.css'];
require_once 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-form">
        <h2>Восстановление пароля</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert success">
                <p><?php echo $success_message; ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" id="forgotForm">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                       placeholder="user@example.com">
            </div>
            <button type="submit" class="btn">Восстановить пароль</button>
            <p class="auth-link">
                <a href="/login.php">Вернуться ко входу</a>
            </p>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>