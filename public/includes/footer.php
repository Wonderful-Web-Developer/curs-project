</main>
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Контакты</h3>
                    <div class="contact-info">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-text">
                                <strong>Телефон</strong>
                                <span>8 (818) 397-55-55</span>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-text">
                                <strong>Email</strong>
                                <span>arktika.apelsin@yandex.ru</span>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-text">
                                <strong>Адрес</strong>
                                <span>г. Онега, ул. Геологическая, д. 2в</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="footer-section opening-hours">
                    <h3>Часы работы</h3>
                    <ul class="hours-list">
                        <li>
                            <span class="day">Понедельник</span>
                            <span class="time">15:00-20:30</span>
                        </li>
                        <li>
                            <span class="day">Вторник-Суббота</span>
                            <span class="time">10:00-20:30</span>
                        </li>
                        <li>
                            <span class="day">Воскресенье</span>
                            <span class="time">12:00-20:30</span>
                        </li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Присоединяйтесь к нам</h3>
                    <p>Будьте в курсе всех акций и новостей!</p>
                    <div class="social-links">
                        <a href="https://vk.com/public220017152" class="social-link vk" aria-label="ВКонтакте">
                            <!-- Можно использовать Font Awesome или свою картинку -->
                            <!-- Вариант с Font Awesome: <i class="fab fa-vk"></i> -->
                            <!-- Вариант с картинкой: -->
                            <img src="/assets/images/vk-icon.svg" alt="VK" onerror="this.onerror=null; this.outerHTML='<i class=\'fab fa-vk\'></i>';">
                        </a>
                    </div>
                </div>
            </div>
            <p class="copyright">2025 Аквапарк Апельсин. Все права защищены.</p>
        </div>
    </footer>
    
    <!-- Подключение Font Awesome для иконок -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Общие скрипты -->
    <script src="/scripts/main.js"></script>
    
    <!-- Специфичные скрипты для страницы -->
    <?php if(isset($page_scripts)): ?>
        <?php foreach((array)$page_scripts as $script): ?>
            <script src="/scripts/<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>