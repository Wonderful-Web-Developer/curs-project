<?php
require_once 'config/database.php';
$page_title = 'Главная';
$page_styles = ['index.css'];
require_once 'includes/header.php';

// Правила посещения
$rules = [
    [
        'icon' => '👞',
        'title' => 'Перемещение в тапках',
        'desc' => 'Для поддержания чистоты'
    ],
    [
        'icon' => '🚿',
        'title' => 'Душ перед бассейном',
        'desc' => 'Обязательно для всех посетителей'
    ],
    [
        'icon' => '🧹',
        'title' => 'Соблюдайте чистоту',
        'desc' => 'Уважайте труд уборщиков'
    ],
    [
        'icon' => '🚭',
        'title' => 'Курение запрещено',
        'desc' => 'Только в специально отведенных местах'
    ],
    [
        'icon' => '🚫',
        'title' => 'Нет алкоголю',
        'desc' => 'В состоянии опьянения вход запрещен'
    ],
    [
        'icon' => '🍔',
        'title' => 'Своя еда',
        'desc' => 'Приносить свою еду и напитки запрещено'
    ],
    [
        'icon' => '👶',
        'title' => 'Дети до 3 лет',
        'desc' => 'Специальные подгузники для купания'
    ],
    [
        'icon' => '🦺',
        'title' => 'Дети до 7 лет',
        'desc' => 'Нарукавники или жилет обязательны'
    ],
    [
        'icon' => '👨‍👦',
        'title' => 'Дети до 14 лет',
        'desc' => 'Только с сопровождением взрослых'
    ],
    [
        'icon' => '🐕',
        'title' => 'Животные',
        'desc' => 'Домашние животные не допускаются'
    ],
    [
        'icon' => '🤫',
        'title' => 'Тишина',
        'desc' => 'Избегайте громких криков'
    ],
    [
        'icon' => '🏖️',
        'title' => 'Лежаки',
        'desc' => 'Не занимайте лежаки вещами'
    ],
    [
        'icon' => '🚽',
        'title' => 'Туалеты',
        'desc' => 'Пользуйтесь оборудованными туалетами'
    ],
    [
        'icon' => '👨‍⚕️',
        'title' => 'Консультация врача',
        'desc' => 'При проблемах со здоровьем'
    ],
    [
        'icon' => '👙',
        'title' => 'Купальная одежда',
        'desc' => 'Только купальники и плавки'
    ],
    [
        'icon' => '🏃',
        'title' => 'Безопасность',
        'desc' => 'Не бегайте и не ныряйте с бортиков'
    ]
];
?>

<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h2 class="hero-title">Добро пожаловать в аквапарк <span class="highlight">"Апельсин"</span>!</h2>
            <p class="hero-subtitle">Современный комплекс водных развлечений для всей семьи</p>
            <div class="hero-stats">
                <div class="stat">
                    <span class="stat-number">2+</span>
                    <span class="stat-label">бассейна<br>(взрослые и детские)</span>
                </div>
                <div class="stat">
                    <span class="stat-number">2</span>
                    <span class="stat-label">хамама<br>(турецкий и бухарский)</span>
                </div>
                <div class="stat">
                    <span class="stat-number">3+</span>
                    <span class="stat-label">доп. услуги<br>(соляная пещера, LPG-массаж, кедровая бочка)</span>
                </div>
                <div class="stat">
                    <span class="stat-number">4+</span>
                    <span class="stat-label">гидромассажные<br>зоны и джакузи</span>
                </div>
            </div>
            <div class="hero-buttons">
                <a href="#services" class="btn btn-primary">Выбрать услугу</a>
                <a href="#rules" class="btn btn-secondary">Правила посещения</a>
            </div>
        </div>
    </div>
    <div class="hero-wave">
        <svg class="wave" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25" fill="currentColor"></path>
            <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5" fill="currentColor"></path>
            <path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z" fill="currentColor"></path>
        </svg>
    </div>
</section>

<section class="about">
    <div class="container">
        <div class="about-content">
            <div class="about-text">
                <h2 class="section-title">О компании</h2>
                <p class="about-description">Аквапарк "Апельсин" - это современный комплекс водных развлечений, оборудованный по последнему слову техники. Мы создали пространство, где каждый найдет развлечение по душе.</p>
                <div class="features">
                    <div class="feature">
                        <span class="feature-icon">🌟</span>
                        <h3 class="feature-title">Безопасность</h3>
                        <p class="feature-desc">Круглосуточная охрана и спасатели</p>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">🧼</span>
                        <h3 class="feature-title">Чистота</h3>
                        <p class="feature-desc">Многоуровневая система очистки воды</p>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">😊</span>
                        <h3 class="feature-title">Комфорт</h3>
                        <p class="feature-desc">Просторные раздевалки и зоны отдыха</p>
                    </div>
                </div>
            </div>
            <div class="about-image">
                <div class="image-placeholder">
                    <!-- <span>🏊‍♀️ Аквапарк "Апельсин"</span> -->
                </div>
            </div>
        </div>
    </div>
