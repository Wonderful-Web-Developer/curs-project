<?php
session_start();

$host = 'MySQL-8.0';
$dbname = 'akvapark_apelsin';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Вместо вывода HTML ошибки, можно записать в лог
    error_log("Ошибка подключения к базе данных: " . $e->getMessage());
    die(json_encode(['error' => 'Ошибка базы данных']));
}

// Функция для инициализации базы данных
function initializeDatabase($pdo) {
    // Заполняем статусы заявок, если таблица пуста
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_statuses");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $statuses = [
            ['Новая', 'new', 1],
            ['В обработке', 'processing', 0],
            ['Отменена', 'canceled', 0],
            ['Выполнена', 'completed', 0]
        ];
        
        $sql = "INSERT INTO order_statuses (name, code, is_default) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        foreach ($statuses as $status) {
            $stmt->execute($status);
        }
    }
    
    // Создаем администратора, если его нет
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = 'admin@akvapark.ru'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $password_hash = password_hash('AkvaApelsin', PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (email, password_hash, full_name, phone, is_admin) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['admin@akvapark.ru', $password_hash, 'Администратор', '+79999999999', 1]);
    }
    
    // Проверяем тестового пользователя
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = 'test@user.ru'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $password_hash = password_hash('Test1234', PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (email, password_hash, full_name, phone, birth_date, is_admin) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'test@user.ru', 
            $password_hash, 
            'Иванов Иван Иванович', 
            '+79161234567', 
            '1990-01-01', 
            0
        ]);
    }
}

// Инициализируем базу данных
initializeDatabase($pdo);
?>