
document.addEventListener('DOMContentLoaded', function() {
    initAdminPage();
});

function initAdminPage() {
    // Инициализация кнопок изменения статуса
    initStatusButtons();
    
    // Инициализация модального окна
    initModal();
    
    // Инициализация фильтров и поиска
    initFilters();
    initSearch();
    
    // Инициализация горизонтального скролла мышью
    initTableDragScroll();
    
    // Инициализация сортировки таблицы
    initAdminSorting();
    
    // Запускаем автообновление
    startAutoRefresh();
}

// ============ СОРТИРОВКА ТАБЛИЦЫ ============
let currentSort = {
    column: null,
    direction: 'asc'
};

function initAdminSorting() {
    const sortButtons = document.querySelectorAll('.sort-btn');
    if (sortButtons.length === 0) return;
    
    sortButtons.forEach(button => {
        button.addEventListener('click', function() {
            const sortType = this.getAttribute('data-sort');
            
            // Если нажимаем на тот же столбец, меняем направление
            if (currentSort.column === sortType) {
                currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort.column = sortType;
                currentSort.direction = 'asc';
            }
            
            sortAdminTable(sortType, currentSort.direction);
            
            // Обновляем иконки
            sortButtons.forEach(btn => {
                const icon = btn.querySelector('i');
                icon.className = 'fas fa-sort';
                
                if (btn.getAttribute('data-sort') === currentSort.column) {
                    icon.className = currentSort.direction === 'asc' 
                        ? 'fas fa-sort-up' 
                        : 'fas fa-sort-down';
                }
            });
        });
    });
}

function sortAdminTable(sortType, direction) {
    const rows = Array.from(document.querySelectorAll('.order-row'));
    const tbody = document.querySelector('.orders-table tbody');
    
    rows.sort((a, b) => {
        let aValue, bValue;
        
        switch(sortType) {
            case 'id':
                aValue = parseInt(a.getAttribute('data-id'));
                bValue = parseInt(b.getAttribute('data-id'));
                break;
                
            case 'user':
                aValue = a.getAttribute('data-user').toLowerCase();
                bValue = b.getAttribute('data-user').toLowerCase();
                break;
                
            case 'service':
                aValue = a.getAttribute('data-service').toLowerCase();
                bValue = b.getAttribute('data-service').toLowerCase();
                break;
                
            case 'created':
                aValue = parseInt(a.getAttribute('data-created'));
                bValue = parseInt(b.getAttribute('data-created'));
                break;
                
            case 'visit':
                aValue = parseInt(a.getAttribute('data-visit'));
                bValue = parseInt(b.getAttribute('data-visit'));
                break;
                
            case 'price':
                aValue = parseFloat(a.getAttribute('data-price'));
                bValue = parseFloat(b.getAttribute('data-price'));
                break;
                
            default:
                return 0;
        }
        
        let comparison = 0;
        if (typeof aValue === 'string') {
            comparison = aValue.localeCompare(bValue);
        } else {
            comparison = aValue - bValue;
        }
        
        return direction === 'asc' ? comparison : -comparison;
    });
    
    // Перемещаем строки в новом порядке
    rows.forEach(row => {
        tbody.appendChild(row);
    });
}

