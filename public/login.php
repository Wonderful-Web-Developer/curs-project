<?php
require_once 'config/database.php';

$errors = [];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if(empty($login) || empty($password)) {
        $errors[] = 'Заполните все поля';
    }
    
    if(empty($errors)) {
        // Проверка администратора
        if($login === 'admin@akvapark.ru' && $password === 'AkvaApelsin') {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$login]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['is_admin'] = $user['is_admin'];
                
                header('Location: /admin.php');
                exit();
            }
        }
        
        // Проверка обычного пользователя
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['is_admin'] = $user['is_admin'];
            
            header('Location: /user_cabinet.php');
            exit();
        } else {
            $errors[] = 'Неверный логин или пароль';
        }
    }
}

$page_title = 'Вход';
$page_styles = ['auth.css'];
$page_scripts = ['auth.js'];
require_once 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-form">
        <h2>Вход в систему</h2>
        
        <?php if(!empty($errors)): ?>
            <div class="alert error">
                <?php foreach($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="loginForm">
            <div class="form-group">
                <label for="login">Email:</label>
                <input type="text" id="login" name="login" required 
                       value="<?php echo htmlspecialchars($_POST['login'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Пароль:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Войти</button>
            <p class="auth-link">
                Нет аккаунта? <a href="/register.php">Зарегистрироваться</a>
            </p>
            <p class="auth-link">
                <a href="/forgot_password.php">Забыли пароль?</a>
            </p>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>