</section>

<section id="services" class="services">
    <div class="container">
        <h2 class="section-title">Наши услуги</h2>
        <p class="section-subtitle">Выберите подходящий вариант отдыха для себя и своей семьи</p>
        <div class="services-grid">
            <?php
            $sql = "SELECT DISTINCT sc.id, sc.name, sc.description FROM service_categories sc 
                    JOIN tariffs t ON sc.id = t.category_id 
                    WHERE t.is_active = 1 
                    GROUP BY sc.id";
            $stmt = $pdo->query($sql);
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $icons = ['🎫', '👨‍👩‍👧‍👦', '🎉', '🏊‍♂️', '🍽️', '🏨'];
            $i = 0;
            
            foreach($categories as $category):
                $icon = $icons[$i % count($icons)];
                $i++;
            ?>
            <div class="service-card" data-aos="fade-up">
                <div class="service-icon"><?php echo $icon; ?></div>
                <h3 class="service-title"><?php echo htmlspecialchars($category['name']); ?></h3>
                <p class="service-description"><?php echo htmlspecialchars($category['description']); ?></p>
                <a href="/create_order.php?category=<?php echo $category['id']; ?>" class="btn btn-secondary">
                    Выбрать
                    <svg class="btn-icon" viewBox="0 0 24 24" width="16" height="16">
                        <path fill="currentColor" d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z" />
                    </svg>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="rules" class="rules">
    <div class="container">
        <div class="rules-header">
            <h2 class="section-title">Правила посещения</h2>
            <p class="section-subtitle">Для вашей безопасности и комфортного отдыха</p>
        </div>
        <div class="rules-grid">
            <?php foreach($rules as $index => $rule): ?>
            <div class="rule-card" data-aos="fade-up" data-aos-delay="<?php echo $index * 50; ?>">
                <div class="rule-icon"><?php echo $rule['icon']; ?></div>
                <div class="rule-content">
                    <h3 class="rule-title"><?php echo $rule['title']; ?></h3>
                    <p class="rule-desc"><?php echo $rule['desc']; ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="rules-note">
            <div class="alert info">
                <strong>Важно:</strong> Нарушение правил может привести к отказу в обслуживании. 
                Сохраняйте билет до конца посещения.
            </div>
        </div>
    </div>
</section>




<section id="what-to-take" class="what-to-take">
    <div class="container">
        <div class="what-to-take-header">
            <h2 class="section-title">Что взять с собой</h2>
            <p class="section-subtitle">Необходимые вещи для комфортного посещения аквапарка</p>
        </div>
        
        <!-- Блок "Подготовьтесь к отдыху" -->
        <div class="what-to-take-prepare">
            <div class="prepare-content">
                <div class="prepare-icon">
                    <i class="fas fa-swimming-pool"></i>
                </div>
                <h3 class="prepare-title">Подготовьтесь к отдыху</h3>
                <p class="prepare-description">Все необходимое для комфортного посещения аквапарка</p>
            </div>
        </div>
        
        <!-- Список вещей в grid -->
        <div class="what-to-take-list">
            <ul>
                <li>
                    <div class="item-icon">
                        <i class="fas fa-female"></i>
                        <i class="fas fa-male"></i>
                    </div>
                    <div class="item-content">
                        <h3>Купальник / плавки</h3>
                        <p>Купальные шорты или купальник для плавания</p>
                    </div>
                </li>
                <li>
                    <div class="item-icon">
                        <i class="fas fa-swimmer"></i>
                    </div>
                    <div class="item-content">
                        <h3>Шапочка для плавания</h3>
                        <p>Для представительниц прекрасного пола любого возраста (кроме детей до 3-х лет)</p>
                    </div>
                </li>
                <li>
                    <div class="item-icon">
                        <i class="fas fa-shoe-prints"></i>
                    </div>
                    <div class="item-content">
                        <h3>Сменная обувь</h3>
                        <p>Для бассейна - удобные тапочки или сланцы</p>
                    </div>
                </li>
                <li>
                    <div class="item-icon">
                        <i class="fas fa-soap"></i>
                    </div>
                    <div class="item-content">
                        <h3>Средства гигиены</h3>
                        <p>Мыло, шампунь, мочалка, полотенце</p>
                    </div>
                </li>
                <li>
                    <div class="item-icon">
                        <i class="fas fa-life-ring"></i>
                    </div>
                    <div class="item-content">
                        <h3>Нарукавники, жилеты</h3>
                        <p>Небольшие круги для плавания (строго без декоративного наполнителя)</p>
                    </div>
                </li>
                <li>
                    <div class="item-icon">
                        <i class="fas fa-smile-beam"></i>
                    </div>
                    <div class="item-content">
                        <h3>Хорошее настроение</h3>
                        <p>Самый важный элемент для отличного отдыха!</p>
                    </div>
                </li>
            </ul>
        </div>
        
        <div class="what-to-take-note">
            <div class="alert info">
                <i class="fas fa-info-circle"></i>
                <strong>Важно:</strong> Пожалуйста, не приносите стеклянную посуду, колющие и режущие предметы.
            </div>
        </div>
    </div>
