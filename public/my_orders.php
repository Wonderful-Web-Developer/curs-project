<?php
require_once 'config/database.php';
require_once 'config/paths.php';
require_once 'includes/auth_check.php';

// Получаем ВСЕ заявки пользователя
$sql = "SELECT o.*, os.name as status_name, sc.name as service_name, 
               pm.name as payment_method, t.price_per_person,
               (SELECT COUNT(*) FROM reviews r WHERE r.order_id = o.id) as has_review
        FROM orders o
        JOIN order_statuses os ON o.status_id = os.id
        JOIN tariffs t ON o.tariff_id = t.id
        JOIN service_categories sc ON t.category_id = sc.id
        JOIN payment_methods pm ON o.payment_method_id = pm.id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$all_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем общее количество заявок для статистики
$total_orders = count($all_orders);

// Группируем заявки по статусам для фильтрации
$status_counts = [
    'Все' => $total_orders,
    'Новая' => 0,
    'В обработке' => 0,
    'Выполнена' => 0,
    'Отменена' => 0
];

foreach ($all_orders as $order) {
    $status = $order['status_name'];
    if (isset($status_counts[$status])) {
        $status_counts[$status]++;
    }
}

$page_title = 'Мои заявки';
$page_styles = ['cabinet.css', 'orders.css'];
$page_scripts = ['cabinet.js', 'orders.js'];
require_once 'includes/header.php';
?>

