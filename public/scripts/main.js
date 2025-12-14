// Общие функции для всех страниц

// Плавная прокрутка к якорям
document.addEventListener('DOMContentLoaded', function() {
    // Плавная прокрутка для внутренних ссылок
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
    // Форматирование телефона
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value[0] === '7') {
                    value = '8' + value.substring(1);
                }
                if (value.length > 1) value = value.substring(0, 1) + '(' + value.substring(1);
                if (value.length > 5) value = value.substring(0, 5) + ')' + value.substring(5);
                if (value.length > 9) value = value.substring(0, 9) + '-' + value.substring(9);
                if (value.length > 12) value = value.substring(0, 12) + '-' + value.substring(12);
                if (value.length > 15) value = value.substring(0, 15);
            }
            e.target.value = value;
        });
    });
    
    // Закрытие модальных окон при клике вне их
    window.addEventListener('click', function(e) {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
    
    // Закрытие модальных окон при нажатии ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                modal.style.display = 'none';
            });
        }
    });
    
    // Инициализация мобильного меню - УЛУЧШЕННАЯ ВЕРСИЯ
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    const overlay = document.querySelector('.mobile-menu-overlay');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            
            const isActive = navMenu.classList.contains('active');
            
            if (!isActive) {
                // Открываем меню
                navMenu.classList.add('active');
                overlay.classList.add('active');
                document.body.classList.add('menu-open');
                
                const icon = menuToggle.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                }
            } else {
                // Закрываем меню
                navMenu.classList.remove('active');
                overlay.classList.remove('active');
                document.body.classList.remove('menu-open');
                
                const icon = menuToggle.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        });
        
        // Закрытие при клике на overlay
        overlay.addEventListener('click', function(e) {
            e.stopPropagation();
            
            navMenu.classList.remove('active');
            overlay.classList.remove('active');
            document.body.classList.remove('menu-open');
            
            const icon = menuToggle.querySelector('i');
            if (icon) {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
        
        // Закрытие при клике на ссылки в меню
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                navMenu.classList.remove('active');
                overlay.classList.remove('active');
                document.body.classList.remove('menu-open');
                
                const icon = menuToggle.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });
        });
        
        // Закрытие при изменении размера окна
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                navMenu.classList.remove('active');
                overlay.classList.remove('active');
                document.body.classList.remove('menu-open');
                
                const icon = menuToggle.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        });
        
        // Закрытие при нажатии ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && navMenu.classList.contains('active')) {
                navMenu.classList.remove('active');
                overlay.classList.remove('active');
                document.body.classList.remove('menu-open');
                
                const icon = menuToggle.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        });
    }
    
    // Подсветка активной страницы
    const currentPage = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        }
    });
});

// Анимации при скролле
function initAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Для обычных элементов
                if (!entry.target.classList.contains('impressions-list')) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    observer.unobserve(entry.target);
                } 
                // Для списка впечатлений
                else {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    
                    // Анимируем пункты списка
                    const listItems = entry.target.querySelectorAll('li');
                    listItems.forEach((item, index) => {
                        setTimeout(() => {
                            item.style.opacity = '1';
                            item.style.transform = 'translateY(0)';
                        }, 100 + index * 100);
                    });
                    
                    observer.unobserve(entry.target);
                }
            }
        });
    }, observerOptions);

    // Назначаем анимации элементам
    document.querySelectorAll('.service-card, .rule-card, .feature').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(el);
    });
    
    // Обрабатываем список впечатлений
    const impressionsList = document.querySelector('.impressions-list');
    if (impressionsList) {
        impressionsList.style.opacity = '0';
        impressionsList.style.transform = 'translateY(20px)';
        impressionsList.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        
        // Устанавливаем начальные стили для пунктов
        const listItems = impressionsList.querySelectorAll('li');
        listItems.forEach(item => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(10px)';
            item.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        });
        
        observer.observe(impressionsList);
    }
}

