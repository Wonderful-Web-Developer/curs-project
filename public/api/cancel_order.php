<?php
require_once '../config/database.php';
session_start();

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$order_id = $data['order_id'] ?? null;

if(!$order_id) {
    echo json_encode(['success' => false, 'error' => 'Не указан ID заявки']);
    exit();
}

// Проверяем, что заявка принадлежит пользователю
$stmt = $pdo->prepare("SELECT * FROM orders o 
                       JOIN order_statuses os ON o.status_id = os.id 
                       WHERE o.id = ? AND o.user_id = ? AND os.code = 'new'");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if(!$order) {
    echo json_encode(['success' => false, 'error' => 'Заявка не найдена или нельзя отменить']);
    exit();
}

// Меняем статус на "Отменена" (id = 3)
$sql = "UPDATE orders SET status_id = 3, updated_at = NOW() WHERE id = ?";
$stmt = $pdo->prepare($sql);

if($stmt->execute([$order_id])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Ошибка при отмене заявки']);
}
?>