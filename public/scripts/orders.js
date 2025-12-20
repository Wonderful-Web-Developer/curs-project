// orders.js - Только отмена заявки и отзывы

document.addEventListener('DOMContentLoaded', function() {
    if (!document.querySelector('.orders-table')) return;
    
    initFilters();
    initSorting();
    initPagination();
    initOrderActions();
    initModals();
    initReviewSystem();
});

// ============ ФИЛЬТРАЦИЯ (СУПЕР ПРОСТАЯ) ============
function initFilters() {
    const statusFilter = document.getElementById('statusFilter');
    const dateFilter = document.getElementById('dateFilter');
    const searchInput = document.getElementById('searchOrders');
    const resetButton = document.querySelector('.reset-filters');
    
    if (!statusFilter || !dateFilter || !searchInput || !resetButton) return;
    
    const orderRows = document.querySelectorAll('.order-row');
    const ordersTableContainer = document.getElementById('ordersTableContainer');
    const ordersFooter = document.getElementById('ordersFooter');
    const noResultsContainer = document.getElementById('noResultsContainer');
    
    function applyFilters() {
        const status = statusFilter.value;
        const date = dateFilter.value;
        const search = searchInput.value.toLowerCase().trim();
        
        let visible = 0;
        
        orderRows.forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            const rowService = row.getAttribute('data-service').toLowerCase();
            const rowId = row.querySelector('.id-badge').textContent.toLowerCase();
            const createdTimestamp = parseInt(row.getAttribute('data-created')) * 1000;
            
            let show = true;
            
            // Фильтр по статусу
            if (status !== 'all' && rowStatus !== status) {
                show = false;
            }
            
            // Фильтр по дате
            if (show && date !== 'all') {
                const now = new Date();
                const createdDate = new Date(createdTimestamp);
                const diffTime = Math.abs(now - createdDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                
                if ((date === 'month' && diffDays > 30) ||
                    (date === '3months' && diffDays > 90) ||
                    (date === 'year' && diffDays > 365)) {
                    show = false;
                }
            }
            
            // Фильтр по поиску
            if (show && search) {
                if (!rowService.includes(search) && !rowId.includes(search)) {
                    show = false;
                }
            }
            
            row.dataset.filteredVisible = show;
            if (show) visible++;
        });
        
        // Управляем видимостью
        if (visible === 0) {
            // Нет результатов - показываем сообщение, скрываем таблицу
            if (noResultsContainer) noResultsContainer.style.display = 'block';
            if (ordersTableContainer) ordersTableContainer.style.display = 'none';
            if (ordersFooter) ordersFooter.style.display = 'none';
        } else {
            // Есть результаты - показываем таблицу, скрываем сообщение
            if (noResultsContainer) noResultsContainer.style.display = 'none';
            if (ordersTableContainer) ordersTableContainer.style.display = 'block';
            if (ordersFooter) ordersFooter.style.display = 'flex';
            
            // Обновляем пагинацию
            initPagination();
        }
        
        // Обновляем счетчики
        updateCounters(visible);
    }
    
    function updateCounters(visible) {
        const visibleCount = document.getElementById('visibleOrdersCount');
        const totalCounter = document.getElementById('totalOrdersCount');
        
        if (visibleCount) {
            const startIndex = 1;
            const endIndex = Math.min(10, visible);
            visibleCount.textContent = visible > 0 ? `${startIndex}-${endIndex}` : '0-0';
        }
        
        if (totalCounter) {
            totalCounter.textContent = visible;
        }
    }
    
    // Обработчики событий
    statusFilter.addEventListener('change', applyFilters);
    dateFilter.addEventListener('change', applyFilters);
    searchInput.addEventListener('input', function() {
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(applyFilters, 500);
    });
    
    resetButton.addEventListener('click', function() {
        statusFilter.value = 'all';
        dateFilter.value = 'all';
        searchInput.value = '';
        applyFilters();
    });
    
    // Инициализируем все строки как видимые
    orderRows.forEach(row => row.dataset.filteredVisible = 'true');
    
    // Применяем фильтры при загрузке
    applyFilters();
}

