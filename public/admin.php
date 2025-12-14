
<?php
require_once 'config/database.php';
require_once 'includes/admin_check.php';
error_reporting(E_ALL);

// Обработка изменения статуса
// if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
//     $order_id = $_POST['order_id'];
//     $status_id = $_POST['status_id'];
//     $admin_notes = $_POST['admin_notes'] ?? '';
    
//     $sql = "UPDATE orders SET status_id = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?";
//     $stmt = $pdo->prepare($sql);
//     $stmt->execute([$status_id, $admin_notes, $order_id]);
    
//     // Перенаправляем, чтобы избежать повторной отправки формы
//     header('Location: ' . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
//     exit();
// }

// Поиск и фильтрация
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Пагинация
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$limit = in_array($limit, [10, 25, 50, 100]) ? $limit : 10; 
$offset = ($page - 1) * $limit;

$sql = "SELECT SQL_CALC_FOUND_ROWS 
               o.*, u.full_name, u.email, u.phone,
               os.name as status_name, os.id as status_id,
               sc.name as service_name,
               pm.name as payment_method
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

$sql .= " ORDER BY o.created_at DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Общее количество для пагинации
$total_stmt = $pdo->query("SELECT FOUND_ROWS()");
$total_orders = $total_stmt->fetchColumn();
$total_pages = ceil($total_orders / $limit);

// Получаем все статусы
$stmt = $pdo->query("SELECT * FROM order_statuses");
$statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Панель администратора';
$page_styles = ['admin.css'];
$page_scripts = ['admin.js'];
require_once 'includes/header.php';
?>

