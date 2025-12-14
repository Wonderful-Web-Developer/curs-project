// Скрипты для страницы создания заявки
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация расчета стоимости
    initPriceCalculation();
    
    // Обработчик изменения тарифов
    document.querySelectorAll('input[name="tariff_id"]').forEach(radio => {
        radio.addEventListener('change', updatePriceCalculation);
    });
    
    // Обработчики изменения количества людей
    document.getElementById('people_count').addEventListener('input', updatePriceCalculation);
    document.getElementById('children_under_3').addEventListener('input', updatePriceCalculation);
    
    // Ограничение даты (только будущие даты)
    const dateInput = document.getElementById('desired_date');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
        dateInput.value = today;
    }
    
    // Ограничение времени (в рабочие часы)
    const timeInput = document.getElementById('desired_time');
    if (timeInput) {
        timeInput.min = '10:00';
        timeInput.max = '21:00';
        timeInput.value = '14:00';
    }
    
    // Валидация формы
    const orderForm = document.getElementById('orderForm');
    if (orderForm) {
        orderForm.addEventListener('submit', validateOrderForm);
    }
    
    // Инициализация начального расчета
    updatePriceCalculation();
});

function initPriceCalculation() {
    // Преобразуем тарифы из глобальной переменной (должна быть определена в create_order.php)
    window.tariffs = window.tariffs || [];
}

function updatePriceCalculation() {
    const selectedTariff = document.querySelector('input[name="tariff_id"]:checked');
    const peopleCount = parseInt(document.getElementById('people_count').value) || 0;
    const childrenCount = parseInt(document.getElementById('children_under_3').value) || 0;
    
    // Валидация количества людей
    if (peopleCount < 1) {
        document.getElementById('people_count').value = 1;
        return;
    }
    
    if (peopleCount > 10) {
        document.getElementById('people_count').value = 10;
        showNotification('Максимальное количество человек: 10', 'warning');
    }
    
    if (childrenCount > 5) {
        document.getElementById('children_under_3').value = 5;
        showNotification('Максимальное количество детей до 3 лет: 5', 'warning');
    }
    
    if (selectedTariff && window.tariffs) {
        const tariffId = parseInt(selectedTariff.value);
        const tariff = window.tariffs.find(t => t.id == tariffId);
        
        if (tariff) {
            const pricePerPerson = parseFloat(tariff.price_per_person);
            const totalPrice = pricePerPerson * peopleCount;
            
            // Обновляем отображение
            document.getElementById('pricePerPerson').textContent = pricePerPerson.toFixed(2);
            document.getElementById('peopleCountDisplay').textContent = peopleCount;
            document.getElementById('childrenCount').textContent = childrenCount;
            document.getElementById('totalPrice').textContent = totalPrice.toFixed(2);
            
            // Анимация изменения цены
            animatePriceChange(totalPrice);
        }
    }
}

function animatePriceChange(newPrice) {
    const totalPriceElement = document.getElementById('totalPrice');
    const oldPrice = parseFloat(totalPriceElement.textContent) || 0;
    
    if (oldPrice !== newPrice) {
        totalPriceElement.style.color = '#28a745';
        totalPriceElement.style.transform = 'scale(1.1)';
        
        setTimeout(() => {
            totalPriceElement.style.color = '';
            totalPriceElement.style.transform = '';
        }, 500);
    }
}

function validateOrderForm(e) {
    let isValid = true;
    const errors = [];
    
    // Проверка выбора тарифа
    const selectedTariff = document.querySelector('input[name="tariff_id"]:checked');
    if (!selectedTariff) {
        errors.push('Выберите тариф');
        isValid = false;
    }
    
    // Проверка количества людей
    const peopleCount = parseInt(document.getElementById('people_count').value) || 0;
    if (peopleCount < 1 || peopleCount > 10) {
        errors.push('Количество человек должно быть от 1 до 10');
        isValid = false;
    }
    
    // Проверка количества детей
    const childrenCount = parseInt(document.getElementById('children_under_3').value) || 0;
    if (childrenCount < 0 || childrenCount > 5) {
        errors.push('Количество детей до 3 лет должно быть от 0 до 5');
        isValid = false;
    }
    
    // Проверка даты
    const desiredDate = document.getElementById('desired_date').value;
    if (!desiredDate) {
        errors.push('Выберите дату посещения');
        isValid = false;
    } else {
        const selectedDate = new Date(desiredDate);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate < today) {
            errors.push('Дата посещения не может быть в прошлом');
            isValid = false;
        }
    }
    
    // Проверка времени
    const desiredTime = document.getElementById('desired_time').value;
    if (!desiredTime) {
        errors.push('Выберите время посещения');
        isValid = false;
    }
    
    // Проверка способа оплаты
    const paymentMethod = document.getElementById('payment_method_id');
    if (!paymentMethod.value) {
        errors.push('Выберите способ оплаты');
        isValid = false;
    }
    
    if (!isValid) {
        e.preventDefault();
        
        // Показываем ошибки
        showNotification(errors.join('<br>'), 'error');
        
        // Прокрутка к первой ошибке
        if (errors.length > 0) {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    }
}

function showNotification(message, type = 'info') {
    // Проверяем, есть ли уже уведомление
    const existingNotification = document.querySelector('.order-notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Создаем элемент уведомления
    const notification = document.createElement('div');
    notification.className = `order-notification notification-${type}`;
    notification.innerHTML = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 10000;
        animation: slideInRight 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        max-width: 400px;
        word-wrap: break-word;
    `;
    
    if (type === 'success') {
        notification.style.backgroundColor = '#28a745';
    } else if (type === 'error') {
        notification.style.backgroundColor = '#dc3545';
    } else if (type === 'warning') {
        notification.style.backgroundColor = '#ffc107';
        notification.style.color = '#212529';
    } else {
        notification.style.backgroundColor = '#17a2b8';
    }
    
    document.body.appendChild(notification);
    
    // Удаляем уведомление через 5 секунд
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 5000);
}

// Добавляем стили для анимации
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .has-error {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1) !important;
    }
`;
document.head.appendChild(style);