// ============ ПАГИНАЦИЯ (ПРОСТАЯ) ============
function initPagination() {
    const rowsPerPage = 10;
    let currentPage = 1;
    
    const paginationContainer = document.querySelector('.pagination');
    if (!paginationContainer) return;
    
    const prevButton = paginationContainer.querySelector('.pagination-btn.prev');
    const nextButton = paginationContainer.querySelector('.pagination-btn.next');
    const pageButtons = paginationContainer.querySelectorAll('.pagination-page');
    
    // Получаем видимые строки
    const allRows = document.querySelectorAll('.order-row');
    const visibleRows = Array.from(allRows).filter(row => row.dataset.filteredVisible === 'true');
    const rowsToPaginate = visibleRows.length > 0 ? visibleRows : allRows;
    
    // Если строк меньше 10, скрываем пагинацию
    if (rowsToPaginate.length <= rowsPerPage) {
        paginationContainer.style.display = 'none';
        
        // Показываем только видимые строки
        allRows.forEach(row => {
            row.style.display = row.dataset.filteredVisible === 'true' ? '' : 'none';
        });
        
        updateCounter(rowsToPaginate.length, currentPage, rowsPerPage);
        return;
    }
    
    // Показываем пагинацию
    paginationContainer.style.display = 'flex';
    
    // Функция для отображения страницы
    function showPage(page) {
        const startIndex = (page - 1) * rowsPerPage;
        const endIndex = startIndex + rowsPerPage;
        
        // Сначала скрываем все
        allRows.forEach(row => {
            row.style.display = 'none';
        });
        
        // Показываем видимые строки текущей страницы
        for (let i = startIndex; i < endIndex && i < rowsToPaginate.length; i++) {
            if (rowsToPaginate[i]) {
                rowsToPaginate[i].style.display = '';
            }
        }
        
        // Обновляем активные кнопки
        pageButtons.forEach(button => {
            const buttonPage = parseInt(button.textContent);
            button.classList.toggle('active', buttonPage === page);
        });
        
        // Обновляем кнопки prev/next
        const totalPages = Math.ceil(rowsToPaginate.length / rowsPerPage);
        if (prevButton) prevButton.disabled = page === 1;
        if (nextButton) nextButton.disabled = page === totalPages;
        
        // Обновляем счетчик
        updateCounter(rowsToPaginate.length, page, rowsPerPage);
        
        currentPage = page;
    }
    
    // Обработчики
    pageButtons.forEach(button => {
        button.addEventListener('click', function() {
            const pageNum = parseInt(this.textContent);
            if (!isNaN(pageNum)) {
                showPage(pageNum);
            }
        });
    });
    
    if (prevButton) {
        prevButton.addEventListener('click', function() {
            if (currentPage > 1) {
                showPage(currentPage - 1);
            }
        });
    }
    
    if (nextButton) {
        nextButton.addEventListener('click', function() {
            const totalPages = Math.ceil(rowsToPaginate.length / rowsPerPage);
            if (currentPage < totalPages) {
                showPage(currentPage + 1);
            }
        });
    }
    
    // Показываем первую страницу
    showPage(1);
}

// ============ СОРТИРОВКА ============
let currentSort = {
    column: null,
    direction: 'asc'
};