<div class="admin-container">
    <h2>Панель администратора</h2>
    
    <div class="quick-actions" style="display: none;">
        <button class="quick-action-btn" onclick="window.location.href='admin.php?date_from=<?php echo date('Y-m-d'); ?>'">
            Сегодня
        </button>
        <button class="quick-action-btn" onclick="window.location.href='admin.php?status=1'">
            Новые
        </button>
        <button class="quick-action-btn" onclick="window.location.href='admin.php?status=4'">
            Выполненные
        </button>
        <button class="quick-action-btn" onclick="window.location.href='admin.php?status=3'">
            Отмененные
        </button>
    </div>
    
    <div class="admin-filters">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="form-group">
                    <input type="text" name="search" placeholder="Поиск по ФИО, email или услуге" 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="form-group">
                    <select name="status">
                        <option value="">Все статусы</option>
                        <?php foreach($statuses as $status): ?>
                            <option value="<?php echo $status['id']; ?>" 
                                <?php echo $status_filter == $status['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($status['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="filter-row">
                <div class="form-group">
                    <label>С:</label>
                    <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                
                <div class="form-group">
                    <label>По:</label>
                    <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                
                <div class="filter-buttons">
                    <button type="submit" class="btn filter-btn">Применить фильтры</button>
                    <a href="/admin.php" class="btn btn-secondary filter-btn">Сбросить</a>
                    <button type="button" class="filter-btn export-btn" onclick="exportToCSV()">
                        <i class="fas fa-download"></i> Экспорт CSV
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Информация о записях -->
    <div class="table-info">
        <div>
            <strong>Всего заявок:</strong> <?php echo $total_orders; ?>
            <?php if($search || $status_filter || $date_from || $date_to): ?>
                <span class="filtered-count">
                    (отфильтровано: <?php echo count($orders); ?>)
                </span>
            <?php endif; ?>
        </div>
        
        <!-- Навигация по страницам -->
        <?php if($total_pages > 1): ?>
        <div class="pagination-top">
            <span class="page-label">Страница:</span>
            <div class="pagination">
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                       class="<?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="admin-orders-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Пользователь</th>
                    <th>Услуга</th>
                    <th>Дата посещения</th>
                    <th>Кол-во человек</th>
                    <th>Дети до 3 лет</th>
                    <th>Стоимость</th>
                    <th>Способ оплаты</th>
                    <th>Статус</th>
                    <th>Дата создания</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($orders)): ?>
                <tr>
                    <td colspan="11" class="no-orders">
                        Заявки не найдены
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach($orders as $order): ?>
                    <tr data-order-id="<?php echo $order['id']; ?>">
                        <td><span class="order-id-badge">#<?php echo $order['id']; ?></span></td>
                        <td class="user-info">
                            <strong><?php echo htmlspecialchars($order['full_name']); ?></strong><br>
                            <small><?php echo htmlspecialchars($order['email']); ?></small><br>
                            <small><?php echo htmlspecialchars($order['phone']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                        <td>
                            <?php echo date('d.m.Y', strtotime($order['desired_date'])); ?><br>
                            <small><?php echo date('H:i', strtotime($order['desired_time'])); ?></small>
                        </td>
                        <td><?php echo $order['people_count']; ?> чел.</td>
                        <td>
                            <?php if($order['children_under_3'] > 0): ?>
                                <span class="children-badge"><?php echo $order['children_under_3']; ?> реб.</span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo number_format($order['total_price'], 2); ?> руб.</td>
                        <td>
                            <span class="payment-method">
                                <?php echo htmlspecialchars($order['payment_method']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="status status-<?php echo $order['status_name']; ?>">
                                <?php echo htmlspecialchars($order['status_name']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-small btn-edit-status" 
                                    data-order-id="<?php echo $order['id']; ?>"
                                    data-status-id="<?php echo $order['status_id']; ?>"
                                    data-admin-notes="<?php echo htmlspecialchars($order['admin_notes'] ?? ''); ?>">
                                Изменить статус
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if($total_pages > 1): ?>
    <div class="pagination-container">
        <div class="pagination-info">
            Показано <?php echo min($limit, count($orders)); ?> из <?php echo $total_orders; ?> записей
        </div>
        
        <div class="pagination">
            <?php if($page > 1): ?>
                <a href="?page=1&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                   title="Первая страница">
                    &laquo;&laquo;
                </a>
                <a href="?page=<?php echo $page-1; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                   title="Предыдущая">
                    &laquo;
                </a>
            <?php endif; ?>
            
            <?php 
            $start = max(1, $page - 2);
            $end = min($total_pages, $start + 4);
            if($end - $start < 4) $start = max(1, $end - 4);
            
            for($i = $start; $i <= $end; $i++): ?>
                <a href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                   class="<?php echo $i == $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php if($page < $total_pages): ?>
                <a href="?page=<?php echo $page+1; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                   title="Следующая">
                    &raquo;
                </a>
                <a href="?page=<?php echo $total_pages; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>" 
                   title="Последняя страница">
                    &raquo;&raquo;
                </a>
            <?php endif; ?>
        </div>
        
        <div class="pagination-options">
            <span class="page-size-label">Записей на странице:</span>
            <select onchange="changePageSize(this.value)">
                <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                <option value="25" <?php echo $limit == 25 ? 'selected' : ''; ?>>25</option>
                <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50</option>
                <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100</option>
            </select>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Модальное окно для изменения статуса -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Изменение статуса заявки</h3>
        <form id="editForm">
            <input type="hidden" id="edit_order_id" name="order_id">
            
            <div class="form-group">
                <label for="status_id">Статус:</label>
                <select id="status_id" name="status_id" required>
                    <?php foreach($statuses as $status): ?>
                        <option value="<?php echo $status['id']; ?>">
                            <?php echo htmlspecialchars($status['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="admin_notes">Примечание:</label>
                <textarea id="admin_notes" name="admin_notes" rows="3"></textarea>
            </div>
            
            <button type="submit" class="btn">Сохранить изменения</button>
        </form>
    </div>
</div>

<script>
function changePageSize(size) {
    const url = new URL(window.location.href);
    url.searchParams.set('limit', size);
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

function exportToCSV() {
    showLoading(true, 'Подготовка экспорта...');

    const params = new URLSearchParams(window.location.search);
    
    fetch(`/api/export_orders.php?${params.toString()}`)
        .then(response => response.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `orders_${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            showLoading(false);
            showNotification('Экспорт завершен', 'success');
        })
        .catch(error => {
            console.error('Error:', error);
            showLoading(false);
            showNotification('Ошибка при экспорте', 'error');
        });
}
</script>

<?php require_once 'includes/footer.php'; ?>