// Инициализация при загрузке
document.addEventListener('DOMContentLoaded', function() {

    const logoIcon = document.querySelector('.logo-icon');
    
    if (logoIcon) {
        setInterval(() => {
            logoIcon.style.boxShadow = '0 0 20px rgba(255, 107, 53, 0.6), inset 0 0 15px rgba(255, 255, 255, 0.3)';
            setTimeout(() => {
                logoIcon.style.boxShadow = '0 4px 10px rgba(0, 0, 0, 0.2), inset 0 0 15px rgba(255, 255, 255, 0.2)';
            }, 300);
        }, 8000);
    }

    initAnimations();
    
    // Параллакс эффект для героя
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const hero = document.querySelector('.hero');
        if (hero) {
            hero.style.transform = `translateY(${scrolled * 0.1}px)`;
        }
    });
});




document.addEventListener('DOMContentLoaded', function() {
    // Обработка кнопок в блоке отзывов
    const addReviewBtn = document.getElementById('add-review-btn');
    const allReviewsBtn = document.getElementById('all-reviews-btn');
    
    if (addReviewBtn) {
        addReviewBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // Проверяем авторизацию
            const isLoggedIn = document.body.classList.contains('logged-in');
            
            if (isLoggedIn) {
                // Перенаправляем в личный кабинет для оставления отзыва
                window.location.href = '/my_orders.php';
            } else {
                // Показываем модальное окно с предложением войти
                if (typeof showLoginModal === 'function') {
                    showLoginModal('Чтобы оставить отзыв, пожалуйста, войдите в систему.');
                } else {
                    window.location.href = '/login.php?redirect=my_orders.php';
                }
            }
        });
    }
    
    if (allReviewsBtn) {
        allReviewsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // Показываем все отзывы (можно реализовать модальное окно или отдельную страницу)
            showAllReviewsModal();
        });
    }
});


// Функция для показа всех отзывов
function showAllReviewsModal() {
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content" style="max-width: 800px;">
            <span class="close">&times;</span>
            <h2 style="color: var(--primary-color); margin-bottom: 2rem;">Все отзывы</h2>
            <div id="all-reviews-container" style="max-height: 400px; overflow-y: auto; padding-right: 10px;">
                <p style="text-align: center; color: #666;">Загрузка отзывов...</p>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    modal.style.display = 'block';
    
    // Закрытие модального окна
    const closeBtn = modal.querySelector('.close');
    closeBtn.onclick = function() {
        modal.remove();
    };
    
    window.onclick = function(event) {
        if (event.target === modal) {
            modal.remove();
        }
    };
    
    // Загрузка всех отзывов через AJAX
    loadAllReviews();
}

// Функция для загрузки всех отзывов
function loadAllReviews() {
    fetch('/api/get_reviews.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('all-reviews-container');
            if (data.length > 0) {
                container.innerHTML = data.map(review => `
                    <div class="review-card" style="margin-bottom: 1.5rem;">
                        <div class="review-header" style="margin-bottom: 1rem;">
                            <div class="reviewer-info">
                                <div class="reviewer-avatar" style="width: 40px; height: 40px;">
                                    ${review.full_name.charAt(0)}
                                </div>
                                <div class="reviewer-details">
                                    <h4 class="reviewer-name">${review.full_name}</h4>
                                    <div class="review-service">${review.service_name}</div>
                                </div>
                            </div>
                            <div class="review-rating">
                                <div class="stars">
                                    ${'★'.repeat(review.rating)}${'☆'.repeat(5-review.rating)}
                                </div>
                                <div class="review-date">${new Date(review.created_at).toLocaleDateString()}</div>
                            </div>
                        </div>
                        <div class="review-content">
                            <p class="review-comment">${review.comment}</p>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p style="text-align: center; color: #666;">Отзывов пока нет.</p>';
            }
        })
        .catch(error => {
            console.error('Error loading reviews:', error);
            document.getElementById('all-reviews-container').innerHTML = 
                '<p style="text-align: center; color: #dc3545;">Ошибка загрузки отзывов</p>';
        });
}