<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth_check.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Недопустимый метод запроса';
    echo json_encode($response);
    exit;
}

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Необходима авторизация';
    echo json_encode($response);
    exit;
}

// Получаем данные из формы
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$birth_date = $_POST['birth_date'] ?? null;
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';

// Валидация обязательных полей
if (empty($full_name) || empty($email) || empty($phone) || empty($current_password)) {
    $response['message'] = 'Все обязательные поля должны быть заполнены';
    echo json_encode($response);
    exit;
}

// Валидация email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Некорректный email адрес';
    echo json_encode($response);
    exit;
}

// Валидация телефона (упрощенная)
$phone_clean = preg_replace('/[^0-9]/', '', $phone);
if (strlen($phone_clean) < 10) {
    $response['message'] = 'Некорректный номер телефона';
    echo json_encode($response);
    exit;
}

try {
    // Получаем текущие данные пользователя
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $response['message'] = 'Пользователь не найден';
        echo json_encode($response);
        exit;
    }
    
    // Проверяем текущий пароль
    if (!password_verify($current_password, $user['password_hash'])) {
        $response['message'] = 'Неверный текущий пароль';
        echo json_encode($response);
        exit;
    }
    
    // Проверяем, не занят ли email другим пользователем
    if ($email !== $user['email']) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $response['message'] = 'Этот email уже используется другим пользователем';
            echo json_encode($response);
            exit;
        }
    }
    
    // Подготавливаем данные для обновления
    $update_fields = [
        'full_name' => $full_name,
        'email' => $email,
        'phone' => $phone,
        'birth_date' => $birth_date ?: null
    ];
    
    // Если указан новый пароль
    if (!empty($new_password)) {
        if (strlen($new_password) < 6) {
            $response['message'] = 'Новый пароль должен содержать минимум 6 символов';
            echo json_encode($response);
            exit;
        }
        $update_fields['password_hash'] = password_hash($new_password, PASSWORD_DEFAULT);
    }
    
    // Формируем SQL запрос
    $set_clause = [];
    $params = [];
    foreach ($update_fields as $field => $value) {
        $set_clause[] = "$field = ?";
        $params[] = $value;
    }
    $params[] = $_SESSION['user_id']; // для WHERE условия
    
    $sql = "UPDATE users SET " . implode(', ', $set_clause) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute($params)) {
        // Обновляем данные в сессии
        $_SESSION['full_name'] = $full_name;
        $_SESSION['email'] = $email;
        
        $response['success'] = true;
        $response['message'] = 'Профиль успешно обновлен';
    } else {
        $response['message'] = 'Ошибка при обновлении профиля';
    }
    
} catch (PDOException $e) {
    // Ловим ошибку дублирования email (уникальное поле)
    if ($e->getCode() == 23000) {
        $response['message'] = 'Этот email уже используется другим пользователем';
    } else {
        $response['message'] = 'Ошибка базы данных: ' . $e->getMessage();
    }
}

echo json_encode($response);