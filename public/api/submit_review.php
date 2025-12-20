<?php
// session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Неавторизованный доступ']);
    exit;
}

// Получаем данные
$order_id = $_POST['order_id'] ?? null;
$rating = (int)($_POST['rating'] ?? 0);
$comment = $_POST['comment'] ?? '';

// Валидация
if (!$order_id || !$rating) {
    echo json_encode(['success' => false, 'error' => 'Отсутствуют обязательные поля']);
    exit;
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'error' => 'Некорректная оценка']);
    exit;
}

try {
    // Проверяем, что заявка существует и принадлежит пользователю
    $stmt = $pdo->prepare("
        SELECT o.*, os.code as status_code 
        FROM orders o
        JOIN order_statuses os ON o.status_id = os.id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode(['success' => false, 'error' => 'Заявка не найдена']);
        exit;
    }
    
    // Проверяем, что заявка выполнена
    if ($order['status_code'] !== 'completed') {
        echo json_encode(['success' => false, 'error' => 'Отзыв можно оставить только для выполненной заявки']);
        exit;
    }
    
    // Проверяем, не оставлял ли уже пользователь отзыв на эту заявку
    $stmt = $pdo->prepare("SELECT id FROM reviews WHERE order_id = ?");
    $stmt->execute([$order_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Вы уже оставляли отзыв на эту заявку']);
        exit;
    }
    
    // Добавляем отзыв
    $stmt = $pdo->prepare("
        INSERT INTO reviews (order_id, rating, comment, created_at) 
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$order_id, $rating, $comment]);
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    error_log("Ошибка при сохранении отзыва: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Ошибка сервера']);
}
?>