<div class="cabinet-container">
    <div class="orders-page-header">
        <div class="breadcrumb">
            <a href="/user_cabinet.php"><i class="fas fa-home"></i> Личный кабинет</a>
            <i class="fas fa-chevron-right"></i>
            <span>Мои заявки</span>
        </div>
        
        <div class="page-title-section">
            <h1><i class="fas fa-clipboard-list"></i> Мои заявки</h1>
            <div class="page-actions">
                <a href="/user_cabinet.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Назад в кабинет
                </a>
                <a href="/create_order.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Новая заявка
                </a>
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
        </div>
    </div>

    <div class="orders-content">
        <!-- Фильтры и поиск -->
        <div class="orders-filters">
            <div class="filter-group">
                <label for="statusFilter"><i class="fas fa-filter"></i> Фильтр по статусу:</label>
                <select id="statusFilter" class="filter-select">
                    <option value="all">Все заявки</option>
                    <option value="Новая">Новые</option>
                    <option value="В обработке">В обработке</option>
                    <option value="Выполнена">Выполненные</option>
                    <option value="Отменена">Отмененные</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="dateFilter"><i class="fas fa-calendar-alt"></i> Период:</label>
                <select id="dateFilter" class="filter-select">
                    <option value="all">За всё время</option>
                    <option value="month">За этот месяц</option>
                    <option value="3months">За 3 месяца</option>
                    <option value="year">За этот год</option>
                </select>
            </div>
            
            <div class="filter-group search-group">
                <label for="searchOrders"><i class="fas fa-search"></i> Поиск:</label>
                <div class="search-input-wrapper">
                    <input type="text" id="searchOrders" placeholder="Поиск по услуге или ID...">
                    <i class="fas fa-search search-icon"></i>
                </div>
            </div>
            
            <button class="btn btn-outline reset-filters">
                <i class="fas fa-redo"></i> Сбросить фильтры
            </button>
        </div>

        <?php if(empty($all_orders)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h3>Заявок не найдено</h3>
                <p>У вас пока нет ни одной заявки. Создайте первую заявку прямо сейчас!</p>
                <a href="/create_order.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus"></i> Создать первую заявку
                </a>
            </div>
        <?php else: ?>

            <!-- Контейнер для сообщения "Нет результатов" -->
             <div id="noResultsContainer" class="no-results-container" style="display: none;">
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Заявки не найдены</h3>
                    <p>Попробуйте изменить параметры фильтрации</p>
                </div>
            </div>


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
                                        <span>Услуга</span>
                                        <button class="sort-btn" data-sort="service">
                                            <i class="fas fa-sort"></i>
                                        </button>
                                    </div>
                                </th>
                                <th>
                                    <div class="table-header">
                                        <span>Дата создания</span>
                                        <button class="sort-btn" data-sort="created">
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
                                <th>
                                    <div class="table-header">
                                        <span>Стоимость</span>
                                        <button class="sort-btn" data-sort="price">
                                            <i class="fas fa-sort"></i>
                                        </button>
                                    </div>
                                </th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($all_orders as $order): ?>
                            <tr class="order-row" 
                                data-id="<?php echo $order['id']; ?>"
                                data-status="<?php echo $order['status_name']; ?>"
                                data-service="<?php echo htmlspecialchars($order['service_name']); ?>"
                                data-created="<?php echo strtotime($order['created_at']); ?>"
                                data-visit="<?php echo strtotime($order['desired_date'] . ' ' . $order['desired_time']); ?>"
                                data-price="<?php echo $order['total_price']; ?>">
                                <td class="order-id-cell">
                                    <span class="id-badge">#<?php echo $order['id']; ?></span>
                                </td>
                                <td class="order-service-cell">
                                    <div class="service-info">
                                        <div class="service-name"><?php echo htmlspecialchars($order['service_name']); ?></div>
                                        <div class="service-meta">
                                            <span class="payment-method">
                                                <i class="fas fa-credit-card"></i>
                                                <?php echo htmlspecialchars($order['payment_method']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="order-date-cell">
                                    <div class="date-info">
                                        <div class="date"><?php echo date('d.m.Y', strtotime($order['created_at'])); ?></div>
                                        <div class="time"><?php echo date('H:i', strtotime($order['created_at'])); ?></div>
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
                                <td class="order-price-cell">
                                    <div class="price-info">
                                        <div class="price"><?php echo number_format($order['total_price'], 0); ?> ₽</div>
                                        <div class="price-per-person">
                                            <?php 
                                            $per_person = $order['total_price'] / $order['people_count'];
                                            echo number_format($per_person, 0); ?> ₽/чел
                                        </div>
                                    </div>
                                </td>
                                <td class="order-status-cell">
                                    <span class="status-badge status-<?php echo $order['status_name']; ?>">
                                        <?php echo htmlspecialchars($order['status_name']); ?>
                                    </span>
                                </td>
                                <td class="order-actions-cell">
                                    <div class="action-buttons">
                                        <?php if($order['status_name'] === 'Новая'): ?>
                                            <button class="btn-action btn-cancel" 
                                                    data-order-id="<?php echo $order['id']; ?>"
                                                    title="Отменить заявку">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php elseif($order['status_name'] === 'Выполнена'): ?>
                                            <?php if($order['has_review'] == 0): ?>
                                                <button class="btn-action btn-review pulse" 
                                                        onclick="showReviewForm(<?php echo $order['id']; ?>)"
                                                        title="Оставить отзыв">
                                                    <i class="fas fa-star"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn-action btn-reviewed" title="Отзыв оставлен" disabled>
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Пагинация -->
                <?php 
                $total_orders = count($all_orders);
                if($total_orders > 10): 
                ?>
                <div class="pagination">
                    <button class="pagination-btn prev" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div class="pagination-pages">
                        <?php
                        $itemsPerPage = 10;
                        $totalPages = ceil($total_orders / $itemsPerPage);
                        
                        $maxVisiblePages = 5;
                        $startPage = 1;
                        $endPage = min($maxVisiblePages, $totalPages);
                        
                        if ($totalPages > $maxVisiblePages) {
                            $endPage = 3; 
                        }
                        
                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <button class="pagination-page <?php echo ($i === 1) ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </button>
                        <?php endfor; ?>
                        
                        <?php if($totalPages > $maxVisiblePages): ?>
                            <span class="pagination-dots">...</span>
                            <button class="pagination-page"><?php echo $totalPages; ?></button>
                        <?php endif; ?>
                    </div>
                    <button class="pagination-btn next" <?php echo ($totalPages > 1) ? '' : 'disabled'; ?>>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- другие действия -->
            <div class="orders-footer">
                <div class="footer-info">
                    <p>Показано <span id="visibleOrdersCount">1-<?php echo min(10, $total_orders); ?></span> из <span id="totalOrdersCount"><?php echo $total_orders; ?></span> заявок</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>


<!-- Модальное окно для отзыва -->
<div id="reviewModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Оставить отзыв</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <form id="reviewForm">
                <input type="hidden" id="review_order_id">
                
                <div class="rating-section">
                    <p class="rating-label">Оцените ваше впечатление:</p>
                    <div class="rating-container">
                        <div class="rating-stars">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <div class="star-container" data-value="<?php echo $i; ?>">
                                    <svg class="star" width="50" height="50" viewBox="0 0 24 24">
                                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                    </svg>
                                    <span class="star-number"><?php echo $i; ?></span>
                                </div>
                            <?php endfor; ?>
                        </div>
                        <div class="rating-hint">
                            <span id="ratingText">Выберите оценку</span>
                        </div>
                    </div>
                    <input type="hidden" id="rating" name="rating" required>
                </div>

                <div class="form-group">
                    <label for="comment" class="form-label">
                        <i class="fas fa-comment"></i> Комментарий
                    </label>
                    <textarea id="comment" name="comment" rows="4" 
                              placeholder="Расскажите о ваших впечатлениях..."></textarea>
                    <div class="char-counter">
                        <span id="charCount">0</span> / 500 символов
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-outline close-modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Отправить отзыв
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php require_once 'includes/footer.php'; ?>