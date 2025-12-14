// Скрипты для личного кабинета
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация рейтинга
    initRatingStars();
    
    // Инициализация модального окна
    initModal();
    
    // Обработчик отправки отзыва
    const reviewForm = document.getElementById('reviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', submitReview);
        
        // Счетчик символов
        const commentField = document.getElementById('comment');
        const charCount = document.getElementById('charCount');
        
        commentField.addEventListener('input', function() {
            charCount.textContent = this.value.length;
            
            if (this.value.length > 500) {
                this.value = this.value.substring(0, 500);
                charCount.textContent = 500;
            }
        });
    }
    
    // Обработчики кнопок отмены
    initCancelButtons();
    
    // Анимация появления
    initAnimations();
});

function initAnimations() {
    // Проверяем, не мобильное ли устройство
    const isMobile = window.innerWidth <= 768;
    
    if (isMobile) {
        // На мобильных отключаем сложные анимации
        document.querySelectorAll('.order-card').forEach(card => {
            card.style.opacity = '1';
            card.style.transform = 'none';
            card.style.transition = 'none';
        });
        return;
    }
    
    // На десктопе оставляем анимации
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '50px'
    });

    document.querySelectorAll('.order-card').forEach(card => {
        observer.observe(card);
    });
}

function initRatingStars() {
    const ratingTexts = [
        'Очень плохо',
        'Плохо',
        'Нормально',
        'Хорошо',
        'Отлично!'
    ];

    const stars = document.querySelectorAll('.star-container');
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const value = parseInt(this.dataset.value);
            setRating(value);
            document.getElementById('ratingText').textContent = ratingTexts[value - 1];
        });
        
        star.addEventListener('mouseover', function() {
            const value = parseInt(this.dataset.value);
            highlightStars(value);
            document.getElementById('ratingText').textContent = ratingTexts[value - 1];
        });
        
        star.addEventListener('mouseout', function() {
            const currentRating = parseInt(document.getElementById('rating').value) || 0;
            highlightStars(currentRating);
            const text = currentRating > 0 ? ratingTexts[currentRating - 1] : 'Выберите оценку';
            document.getElementById('ratingText').textContent = text;
        });
    });
}

function initModal() {
    // Закрытие модального окна
    const closeButtons = document.querySelectorAll('.close, .close-modal');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('reviewModal').style.display = 'none';
        });
    });
    
    // Закрытие при клике вне окна
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('reviewModal');
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Закрытие при нажатии ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('reviewModal');
            if (modal && modal.style.display === 'block') {
                modal.style.display = 'none';
            }
        }
    });
}

function initCancelButtons() {
    document.querySelectorAll('.btn-cancel').forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            if (confirm('Вы уверены, что хотите отменить эту заявку?')) {
                cancelOrder(orderId);
            }
        });
    });
}

function setRating(value) {
    document.getElementById('rating').value = value;
    highlightStars(value);
}

function highlightStars(value) {
    const stars = document.querySelectorAll('.star-container');
    stars.forEach(star => {
        const starValue = parseInt(star.dataset.value);
        if (starValue <= value) {
            star.classList.add('active');
            star.querySelector('.star').style.fill = '#ffc107';
        } else {
            star.classList.remove('active');
            star.querySelector('.star').style.fill = '#e0e0e0';
        }
    });
}

function showReviewForm(orderId) {
    // Сброс формы
    document.getElementById('reviewForm').reset();
    document.getElementById('review_order_id').value = orderId;
    document.getElementById('charCount').textContent = '0';
    document.getElementById('ratingText').textContent = 'Выберите оценку';
    setRating(0);
    
    // Показ модального окна
    const modal = document.getElementById('reviewModal');
    modal.style.display = 'block';
    modal.style.animation = 'fadeIn 0.3s ease';
    
    // Фокус на первой звезде
    setTimeout(() => {
        const stars = document.querySelectorAll('.star-container');
        if (stars.length > 0) {
            stars[0].focus();
        }
    }, 100);
}

function cancelOrder(orderId) {
    showLoading(true, 'Отмена заявки...');
    
    fetch('/api/cancel_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ order_id: orderId })
    })
    .then(response => response.json())
    .then(data => {
        showLoading(false);
        
        if (data.success) {
            showNotification('Заявка успешно отменена', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Ошибка: ' + data.error, 'error');
        }
    })
    .catch(error => {
        showLoading(false);
        console.error('Error:', error);
        showNotification('Ошибка при отмене заявки', 'error');
    });
}

