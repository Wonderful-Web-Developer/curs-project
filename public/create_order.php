<?php
require_once 'config/database.php';
require_once 'includes/auth_check.php';

$errors = [];
$success = false;

// Получаем тарифы для выбора
$category_id = $_GET['category'] ?? null;

$sql = "SELECT t.*, sc.name as category_name, tt.name as ticket_type, 
               ts.name as time_slot, dt.name as day_type
        FROM tariffs t
        JOIN service_categories sc ON t.category_id = sc.id
        JOIN ticket_types tt ON t.ticket_type_id = tt.id
        JOIN time_slots ts ON t.time_slot_id = ts.id
        JOIN day_types dt ON t.day_type_id = dt.id
        WHERE t.is_active = 1 
        AND (t.category_id = ? OR ? IS NULL)
        ORDER BY t.ticket_type_id, t.time_slot_id, t.day_type_id, t.duration_hours";

$stmt = $pdo->prepare($sql);
$stmt->execute([$category_id, $category_id]);
$tariffs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем способы оплаты
$stmt = $pdo->query("SELECT * FROM payment_methods ORDER BY id");
$payment_methods = $stmt->fetchAll(PDO::FETCH_ASSOC);

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tariff_id = $_POST['tariff_id'] ?? '';
    $people_count = $_POST['people_count'] ?? 1;
    $children_under_3 = $_POST['children_under_3'] ?? 0;
    $desired_date = $_POST['desired_date'] ?? '';
    $desired_time = $_POST['desired_time'] ?? '';
    $payment_method_id = $_POST['payment_method_id'] ?? '';
    
    // Валидация
    if(empty($tariff_id)) {
        $errors[] = 'Выберите тариф';
    }
    
    if($people_count < 1 || $people_count > 10) {
        $errors[] = 'Количество человек должно быть от 1 до 10';
    }
    
    if($children_under_3 < 0 || $children_under_3 > 5) {
        $errors[] = 'Количество детей до 3 лет должно быть от 0 до 5';
    }
    
    if(empty($desired_date)) {
        $errors[] = 'Выберите дату';
    } else {
        $selected_date = strtotime($desired_date);
        $today = strtotime('today');
        if($selected_date < $today) {
            $errors[] = 'Дата не может быть в прошлом';
        }
    }
    
    if(empty($desired_time)) {
        $errors[] = 'Выберите время';
    } else {
        $time = strtotime($desired_time);
        if($time < strtotime('10:00') || $time > strtotime('21:00')) {
            $errors[] = 'Время должно быть с 10:00 до 21:00';
        }
    }
    
    if(empty($payment_method_id)) {
        $errors[] = 'Выберите способ оплаты';
    }
    
    if(empty($errors)) {
        // Получаем информацию о тарифе
        $stmt = $pdo->prepare("SELECT price_per_person FROM tariffs WHERE id = ?");
        $stmt->execute([$tariff_id]);
        $tariff = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($tariff) {
            // Рассчитываем стоимость (дети до 3 лет бесплатно)
            $total_price = $tariff['price_per_person'] * $people_count;
            
            // Создаем заявку
            $sql = "INSERT INTO orders (user_id, tariff_id, people_count, children_under_3, 
                       desired_date, desired_time, total_price, payment_method_id, status_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
            $stmt = $pdo->prepare($sql);
            
            if($stmt->execute([
                $_SESSION['user_id'],
                $tariff_id,
                $people_count,
                $children_under_3,
                $desired_date,
                $desired_time,
                $total_price,
                $payment_method_id
            ])) {
                $success = true;
                $last_order_id = $pdo->lastInsertId();
            } else {
                $errors[] = 'Ошибка при создании заявки';
            }
        }
    }
}

$page_title = 'Создание заявки';
$page_styles = ['order.css'];
$page_scripts = ['order.js'];
require_once 'includes/header.php';
?>

