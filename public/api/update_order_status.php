<?php
require_once '../config/database.php';
session_start();

header('Content-Type: application/json');

// Проверяем, что пользователь администратор
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'error' => 'Доступ запрещен']);
    exit();
}

// Получаем данные из запроса
$data = json_decode(file_get_contents('php://input'), true);
$order_id = $data['order_id'] ?? null;
$status_id = $data['status_id'] ?? null;
$admin_notes = $data['admin_notes'] ?? '';

if (!$order_id || !$status_id) {
    echo json_encode(['success' => false, 'error' => 'Не указаны все параметры']);
    exit();
}

// Проверяем существование статуса
$stmt = $pdo->prepare("SELECT id, name FROM order_statuses WHERE id = ?");
$stmt->execute([$status_id]);
$status = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$status) {
    echo json_encode(['success' => false, 'error' => 'Указан неверный статус']);
    exit();
}

// Проверяем существование заявки
$stmt = $pdo->prepare("SELECT id FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Заявка не найдена']);
    exit();
}

// Обновляем статус заявки
$sql = "UPDATE orders SET status_id = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?";
$stmt = $pdo->prepare($sql);

if ($stmt->execute([$status_id, $admin_notes, $order_id])) {
    echo json_encode([
        'success' => true,
        'message' => 'Статус заявки обновлен',
        'order_id' => $order_id,
        'status_id' => $status_id,
        'status_name' => $status['name']
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Ошибка при обновлении статуса']);
}
?>