</section>




<section class="bright-impressions">
    <div class="bright-container">
        <div class="bright-header">
            <h2>Наш аквакомплекс подарит вам:</h2>
        </div>
        
        <div class="impressions-list">
            <ul>
                <li>Яркие впечатления</li>
                <li>Отдых от гаджетов</li>
                <li>Незабываемые эмоции</li>
                <li>Памятные моменты</li>
                <li>Время с семьей</li>
                <li>Возможность попробовать "что-то новое"</li>
            </ul>
        </div>
        
        <div class="impressions-cta">
            <a href="#services" class="btn btn-primary">
                <i class="fas fa-water" style="margin-right: 8px;"></i> Выбрать развлечения
            </a>
        </div>
    </div>
</section>




<section class="reviews-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Отзывы посетителей</h2>
            <p class="section-subtitle">Что говорят наши гости</p>
        </div>
        
        <div class="reviews-grid">
            <?php
            // Запрос для получения последних 5 отзывов с информацией о пользователях
            $reviews_sql = "
                SELECT 
                    r.*, 
                    u.full_name, 
                    sc.name as service_name,
                    o.total_price,
                    r.created_at as review_date
                FROM reviews r
                JOIN orders o ON r.order_id = o.id
                JOIN users u ON o.user_id = u.id
                JOIN tariffs t ON o.tariff_id = t.id
                JOIN service_categories sc ON t.category_id = sc.id
                ORDER BY r.created_at DESC
                LIMIT 6
            ";
            
            $reviews_stmt = $pdo->query($reviews_sql);
            $reviews = $reviews_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($reviews) > 0):
                foreach($reviews as $review):
                    // Генерация звездочек рейтинга
                    $rating_stars = '';
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $review['rating']) {
                            $rating_stars .= '<i class="fas fa-star"></i>';
                        } else {
                            $rating_stars .= '<i class="far fa-star"></i>';
                        }
                    }
                    
                    // Форматирование даты
                    $review_date = date('d.m.Y', strtotime($review['review_date']));
                    
                    // Сокращение имени (только имя)
                    $full_name_parts = explode(' ', $review['full_name']);
                    $short_name = $full_name_parts[0] . ' ' . mb_substr($full_name_parts[1] ?? '', 0, 1) . '.';
            ?>
            <div class="review-card">
                <div class="review-header">
                    <div class="reviewer-info">
                        <div class="reviewer-avatar">
                            <?php echo mb_substr($full_name_parts[0], 0, 1); ?>
                        </div>
                        <div class="reviewer-details">
                            <h4 class="reviewer-name"><?php echo htmlspecialchars($short_name); ?></h4>
                            <div class="review-service"><?php echo htmlspecialchars($review['service_name']); ?></div>
                        </div>
                    </div>
                    <div class="review-rating">
                        <div class="stars"><?php echo $rating_stars; ?></div>
                        <div class="review-date"><?php echo $review_date; ?></div>
                    </div>
                </div>
                <div class="review-content">
                    <p class="review-comment"><?php echo htmlspecialchars($review['comment']); ?></p>
                </div>
                <div class="review-footer">
                    <div class="review-price">
                        <i class="fas fa-ruble-sign"></i>
                        <?php echo number_format($review['total_price'], 0, '', ' '); ?>
                    </div>
                    <div class="review-verified">
                        <i class="fas fa-check-circle"></i> Подтвержденный отзыв
                    </div>
                </div>
            </div>
            <?php 
                endforeach;
            else:
            ?>
            <div class="no-reviews">
                <div class="no-reviews-icon">
                    <i class="far fa-comment-alt"></i>
                </div>
                <h3>Пока нет отзывов</h3>
                <p>Будьте первым, кто оставит отзыв после посещения нашего аквапарка!</p>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="reviews-actions">
            <a href="#" class="btn btn-secondary" id="add-review-btn">
                <i class="fas fa-plus-circle"></i> Оставить отзыв
            </a>
            <a href="#" class="btn btn-outline" id="all-reviews-btn">
                <i class="fas fa-star"></i> Все отзывы
            </a>
        </div>
    </div>
</section>




<?php require_once 'includes/footer.php'; ?>