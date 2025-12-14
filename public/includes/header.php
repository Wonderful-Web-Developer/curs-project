<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Аквапарк Апельсин - <?php echo $page_title ?? 'Главная'; ?></title>

    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    
    <!-- Общие стили -->
    <link rel="stylesheet" href="/styles/main.css">
    
    <!-- Иконки Font Awesome для улучшенного меню -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Специфичные стили для страницы -->
    <?php if(isset($page_styles)): ?>
        <?php foreach((array)$page_styles as $style): ?>
            <link rel="stylesheet" href="/styles/<?php echo $style; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <div class="logo">
                    <div class="logo-container">
                        <a href="/index.php">
                            <div class="logo-icon">
                                <img src="../assets/images/logo1.jpg" alt="logo">
                            </div>
                            <div class="logo-text">
                                <h1 class="logo-title">Аквапарк Апельсин</h1>
                                <p class="logo-subtitle">Семейный отдых</p>
                            </div>
                        </a>
                    </div>
                </div>
                
                <div class="nav-right">
                    <ul class="nav-menu">
                        <li class="nav-item">
                            <a href="/index.php" class="nav-link">
                                <i class="fas fa-home"></i>
                                <span>Главная</span>
                            </a>
                        </li>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <?php if($_SESSION['is_admin']): ?>
                                <li class="nav-item">
                                    <a href="/admin.php" class="nav-link">
                                        <i class="fas fa-cogs"></i>
                                        <span>Панель администратора</span>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="nav-item">
                                    <a href="/user_cabinet.php" class="nav-link">
                                        <i class="fas fa-user-circle"></i>
                                        <span>Личный кабинет</span>
                                    </a>
                                </li>
                                <!-- <li class="nav-item">
                                    <a href="/my_orders.php" class="nav-link">
                                        <i class="fas fa-list"></i>
                                        <span>Мои заявки</span>
                                    </a>
                                </li> -->
                                <li class="nav-item">
                                    <a href="/create_order.php" class="nav-link nav-cta">
                                        <i class="fas fa-plus-circle"></i>
                                        <span>Новая заявка</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li class="nav-item">
                                <a href="/logout.php" class="nav-link">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Выход</span>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a href="/login.php" class="nav-link">
                                    <i class="fas fa-sign-in-alt"></i>
                                    <span>Вход</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/register.php" class="nav-link nav-cta">
                                    <i class="fas fa-user-plus"></i>
                                    <span>Регистрация</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    
                    <div class="mobile-menu-toggle">
                        <i class="fas fa-bars"></i>
                    </div>
                </div>
            </nav>
        </div>
    </header>
    
    <!-- Мобильное меню -->
    <div class="mobile-menu-overlay"></div>
    
    <main class="main">