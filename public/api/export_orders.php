
<?php
require_once '../config/database.php';
session_start();

// Проверяем, что пользователь администратор
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('HTTP/1.1 403 Forbidden');
    exit('Доступ запрещен');
}

// Получаем параметры фильтрации
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Получаем все заявки с фильтрами
$sql = "SELECT 
               o.id,
               u.full_name,
               u.email,
               u.phone,
               sc.name as service_name,
               o.desired_date,
               o.desired_time,
               o.people_count,
               o.children_under_3,
               o.total_price,
               pm.name as payment_method,
               os.name as status_name,
               o.admin_notes,
               o.created_at
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN order_statuses os ON o.status_id = os.id
        JOIN tariffs t ON o.tariff_id = t.id
        JOIN service_categories sc ON t.category_id = sc.id
        JOIN payment_methods pm ON o.payment_method_id = pm.id
        WHERE 1=1";

$params = [];

if($search) {
    $sql .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR sc.name LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if($status_filter) {
    $sql .= " AND o.status_id = ?";
    $params[] = $status_filter;
}

if($date_from) {
    $sql .= " AND o.desired_date >= ?";
    $params[] = $date_from;
}

if($date_to) {
    $sql .= " AND o.desired_date <= ?";
    $params[] = $date_to;
}

$sql .= " ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Устанавливаем заголовки для CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=orders_' . date('Y-m-d') . '.csv');

// Создаем поток вывода
$output = fopen('php://output', 'w');

// Заголовки CSV
fputcsv($output, [
    'ID',
    'ФИО',
    'Email',
    'Телефон',
    'Услуга',
    'Дата посещения',
    'Время посещения',
    'Количество человек',
    'Дети до 3 лет',
    'Стоимость',
    'Способ оплаты',
    'Статус',
    'Примечание администратора',
    'Дата создания'
]);

// Данные
foreach ($orders as $order) {
    fputcsv($output, [
        $order['id'],
        $order['full_name'],
        $order['email'],
        $order['phone'],
        $order['service_name'],
        $order['desired_date'],
        $order['desired_time'],
        $order['people_count'],
        $order['children_under_3'],
        $order['total_price'],
        $order['payment_method'],
        $order['status_name'],
        $order['admin_notes'],
        $order['created_at']
    ]);
}

fclose($output);