function submitReview(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const orderId = document.getElementById('review_order_id').value;
    
    if (!orderId) {
        showNotification('Ошибка: не указан ID заявки', 'error');
        return;
    }
    
    const rating = formData.get('rating');
    if (!rating || rating < 1 || rating > 5) {
        showNotification('Пожалуйста, выберите оценку', 'error');
        return;
    }
    
    formData.append('order_id', orderId);
    
    // Показываем индикатор загрузки
    showLoading(true, 'Отправка отзыва...');
    
    fetch('/api/submit_review.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                throw new Error('Сервер вернул не JSON: ' + text.substring(0, 100));
            });
        }
        return response.json();
    })
    .then(data => {
        showLoading(false);
        
        if (data.success) {
            showNotification('Отзыв успешно отправлен! Спасибо за ваше мнение!', 'success');
            const modal = document.getElementById('reviewModal');
            modal.style.display = 'none';
            
            // Обновляем страницу через 1.5 секунды
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Ошибка: ' + data.error, 'error');
        }
    })
    .catch(error => {
        showLoading(false);
        console.error('Error:', error);
        showNotification('Ошибка при отправке отзыва: ' + error.message, 'error');
    });
}

function showLoading(show, message = 'Загрузка...') {
    let loader = document.querySelector('.loading-overlay');
    
    if (show) {
        if (!loader) {
            loader = document.createElement('div');
            loader.className = 'loading-overlay';
            loader.innerHTML = `
                <div class="loader">
                    <div class="spinner"></div>
                    <p>${message}</p>
                </div>
            `;
            loader.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.95);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
                backdrop-filter: blur(5px);
            `;
            
            const spinnerStyle = document.createElement('style');
            spinnerStyle.textContent = `
                .loader {
                    text-align: center;
                    animation: fadeIn 0.3s ease;
                }
                
                .loader .spinner {
                    border: 4px solid rgba(255, 107, 53, 0.1);
                    border-top: 4px solid var(--primary-color);
                    border-radius: 50%;
                    width: 60px;
                    height: 60px;
                    animation: spin 1s linear infinite;
                    margin-bottom: 1.5rem;
                    box-shadow: 0 4px 15px rgba(255, 107, 53, 0.2);
                }
                
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                
                .loader p {
                    color: var(--primary-color);
                    font-weight: 600;
                    font-size: 1.2rem;
                    margin: 0;
                }
            `;
            document.head.appendChild(spinnerStyle);
            document.body.appendChild(loader);
        }
        loader.style.display = 'flex';
    } else if (loader) {
        loader.style.display = 'none';
    }
}

function showNotification(message, type = 'info') {
    // Удаляем старое уведомление
    const oldNotification = document.querySelector('.cabinet-notification');
    if (oldNotification) {
        oldNotification.remove();
    }
    
    // Создаем новое уведомление
    const notification = document.createElement('div');
    notification.className = `cabinet-notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Добавляем стили
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Удаляем уведомление через 4 секунды
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 4000);
}

// Добавляем стили для уведомлений
const notificationStyle = document.createElement('style');
notificationStyle.textContent = `
    .cabinet-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        color: white;
        font-weight: 500;
        z-index: 10001;
        transform: translateX(150%);
        transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        max-width: 400px;
        word-wrap: break-word;
    }
    
    .cabinet-notification.show {
        transform: translateX(0);
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }
    
    .cabinet-notification i {
        font-size: 1.2rem;
    }
    
    .notification-success {
        background: linear-gradient(135deg, #00b894, #55efc4);
        border-left: 4px solid #00a085;
    }
    
    .notification-error {
        background: linear-gradient(135deg, #d63031, #ff7675);
        border-left: 4px solid #c62828;
    }
    
    .notification-info {
        background: linear-gradient(135deg, #0984e3, #74b9ff);
        border-left: 4px solid #086ccc;
    }
`;
document.head.appendChild(notificationStyle);

// Экспорт функций для глобального использования
window.showReviewForm = showReviewForm;



window.addEventListener('resize', function() {
    initAnimations();
});