function initSorting() {
    const sortButtons = document.querySelectorAll('.sort-btn');
    
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
            
            sortTable(sortType, currentSort.direction);
            
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

function sortTable(sortType, direction) {
    const rows = Array.from(document.querySelectorAll('.order-row'));
    const tbody = document.querySelector('.orders-table tbody');
    
    rows.sort((a, b) => {
        let aValue, bValue;
        
        switch(sortType) {
            case 'id':
                aValue = parseInt(a.getAttribute('data-id'));
                bValue = parseInt(b.getAttribute('data-id'));
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
    
    // После сортировки обновляем пагинацию
    initPagination();
}


// ============ ОБНОВЛЕНИЕ СЧЕТЧИКА ============
function updateCounter(totalRows, currentPage, rowsPerPage) {
    const counter = document.getElementById('visibleOrdersCount');
    if (counter) {
        const startIndex = (currentPage - 1) * rowsPerPage + 1;
        const endIndex = Math.min(currentPage * rowsPerPage, totalRows);
        counter.textContent = `${startIndex}-${endIndex}`;
    }
}

// ============ ОСНОВНЫЕ ДЕЙСТВИЯ ============
function initOrderActions() {
    // Кнопка отмены заявки
    document.querySelectorAll('.btn-cancel').forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const orderId = this.getAttribute('data-order-id');
            cancelOrder(orderId);
        });
    });
}

// ============ МОДАЛЬНЫЕ ОКНА ============
function initModals() {
    // Модальное окно отзыва
    const reviewModal = document.getElementById('reviewModal');
    if (reviewModal) {
        const closeButtons = reviewModal.querySelectorAll('.close, .close-modal');
        
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                reviewModal.style.display = 'none';
            });
        });
        
        window.addEventListener('click', function(e) {
            if (e.target === reviewModal) {
                reviewModal.style.display = 'none';
            }
        });
    }
}

// ============ СИСТЕМА ОТЗЫВОВ ============
function initReviewSystem() {
    // Инициализация звезд рейтинга
    initStarRating();
    
    // Подсчет символов в комментарии
    const commentTextarea = document.getElementById('comment');
    if (commentTextarea) {
        const charCount = document.getElementById('charCount');
        commentTextarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }
    
    // Отправка формы отзыва
    const reviewForm = document.getElementById('reviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const orderId = document.getElementById('review_order_id').value;
            const rating = document.getElementById('rating').value;
            const comment = document.getElementById('comment').value;
            
            if (!rating) {
                alert('Пожалуйста, поставьте оценку');
                return;
            }
            
            submitReview(orderId, rating, comment);
        });
    }
}

function initStarRating() {
    const stars = document.querySelectorAll('.star-container');
    const ratingInput = document.getElementById('rating');
    const ratingText = document.getElementById('ratingText');
    
    const ratingTexts = {
        1: 'Очень плохо',
        2: 'Плохо',
        3: 'Нормально',
        4: 'Хорошо',
        5: 'Отлично'
    };
    
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const value = parseInt(this.getAttribute('data-value'));
            ratingInput.value = value;
            ratingText.textContent = ratingTexts[value] || 'Выберите оценку';
            
            // Обновляем отображение звезд
            stars.forEach((s, index) => {
                if (index < value) {
                    s.querySelector('.star').style.fill = '#ffd700';
                } else {
                    s.querySelector('.star').style.fill = '#ddd';
                }
            });
        });
        
        // Эффект при наведении
        star.addEventListener('mouseover', function() {
            const value = parseInt(this.getAttribute('data-value'));
            stars.forEach((s, index) => {
                if (index < value) {
                    s.querySelector('.star').style.fill = '#ffed85';
                }
            });
        });
        
        star.addEventListener('mouseout', function() {
            const currentRating = parseInt(ratingInput.value) || 0;
            stars.forEach((s, index) => {
                if (index < currentRating) {
                    s.querySelector('.star').style.fill = '#ffd700';
                } else {
                    s.querySelector('.star').style.fill = '#ddd';
                }
            });
        });
    });
}

// ============ ФУНКЦИЯ ОТМЕНЫ ЗАЯВКИ ============
function cancelOrder(orderId) {
    if (confirm('Вы уверены, что хотите отменить эту заявку?')) {
        showLoading(true, 'Отмена заявки...');
        
        fetch('/api/cancel_order.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ order_id: orderId })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            showLoading(false);
            
            if (data.success) {
                alert('Заявка успешно отменена');
                
                updateOrderStatus(orderId, 'Отменена');
                
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                alert('Ошибка: ' + data.error);
            }
        })
        .catch(error => {
            showLoading(false);
            console.error('Ошибка при отмене заявки:', error);
            alert('Произошла ошибка при отмене заявки. Пожалуйста, попробуйте еще раз.');
        });
    }
}