// ============ ОСТАЛЬНОЙ КОД ============
function initTableDragScroll() {
    const tableContainer = document.querySelector('.admin-orders-table');
    if (!tableContainer) return;
    
    let isDragging = false;
    let startX;
    let scrollLeft;
    let startTime;
    
    // Только для десктопных устройств
    if (window.innerWidth < 768) return;
    
    tableContainer.addEventListener('mousedown', (e) => {
        // Проверяем, что нажата левая кнопка мыши
        if (e.button !== 0) return;
        
        // Не активируем перетаскивание для кликабельных элементов
        if (e.target.closest('button') || 
            e.target.closest('a') || 
            e.target.closest('input') || 
            e.target.closest('select')) {
            return;
        }
        
        isDragging = true;
        startX = e.pageX - tableContainer.offsetLeft;
        scrollLeft = tableContainer.scrollLeft;
        startTime = Date.now();
        
        // Изменяем курсор
        tableContainer.style.cursor = 'grabbing';
        tableContainer.style.userSelect = 'none';
        
        e.preventDefault();
    });
    
    tableContainer.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        
        const x = e.pageX - tableContainer.offsetLeft;
        const walk = (x - startX) * 1.5; // Скорость прокрутки
        tableContainer.scrollLeft = scrollLeft - walk;
        
        // Предотвращаем выделение текста при перетаскивании
        e.preventDefault();
    });
    
    tableContainer.addEventListener('mouseup', (e) => {
        if (!isDragging) return;
        
        // Проверяем, был ли это клик или перетаскивание
        const clickDuration = Date.now() - startTime;
        const moveDistance = Math.abs(e.pageX - (startX + tableContainer.offsetLeft));
        
        // Если было короткое нажатие и небольшое движение - это клик
        if (clickDuration < 200 && moveDistance < 5) {
            // Восстанавливаем стандартное поведение для клика
            tableContainer.style.cursor = '';
            tableContainer.style.userSelect = '';
            isDragging = false;
            return;
        }
        
        // Плавное завершение скролла
        tableContainer.style.cursor = '';
        tableContainer.style.userSelect = '';
        isDragging = false;
        
        e.preventDefault();
    });
    
    tableContainer.addEventListener('mouseleave', () => {
        if (isDragging) {
            tableContainer.style.cursor = '';
            tableContainer.style.userSelect = '';
            isDragging = false;
        }
    });
    
    // Останавливаем перетаскивание при отпускании кнопки мыши в любом месте документа
    document.addEventListener('mouseup', () => {
        if (isDragging) {
            tableContainer.style.cursor = '';
            tableContainer.style.userSelect = '';
            isDragging = false;
        }
    });
    
    // Добавляем визуальный индикатор возможности перетаскивания
    tableContainer.style.cursor = 'grab';
}

function initStatusButtons() {
    // Используем делегирование событий для динамически созданных кнопок
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-edit-status') || 
            e.target.closest('.btn-edit-status')) {
            const button = e.target.classList.contains('btn-edit-status') ? 
                          e.target : e.target.closest('.btn-edit-status');
            
            const orderId = button.getAttribute('data-order-id');
            const statusId = button.getAttribute('data-status-id');
            const adminNotes = button.getAttribute('data-admin-notes') || '';
            
            if (orderId && statusId) {
                openEditModal(orderId, statusId, adminNotes);
            }
        }
    });
}

function initModal() {
    // Кнопка закрытия модального окна
    const closeBtn = document.querySelector('#editModal .close');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            document.getElementById('editModal').style.display = 'none';
        });
    }
    
    // Закрытие модального окна при клике вне его
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('editModal');
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Закрытие при нажатии ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('editModal');
            if (modal && modal.style.display === 'block') {
                modal.style.display = 'none';
            }
        }
    });
    
    // Обработчик отправки формы
    const editForm = document.getElementById('editForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitStatusChange();
        });
    }
    
    // Кнопка отмены в модальном окне
    const closeModalBtn = document.querySelector('#editModal .close-modal');
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
            document.getElementById('editModal').style.display = 'none';
        });
    }
}

function initFilters() {
    const filterForm = document.querySelector('.filter-form');
    if (!filterForm) return;
    
    // Кнопка сброса фильтров
    const resetBtn = filterForm.querySelector('.btn-secondary');
    if (resetBtn) {
        resetBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = '/admin.php';
        });
    }
}

function initSearch() {
    const searchInput = document.querySelector('input[name="search"]');
    if (!searchInput) return;
    
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            if (this.value.length >= 3 || this.value.length === 0) {
                this.form.submit();
            }
        }, 500);
    });
}

function openEditModal(orderId, statusId, adminNotes) {
    document.getElementById('edit_order_id').value = orderId;
    document.getElementById('status_id').value = statusId;
    document.getElementById('admin_notes').value = adminNotes;
    
    const modal = document.getElementById('editModal');
    modal.style.display = 'block';
    
    // Фокус на поле статуса
    setTimeout(() => {
        document.getElementById('status_id').focus();
    }, 100);
}

function submitStatusChange() {
    const orderId = document.getElementById('edit_order_id').value;
    const statusId = document.getElementById('status_id').value;
    const adminNotes = document.getElementById('admin_notes').value;
    
    if (!orderId || !statusId) {
        showNotification('Заполните все обязательные поля', 'error');
        return;
    }
    
    // Показываем индикатор загрузки
    showLoading(true, 'Сохранение изменений...');
    
    fetch('/api/update_order_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            order_id: orderId,
            status_id: statusId,
            admin_notes: adminNotes
        })
    })
    .then(response => response.json())
    .then(data => {
        showLoading(false);
        
        if (data.success) {
            showNotification('Статус заявки успешно обновлен', 'success');
            
            // Закрываем модальное окно
            document.getElementById('editModal').style.display = 'none';
            
            // Обновляем строку в таблице без перезагрузки страницы
            updateOrderRow(orderId, data.status_id, data.status_name, adminNotes);
            
        } else {
            showNotification('Ошибка: ' + data.error, 'error');
        }
    })
    .catch(error => {
        showLoading(false);
        console.error('Error:', error);
        showNotification('Ошибка при обновлении статуса', 'error');
    });
}




