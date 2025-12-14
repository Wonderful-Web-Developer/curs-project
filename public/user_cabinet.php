<?php
require_once 'config/database.php';
require_once 'includes/auth_check.php';

// Получаем информацию о пользователе
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Получаем статистику пользователя
$stats_sql = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN o.status_id = (SELECT id FROM order_statuses WHERE name = 'Выполнена') THEN 1 ELSE 0 END) as completed_orders,
    SUM(CASE WHEN o.status_id = (SELECT id FROM order_statuses WHERE name = 'Новая') THEN 1 ELSE 0 END) as new_orders,
    SUM(CASE WHEN o.status_id = (SELECT id FROM order_statuses WHERE name = 'Отменена') THEN 1 ELSE 0 END) as canceled_orders,
    COALESCE(SUM(
        CASE WHEN o.status_id != (SELECT id FROM order_statuses WHERE name = 'Отменена') 
        THEN o.total_price 
        ELSE 0 
        END
    ), 0) as total_spent
    FROM orders o
    WHERE o.user_id = ?";
$stats_stmt = $pdo->prepare($stats_sql);
$stats_stmt->execute([$_SESSION['user_id']]);
$user_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Получаем ПОСЛЕДНИЕ 4 заявки пользователя
$sql = "SELECT o.*, os.name as status_name, sc.name as service_name, 
               pm.name as payment_method, t.price_per_person,
               (SELECT COUNT(*) FROM reviews r WHERE r.order_id = o.id) as has_review
        FROM orders o
        JOIN order_statuses os ON o.status_id = os.id
        JOIN tariffs t ON o.tariff_id = t.id
        JOIN service_categories sc ON t.category_id = sc.id
        JOIN payment_methods pm ON o.payment_method_id = pm.id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC 
        LIMIT 4";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Личный кабинет';
$page_styles = ['cabinet.css'];
$page_scripts = ['cabinet.js'];
require_once 'includes/header.php';
?>

<div class="cabinet-container">
    <!-- Статистика пользователя -->
    <div class="user-stats">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $user_stats['total_orders'] ?? 0; ?></h3>
                <p>Всего заявок</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $user_stats['completed_orders'] ?? 0; ?></h3>
                <p>Выполнено</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $user_stats['new_orders'] ?? 0; ?></h3>
                <p>Новых</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($user_stats['total_spent'] ?? 0, 0); ?> ₽</h3>
                <p>Всего потрачено</p>
            </div>
        </div>
    </div>

    <div class="cabinet-content">
        <!-- Боковая панель профиля -->
        <div class="profile-sidebar">
            <div class="profile-header">
                <div class="profile-avatar">
                    <div class="avatar-circle">
                        <span class="avatar-initial"><?php echo mb_substr($user['full_name'], 0, 1); ?></span>
                    </div>
                    <div class="profile-status">
                        <span class="status-dot <?php echo $user['is_admin'] ? 'admin' : 'user'; ?>"></span>
                        <span class="status-text"><?php echo $user['is_admin'] ? 'Администратор' : 'Пользователь'; ?></span>
                    </div>
                </div>
                <h2 class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></h2>
                <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>

            <div class="profile-info">
                <div class="info-item">
                    <i class="fas fa-phone"></i>
                    <div>
                        <span class="info-label">Телефон</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['phone']); ?></span>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-birthday-cake"></i>
                    <div>
                        <span class="info-label">Дата рождения</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['birth_date']); ?></span>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-calendar-alt"></i>
                    <div>
                        <span class="info-label">Дата регистрации</span>
                        <span class="info-value"><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>
            </div>

            <div class="profile-actions">
                <a href="/edit_profile.php" class="btn btn-outline">
                    <i class="fas fa-edit"></i> Редактировать профиль
                </a>
                <a href="/logout.php" class="btn btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Выйти
                </a>
            </div>
        </div>

        <!-- Основной контент -->
        <div class="main-content">
            <div class="content-header">
                <h2>Последние заявки</h2>
                <div class="header-actions">
                    <?php if($user_stats['total_orders'] > 0): ?>
                        <a href="/my_orders.php" class="btn btn-outline">
                            <i class="fas fa-list"></i> Все заявки
                        </a>
                    <?php endif; ?>
                    <a href="/create_order.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Новая заявка
                    </a>
                </div>
            </div>

            <?php if(empty($recent_orders)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h3>У вас пока нет заявок</h3>
                    <p>Создайте свою первую заявку и начните получать незабываемые впечатления!</p>
                    <a href="/create_order.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus"></i> Создать первую заявку
                    </a>
                </div>
            <?php else: ?>
                <div class="orders-grid">
                    <?php foreach($recent_orders as $order): ?>
                        <div class="order-card" data-status="<?php echo $order['status_name']; ?>">
                            <div class="order-header">
                                <div class="order-meta">
                                    <span class="order-date"><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></span>
                                    <span class="order-id">#<?php echo $order['id']; ?></span>
                                </div>
                                <span class="status-badge status-<?php echo $order['status_name']; ?>">
                                    <?php echo htmlspecialchars($order['status_name']); ?>
                                </span>
                            </div>

                            <div class="order-body">
                                <h4 class="order-service"><?php echo htmlspecialchars($order['service_name']); ?></h4>
                                
                                <div class="order-details">
                                    <div class="detail-item">
                                        <i class="far fa-calendar"></i>
                                        <span><?php echo date('d.m.Y H:i', strtotime($order['desired_date'] . ' ' . $order['desired_time'])); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-users"></i>
                                        <span><?php echo $order['people_count']; ?> человек</span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-credit-card"></i>
                                        <span><?php echo htmlspecialchars($order['payment_method']); ?></span>
                                    </div>
                                </div>

                                <div class="order-price">
                                    <span class="price-label">Стоимость:</span>
                                    <span class="price-value"><?php echo number_format($order['total_price'], 0); ?> ₽</span>
                                </div>
                            </div>

                            <div class="order-footer">
                                <?php if($order['status_name'] === 'Новая'): ?>
                                    <button class="btn btn-outline btn-sm btn-cancel" data-order-id="<?php echo $order['id']; ?>">
                                        <i class="fas fa-times"></i> Отменить
                                    </button>
                                <?php elseif($order['status_name'] === 'Выполнена'): ?>
                                    <?php if($order['has_review'] == 0): ?>
                                        <button class="btn btn-primary btn-sm" onclick="showReviewForm(<?php echo $order['id']; ?>)">
                                            <i class="fas fa-star"></i> Оставить отзыв
                                        </button>
                                    <?php else: ?>
                                        <span class="review-done">
                                            <i class="fas fa-check-circle"></i> Отзыв оставлен
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if($user_stats['total_orders'] > 5): ?>
                    <div class="view-all-section">
                        <p>Показано <?php echo count($recent_orders); ?> из <?php echo $user_stats['total_orders']; ?> заявок</p>
                        <!-- <a href="/my_orders.php" class="btn btn-outline btn-block">
                            <i class="fas fa-list"></i> Показать все заявки
                        </a> -->
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
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