// ============ ФУНКЦИЯ ОБНОВЛЕНИЯ СТАТУСА ЗАЯВКИ ============
function updateOrderStatus(orderId, newStatus) {
    const orderRow = document.querySelector(`.order-row[data-id="${orderId}"]`);
    if (!orderRow) return;
    
    orderRow.setAttribute('data-status', newStatus);
    
    const statusBadge = orderRow.querySelector('.status-badge');
    if (statusBadge) {
        statusBadge.textContent = newStatus;
        statusBadge.className = `status-badge status-${newStatus}`;
    }
    
    const cancelButton = orderRow.querySelector('.btn-cancel');
    if (cancelButton) {
        cancelButton.remove();
    }
}

// ============ ФУНКЦИЯ ОТПРАВКИ ОТЗЫВА ============
function submitReview(orderId, rating, comment) {
    showLoading(true, 'Отправка отзыва...');
    
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('rating', rating);
    formData.append('comment', comment);
    
    fetch('/api/submit_review.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        showLoading(false);
        
        if (data.success) {
            alert('Отзыв сохранен! Спасибо за ваше мнение.');
            
            const modal = document.getElementById('reviewModal');
            if (modal) {
                modal.style.display = 'none';
            }
            
            updateReviewButton(orderId);
            
        } else {
            alert('Ошибка: ' + data.error);
        }
    })
    .catch(error => {
        showLoading(false);
        console.error('Ошибка при отправке отзыва:', error);
        alert('Произошла ошибка при отправке отзыва. Пожалуйста, попробуйте еще раз.');
    });
}

// ============ ФУНКЦИЯ ОБНОВЛЕНИЯ КНОПКИ ОТЗЫВА ============
function updateReviewButton(orderId) {
    const orderRow = document.querySelector(`.order-row[data-id="${orderId}"]`);
    if (!orderRow) return;
    
    const reviewButton = orderRow.querySelector('.btn-review');
    if (reviewButton) {
        reviewButton.innerHTML = '<i class="fas fa-check"></i>';
        reviewButton.className = 'btn-action btn-reviewed';
        reviewButton.title = 'Отзыв оставлен';
        reviewButton.disabled = true;
        reviewButton.classList.remove('pulse');
        
        reviewButton.onclick = null;
    }
}

// ============ ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ ============
function showLoading(show, message = 'Загрузка...') {
    let loader = document.getElementById('loading-overlay');
    
    if (show) {
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'loading-overlay';
            loader.innerHTML = `
                <div style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(255,255,255,0.9);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 9999;
                ">
                    <div style="text-align: center;">
                        <div style="
                            border: 4px solid #f3f3f3;
                            border-top: 4px solid var(--primary-color);
                            border-radius: 50%;
                            width: 50px;
                            height: 50px;
                            animation: spin 1s linear infinite;
                            margin: 0 auto 1rem;
                        "></div>
                        <p>${message}</p>
                    </div>
                </div>
            `;
            
            // Добавляем анимацию спиннера
            const style = document.createElement('style');
            style.textContent = `
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
            
            document.body.appendChild(loader);
        }
        loader.style.display = 'flex';
    } else if (loader) {
        loader.style.display = 'none';
    }
}

// ============ ГЛОБАЛЬНЫЕ ФУНКЦИИ ============
function showReviewForm(orderId) {
    const modal = document.getElementById('reviewModal');
    const orderIdInput = document.getElementById('review_order_id');
    
    orderIdInput.value = orderId;
    
    // Сбрасываем форму
    document.getElementById('rating').value = '';
    document.getElementById('comment').value = '';
    document.getElementById('charCount').textContent = '0';
    document.getElementById('ratingText').textContent = 'Выберите оценку';
    
    // Сбрасываем звезды
    document.querySelectorAll('.star-container .star').forEach(star => {
        star.style.fill = '#ddd';
    });
    
    modal.style.display = 'block';
}