function updateStatistics(statusName, action) {
    const statusStats = {
        'Новая': '.summary-card:nth-child(2) h3',
        'В обработке': null, // Добавьте карточку для этого статуса если нужно
        'Выполнена': '.summary-card:nth-child(3) h3',
        'Отменена': '.summary-card:nth-child(4) h3'
    };
    
    const selector = statusStats[statusName];
    if (!selector) return;
    
    const statElement = document.querySelector(selector);
    if (statElement) {
        let currentCount = parseInt(statElement.textContent);
        
        if (action === 'increment') {
            // Увеличиваем счетчик текущего статуса
            currentCount++;
            statElement.textContent = currentCount;
            
            // Уменьшаем счетчик предыдущего статуса
            const oldStatus = statElement.parentElement.parentElement.querySelector('.status-badge');
            if (oldStatus && oldStatus.textContent !== statusName) {
                const oldStatusName = oldStatus.textContent;
                const oldSelector = statusStats[oldStatusName];
                if (oldSelector) {
                    const oldStatElement = document.querySelector(oldSelector);
                    if (oldStatElement) {
                        let oldCount = parseInt(oldStatElement.textContent);
                        if (oldCount > 0) {
                            oldCount--;
                            oldStatElement.textContent = oldCount;
                        }
                    }
                }
            }
        }
    }
}




function updateOrderRow(orderId, statusId, statusName, adminNotes) {
    // Находим строку с заявкой
    const row = document.querySelector(`tr[data-id="${orderId}"]`);
    if (!row) {
        console.error('Row not found for order:', orderId);
        return;
    }
    
    // Обновляем статус в ячейке
    const statusCell = row.querySelector('.status-badge');
    if (statusCell) {
        statusCell.textContent = statusName;
        // Обновляем класс статуса
        statusCell.className = `status-badge status-${statusName}`;
        
        // Обновляем data-атрибут строки
        row.setAttribute('data-status', statusName);
    }
    
    // Обновляем кнопку
    const editBtn = row.querySelector('.btn-edit-status');
    if (editBtn) {
        editBtn.setAttribute('data-status-id', statusId);
        editBtn.setAttribute('data-admin-notes', adminNotes);
    }
    
    // Обновляем статистику на странице без перезагрузки
    updateStatistics(statusName, 'increment');
    
    // Показываем анимацию обновления
    row.style.backgroundColor = '#e8f5e9';
    setTimeout(() => {
        row.style.backgroundColor = '';
    }, 1000);
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
                background: rgba(255, 255, 255, 0.9);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 10000;
            `;
            
            const spinnerStyle = document.createElement('style');
            spinnerStyle.textContent = `
                .loader .spinner {
                    border: 5px solid #f3f3f3;
                    border-top: 5px solid var(--primary-color);
                    border-radius: 50%;
                    width: 50px;
                    height: 50px;
                    animation: spin 1s linear infinite;
                    margin-bottom: 1rem;
                }
                
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                
                .loader p {
                    color: var(--primary-color);
                    font-weight: 600;
                    font-size: 1.1rem;
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
    const oldNotification = document.querySelector('.notification');
    if (oldNotification) {
        oldNotification.remove();
    }
    
    // Создаем новое уведомление
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 10001;
        animation: slideInRight 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    
    if (type === 'success') {
        notification.style.backgroundColor = '#28a745';
    } else if (type === 'error') {
        notification.style.backgroundColor = '#dc3545';
    } else {
        notification.style.backgroundColor = '#17a2b8';
    }
    
    document.body.appendChild(notification);
    
    // Удаляем уведомление через 3 секунды
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Автоматическое обновление таблицы каждые 30 секунд
let autoRefreshInterval;
function startAutoRefresh(interval = 30000) {
    if (autoRefreshInterval) clearInterval(autoRefreshInterval);
    autoRefreshInterval = setInterval(() => {
        if (!document.querySelector('.modal[style*="block"]')) {
            window.location.reload();
        }
    }, interval);
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}

// Добавляем стили для анимации уведомлений
if (!document.querySelector('#notification-styles')) {
    const style = document.createElement('style');
    style.id = 'notification-styles';
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
    `;
    document.head.appendChild(style);
}