<div class="container">
    <h2 style="margin: 20px 0px 20px 0px">Создание заявки</h2>
    
    <?php if($success): ?>
        <div class="alert success">
            <h3>Заявка успешно создана!</h3>
            <p>Номер вашей заявки: #<?php echo $last_order_id; ?></p>
            <p>Сумма к оплате: <?php echo number_format($total_price, 2); ?> руб.</p>
            <p>Статус: <span class="status status-Новая">Новая</span></p>
            <div class="alert-actions">
                <a href="/user_cabinet.php" class="btn">Вернуться в личный кабинет</a>
                <a href="/create_order.php" class="btn btn-secondary">Создать ещё заявку</a>
            </div>
        </div>
    <?php elseif(!empty($errors)): ?>
        <div class="alert error">
            <h3>Ошибки при создании заявки:</h3>
            <ul>
                <?php foreach($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if(!$success): ?>
    <div class="create-order-form">
        <form method="POST" id="orderForm">
            <div class="form-section">
                <h3>Выберите тариф</h3>
                <?php if(empty($tariffs)): ?>
                    <div class="no-tariffs">
                        <p>Нет доступных тарифов для выбранной категории</p>
                        <a href="/index.php" class="btn">Вернуться на главную</a>
                    </div>
                <?php else: ?>
                    <div class="tariffs-grid">
                        <?php foreach($tariffs as $tariff): ?>
                        <div class="tariff-item">
                            <input type="radio" name="tariff_id" value="<?php echo $tariff['id']; ?>" 
                                id="tariff_<?php echo $tariff['id']; ?>" class="tariff-radio" required>
                            <label for="tariff_<?php echo $tariff['id']; ?>" class="tariff-card">
                                <div class="tariff-card-content">
                                    <h4><?php echo htmlspecialchars($tariff['category_name'] . ' - ' . $tariff['ticket_type']); ?></h4>
                                    <div class="tariff-details">
                                        <p class="time-info"><?php echo htmlspecialchars($tariff['time_slot']); ?></p>
                                        <p class="day-info"><?php echo htmlspecialchars($tariff['day_type']); ?></p>
                                        <p class="duration">⏱️ Продолжительность: <?php echo $tariff['duration_hours']; ?> час.</p>
                                        <p class="price"><?php echo number_format($tariff['price_per_person'], 2); ?> руб./чел.</p>
                                    </div>
                                    <?php if($tariff['notes']): ?>
                                        <div class="notes">
                                            <strong>ℹ️ Примечание:</strong> <?php echo htmlspecialchars($tariff['notes']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-section">
                <h3>Детали заявки</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="people_count">Количество человек (от 3 лет):</label>
                        <input type="number" id="people_count" name="people_count" 
                               min="1" max="10" value="1" required>
                        <small class="form-text">От 1 до 10 человек</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="children_under_3">Детей до 3 лет (бесплатно):</label>
                        <input type="number" id="children_under_3" name="children_under_3" 
                               min="0" max="5" value="0">
                        <small class="form-text">До 5 детей включительно</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="desired_date">Желаемая дата посещения:</label>
                        <input type="date" id="desired_date" name="desired_date" 
                               min="<?php echo date('Y-m-d'); ?>" required>
                        <small class="form-text">Выберите дату</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="desired_time">Желаемое время:</label>
                        <input type="time" id="desired_time" name="desired_time" 
                               min="10:00" max="21:00" value="14:00" required>
                        <small class="form-text">Время работы: 10:00 - 21:00</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="payment_method_id">Способ оплаты:</label>
                    <select id="payment_method_id" name="payment_method_id" required>
                        <option value="">Выберите способ оплаты</option>
                        <?php foreach($payment_methods as $method): ?>
                            <option value="<?php echo $method['id']; ?>">
                                <?php echo htmlspecialchars($method['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text">Выберите удобный способ оплаты</small>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <span class="btn-icon">✓</span> Отправить заявку
                </button>
                <a href="/user_cabinet.php" class="btn btn-secondary">
                    <span class="btn-icon">←</span> Вернуться в кабинет
                </a>
            </div>
        </form>
    </div>
    
    <div id="priceCalculation" class="price-calculation">
        <h4><span class="calc-icon">🧮</span> Расчет стоимости</h4>
        <div class="calculation-details">
            <div class="calculation-row">
                <span>Стоимость за 1 человека:</span>
                <span id="pricePerPerson">0.00</span> руб.
            </div>
            <div class="calculation-row">
                <span>Количество человек:</span>
                <span id="peopleCountDisplay">1</span> чел.
            </div>
            <div class="calculation-row">
                <span>Детей до 3 лет:</span>
                <span id="childrenCount">0</span> чел. (бесплатно)
            </div>
            <div class="calculation-row total">
                <span>Итого к оплате:</span>
                <span id="totalPrice">0.00</span> руб.
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Передаем тарифы из PHP в JavaScript
window.tariffs = <?php echo json_encode($tariffs); ?>;
</script>

<?php require_once 'includes/footer.php'; ?>