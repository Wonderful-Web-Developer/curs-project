<?php
require_once 'config/database.php';

$errors = [];
$success = false;

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $birth_date = $_POST['birth_date'] ?? '';
    
    // Валидация
    if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email';
    }
    
    if(strlen($password) < 8) {
        $errors[] = 'Пароль должен содержать минимум 8 символов';
    }
    
    if($password !== $confirm_password) {
        $errors[] = 'Пароли не совпадают';
    }
    
    if(empty($full_name) || !preg_match('/^[а-яА-ЯёЁ\s]+$/u', $full_name)) {
        $errors[] = 'Введите корректное ФИО (только кириллица и пробелы)';
    }
    
    // Обновленная проверка телефона (разрешаем и 8, и +7)
    if(empty($phone) || !preg_match('/^(8|\+7)\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $phone)) {
        $errors[] = 'Введите телефон в формате 8(XXX)XXX-XX-XX или +7(XXX)XXX-XX-XX';
    }
    
    if(empty($birth_date)) {
        $errors[] = 'Введите дату рождения';
    }
    
    // Проверка уникальности email
    if(empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if($stmt->fetch()) {
            $errors[] = 'Пользователь с таким email уже существует';
        }
    }
    
    // Регистрация
    if(empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (email, password_hash, full_name, phone, birth_date) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if($stmt->execute([$email, $password_hash, $full_name, $phone, $birth_date])) {
            $success = true;
        } else {
            $errors[] = 'Ошибка при регистрации';
        }
    }
}

$page_title = 'Регистрация';
$page_styles = ['auth.css'];
$page_scripts = ['auth.js'];
require_once 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-form">
        <h2>Регистрация</h2>
        
        <?php if($success): ?>
            <div class="alert success">
                Регистрация успешна! <a href="/login.php">Войдите в систему</a>
            </div>
        <?php elseif(!empty($errors)): ?>
            <div class="alert error">
                <?php foreach($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if(!$success): ?>
        <form method="POST" id="registerForm">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required placeholder="user@mail.com"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                <span class="error-message" id="email-error"></span>
            </div>
            
            <div class="form-group">
                <label for="password">Пароль (мин. 8 символов):</label>
                <input type="password" id="password" name="password" required minlength="8">
                <span class="error-message" id="password-error"></span>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Подтвердите пароль:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <span class="error-message" id="confirm-password-error"></span>
            </div>
            
            <div class="form-group">
                <label for="full_name">ФИО:</label>
                <input type="text" id="full_name" name="full_name" required 
                       pattern="[А-Яа-яЁё\s]+"
                       placeholder="Иванов Иван Иванович"
                       value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                <span class="error-message" id="full-name-error"></span>
            </div>
            
            <div class="form-group">
                <label for="phone">Телефон:</label>
                <input type="tel" id="phone" name="phone" required 
                       pattern="(8|\+7)\(\d{3}\)\d{3}-\d{2}-\d{2}"
                       placeholder="8(XXX)XXX-XX-XX или +7(XXX)XXX-XX-XX"
                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                <span class="error-message" id="phone-error"></span>
            </div>
            
            <div class="form-group">
                <label for="birth_date">Дата рождения:</label>
                <input type="date" id="birth_date" name="birth_date" required
                       value="<?php echo htmlspecialchars($_POST['birth_date'] ?? ''); ?>">
                <span class="error-message" id="birth-date-error"></span>
            </div>
            
            <button type="submit" class="btn">Зарегистрироваться</button>
            <p class="auth-link">
                Уже зарегистрированы? <a href="/login.php">Войти</a>
            </p>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>