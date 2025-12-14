<?php
require_once 'database.php';

// Добавляем тестовые данные для разработки

// 1. Добавляем несколько тестовых пользователей
$users = [
    ['user1@test.ru', 'User1234', 'Петров Петр Петрович', '+79161111111', '1985-05-15', 0],
    ['user2@test.ru', 'User1234', 'Сидорова Анна Ивановна', '+79162222222', '1992-08-22', 0],
    ['user3@test.ru', 'User1234', 'Козлов Алексей Викторович', '+79163333333', '1978-11-30', 0]
];

foreach($users as $user) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$user[0]]);
    if($stmt->fetchColumn() == 0) {
        $password_hash = password_hash($user[1], PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (email, password_hash, full_name, phone, birth_date, is_admin) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user[0], $password_hash, $user[2], $user[3], $user[4], $user[5]]);
    }
}

// 2. Добавляем тестовые заявки
$orders = [
    [2, 13, 2, 1, '2025-12-10', '15:00:00', 1650.00, 1, 2], // В обработке
    [2, 15, 3, 0, '2025-12-11', '16:30:00', 2850.00, 2, 4], // Выполнена
    [3, 9, 1, 0, '2025-12-12', '11:00:00', 710.00, 3, 1],   // Новая
    [4, 5, 4, 2, '2025-12-13', '14:00:00', 1960.00, 1, 3],  // Отменена
];

foreach($orders as $order) {
    // Проверяем, существует ли уже такая заявка
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND desired_date = ? AND desired_time = ?");
    $stmt->execute([$order[0], $order[4], $order[5]]);
    if($stmt->fetchColumn() == 0) {
        $sql = "INSERT INTO orders (user_id, tariff_id, people_count, children_under_3, 
                   desired_date, desired_time, total_price, payment_method_id, status_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($order);
    }
}

echo "Тестовые данные успешно добавлены!";
?>