
<?php
require_once 'config/database.php';
require_once 'includes/admin_check.php';
error_reporting(E_ALL);

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

// Рассчитываем статистику
// Общая выручка (только выполненные заказы)
$stmt = $pdo->query("SELECT SUM(total_price) as total_revenue FROM orders WHERE status_id = 4");
$total_revenue = $stmt->fetchColumn() ?: 0;

// Статистика по статусам
$status_counts = ['Новая' => 0, 'В обработке' => 0, 'Выполнена' => 0, 'Отменена' => 0];
$stmt = $pdo->query("SELECT os.name, COUNT(*) as count FROM orders o JOIN order_statuses os ON o.status_id = os.id GROUP BY os.name");
$status_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($status_stats as $stat) {
    if (isset($status_counts[$stat['name']])) {
        $status_counts[$stat['name']] = $stat['count'];
    }
}

$page_title = 'Панель администратора';
$page_styles = ['admin.css', 'orders.css'];
$page_scripts = ['admin.js'];
require_once 'includes/header.php';
?>

<div class="admin-container">
    <div class="orders-page-header">
        <div class="breadcrumb">
            <a href="/index.php"><i class="fas fa-home"></i> Главная</a>
            <i class="fas fa-chevron-right"></i>
            <span>Панель администратора</span>
        </div>
        
        <div class="page-title-section">
            <h1><i class="fas fa-cogs"></i> Панель администратора</h1>
            <div class="page-actions">
                <button type="button" class="btn btn-primary export-btn" onclick="exportToCSV()">
                    <i class="fas fa-download"></i> Экспорт CSV
                </button>
            </div>
        </div>
        
        <div class="orders-summary">
            <div class="summary-card">
                <i class="fas fa-receipt"></i>
                <div>
                    <h3><?php echo $total_orders; ?></h3>
                    <p>Всего заявок</p>
                </div>
            </div>
            
            <div class="summary-card">
                <i class="fas fa-clock"></i>
                <div>
                    <h3><?php echo $status_counts['Новая']; ?></h3>
                    <p>Новых</p>
                </div>
            </div>
            
            <div class="summary-card">
                <i class="fas fa-check-circle"></i>
                <div>
                    <h3><?php echo $status_counts['Выполнена']; ?></h3>
                    <p>Выполнено</p>
                </div>
            </div>
            
            <div class="summary-card">
                <i class="fas fa-times-circle"></i>
                <div>
                    <h3><?php echo $status_counts['Отменена']; ?></h3>
                    <p>Отменено</p>
                </div>
            </div>
            
            <!-- Новая карточка: Общая выручка -->
            <div class="summary-card">
                <i class="fas fa-money-bill-wave"></i>
                <div>
                    <h3><?php echo number_format($total_revenue, 0); ?> ₽</h3>
                    <p>Общая выручка</p>
                </div>
            </div>
        </div>
    </div>

    <div class="orders-content">
        <!-- Фильтры и поиск -->
        <div class="orders-filters">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="search"><i class="fas fa-search"></i> Поиск:</label>
                    <div class="search-input-wrapper">
                        <input type="text" id="search" name="search" placeholder="Поиск по ФИО, email или услуге" 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>
                
                <div class="filter-group">
                    <label for="status"><i class="fas fa-filter"></i> Статус:</label>
                    <select id="status" name="status" class="filter-select">
                        <option value="">Все статусы</option>
                        <?php foreach($statuses as $status): ?>
                            <option value="<?php echo $status['id']; ?>" 
                                <?php echo $status_filter == $status['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($status['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="date_from"><i class="fas fa-calendar-alt"></i> С:</label>
                    <input type="date" id="date_from" name="date_from" class="filter-select" 
                           value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                
                <div class="filter-group">
                    <label for="date_to"><i class="fas fa-calendar-alt"></i> По:</label>
                    <input type="date" id="date_to" name="date_to" class="filter-select" 
                           value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Применить
                    </button>
                    <a href="/admin.php" class="btn btn-outline">
                        <i class="fas fa-redo"></i> Сбросить
                    </a>
                </div>
            </form>
        </div>

        <?php if(empty($orders)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h3>Заявок не найдено</h3>
                <p>Попробуйте изменить параметры фильтрации</p>
            </div>
        <?php else: ?>
            <!-- Таблица заявок -->
            <div class="orders-table-container" id="ordersTableContainer">
                <div class="table-responsive">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>
                                    <div class="table-header">
                                        <span>ID</span>
                                        <button class="sort-btn" data-sort="id">
                                            <i class="fas fa-sort"></i>
                                        </button>
                                    </div>
                                </th>
                                <th>
                                    <div class="table-header">
                                        <span>Пользователь</span>
                                        <button class="sort-btn" data-sort="user">
                                            <i class="fas fa-sort"></i>
                                        </button>
                                    </div>
                                </th>
                                <th>
                                    <div class="table-header">
                                        <span>Услуга</span>
                                        <button class="sort-btn" data-sort="service">
                                            <i class="fas fa-sort"></i>
                                        </button>
                                    </div>
                                </th>
                                <th>
                                    <div class="table-header">
                                        <span>Дата посещения</span>
                                        <button class="sort-btn" data-sort="visit">
                                            <i class="fas fa-sort"></i>
                                        </button>
                                    </div>
                                </th>
                                <th>Кол-во чел.</th>
                                <th>Дети до 3 лет</th>
                                <th>
                                    <div class="table-header">
                                        <span>Стоимость</span>
                                        <button class="sort-btn" data-sort="price">
                                            <i class="fas fa-sort"></i>
                                        </button>
                                    </div>
                                </th>
                                <th>Способ оплаты</th>
                                <th>Статус</th>
                                <th>
                                    <div class="table-header">
                                        <span>Дата создания</span>
                                        <button class="sort-btn" data-sort="created">
                                            <i class="fas fa-sort"></i>
                                        </button>
                                    </div>
                                </th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $order): ?>
                            <tr class="order-row" 
                                data-id="<?php echo $order['id']; ?>"
                                data-status="<?php echo $order['status_name']; ?>"
                                data-user="<?php echo htmlspecialchars($order['full_name']); ?>"
                                data-service="<?php echo htmlspecialchars($order['service_name']); ?>"
                                data-created="<?php echo strtotime($order['created_at']); ?>"
                                data-visit="<?php echo strtotime($order['desired_date'] . ' ' . $order['desired_time']); ?>"
                                data-price="<?php echo $order['total_price']; ?>">
                                <td class="order-id-cell">
                                    <span class="id-badge">#<?php echo $order['id']; ?></span>
                                </td>
                                <td class="order-user-cell">
                                    <div class="user-info">
                                        <div class="user-name"><?php echo htmlspecialchars($order['full_name']); ?></div>
                                        <div class="user-meta">
                                            <span class="user-email">
                                                <i class="fas fa-envelope"></i>
                                                <?php echo htmlspecialchars($order['email']); ?>
                                            </span>
                                            <span class="user-phone">
                                                <i class="fas fa-phone"></i>
                                                <?php echo htmlspecialchars($order['phone']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="order-service-cell">
                                    <div class="service-info">
                                        <div class="service-name"><?php echo htmlspecialchars($order['service_name']); ?></div>
                                    </div>
                                </td>
                                <td class="order-visit-cell">
                                    <div class="date-info">
                                        <div class="date"><?php echo date('d.m.Y', strtotime($order['desired_date'])); ?></div>
                                        <div class="time"><?php echo date('H:i', strtotime($order['desired_time'])); ?></div>
                                    </div>
                                </td>
                                <td class="order-people-cell">
                                    <span class="people-count">
                                        <i class="fas fa-users"></i>
                                        <?php echo $order['people_count']; ?>
                                    </span>
                                </td>
                                <td class="order-children-cell">
                                    <?php if($order['children_under_3'] > 0): ?>
                                        <span class="children-badge"><?php echo $order['children_under_3']; ?> реб.</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="order-price-cell">
                                    <div class="price-info">
                                        <div class="price"><?php echo number_format($order['total_price'], 0); ?> ₽</div>
                                    </div>
                                </td>
                                <td class="order-payment-cell">
                                    <span class="payment-method">
                                        <?php echo htmlspecialchars($order['payment_method']); ?>
                                    </span>
                                </td>
                                <td class="order-status-cell">
                                    <span class="status-badge status-<?php echo $order['status_name']; ?>">
                                        <?php echo htmlspecialchars($order['status_name']); ?>
                                    </span>
                                </td>
                                <td class="order-created-cell">
                                    <div class="date-info">
                                        <div class="date"><?php echo date('d.m.Y', strtotime($order['created_at'])); ?></div>
                                        <div class="time"><?php echo date('H:i', strtotime($order['created_at'])); ?></div>
                                    </div>
                                </td>
                                <td class="order-actions-cell">
                                    <div class="action-buttons">
                                        <button class="btn-action btn-edit-status" 
                                                data-order-id="<?php echo $order['id']; ?>"
                                                data-status-id="<?php echo $order['status_id']; ?>"
                                                data-admin-notes="<?php echo htmlspecialchars($order['admin_notes'] ?? ''); ?>"
                                                title="Изменить статус">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Пагинация -->
                <?php if($total_pages > 1): ?>
                <div class="pagination">
                    <?php if($page > 1): ?>
                        <button class="pagination-btn prev" onclick="changePage(<?php echo $page-1; ?>)">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    <?php else: ?>
                        <button class="pagination-btn prev" disabled>
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    <?php endif; ?>
                    
                    <div class="pagination-pages">
                        <?php 
                        $start = max(1, $page - 2);
                        $end = min($total_pages, $start + 4);
                        if($end - $start < 4) $start = max(1, $end - 4);
                        
                        for($i = $start; $i <= $end; $i++): ?>
                            <button class="pagination-page <?php echo $i == $page ? 'active' : ''; ?>" 
                                    onclick="changePage(<?php echo $i; ?>)">
                                <?php echo $i; ?>
                            </button>
                        <?php endfor; ?>
                        
                        <?php if($end < $total_pages): ?>
                            <span class="pagination-dots">...</span>
                            <button class="pagination-page" onclick="changePage(<?php echo $total_pages; ?>)">
                                <?php echo $total_pages; ?>
                            </button>
                        <?php endif; ?>
                    </div>
                    
                    <?php if($page < $total_pages): ?>
                        <button class="pagination-btn next" onclick="changePage(<?php echo $page+1; ?>)">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    <?php else: ?>
                        <button class="pagination-btn next" disabled>
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Информация о записях -->
            <div class="orders-footer">
                <div class="footer-info">
                    <p>
                        Показано 
                        <span id="visibleOrdersCount">
                            <?php echo $offset + 1; ?>-<?php echo min($offset + $limit, $total_orders); ?>
                        </span> 
                        из <span id="totalOrdersCount"><?php echo $total_orders; ?></span> заявок
                    </p>
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
</div>

<!-- Модальное окно для изменения статуса -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Изменение статуса заявки</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editForm">
                <input type="hidden" id="edit_order_id" name="order_id">
                
                <div class="form-group">
                    <label for="status_id" class="form-label">
                        <i class="fas fa-flag"></i> Статус:
                    </label>
                    <select id="status_id" name="status_id" required class="filter-select">
                        <?php foreach($statuses as $status): ?>
                            <option value="<?php echo $status['id']; ?>">
                                <?php echo htmlspecialchars($status['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="admin_notes" class="form-label">
                        <i class="fas fa-sticky-note"></i> Примечание:
                    </label>
                    <textarea id="admin_notes" name="admin_notes" rows="3" 
                              placeholder="Дополнительная информация..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-outline close-modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function changePage(page) {
    const url = new URL(window.location.href);
    url.searchParams.set('page', page);
    window.location.href = url.toString();
}

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
