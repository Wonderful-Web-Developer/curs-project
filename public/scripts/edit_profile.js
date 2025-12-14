document.addEventListener('DOMContentLoaded', function() {
 
    const passwordToggles = document.querySelectorAll('.password-toggle');
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    
    const cancelBtn = document.querySelector('.btn-outline');
    const saveBtn = document.querySelector('.btn-primary');
    
    if (cancelBtn) {
        cancelBtn.innerHTML = '<i class="fas fa-times"></i> Отмена';
    }
    
    if (saveBtn) {
        saveBtn.innerHTML = '<i class="fas fa-save"></i> Сохранить изменения';
    }
    
    const form = document.getElementById('editProfileForm');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Сохранение...';
            submitBtn.disabled = true;
            submitBtn.classList.add('loading');
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('/api/update_profile.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification(result.message, 'success');
                    
                    updateFormData(formData);
                    
                    setTimeout(() => {
                        window.location.href = '/user_cabinet.php';
                    }, 2000);
                } else {
                    showNotification(result.message, 'error');
                    
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('loading');
                    
                    highlightErrorFields(result.message);
                }
            } catch (error) {
                console.error('Ошибка:', error);
                showNotification('Произошла ошибка при отправке формы', 'error');
                
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                submitBtn.classList.remove('loading');
            }
        });
    }
    
    initFormValidation();
    
    function showNotification(message, type = 'success') {

        const existingNotification = document.querySelector('.notification');
        if (existingNotification) {
            existingNotification.remove();
        }
        
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.style.display = 'block';
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <div class="notification-text">${message}</div>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.5s forwards';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 500);
        }, 3000);
    }
    
    function initFormValidation() {
        const inputs = form.querySelectorAll('input[required]');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                clearFieldError(this);
            });
        });
        
        const emailInput = document.getElementById('email');
        if (emailInput) {
            emailInput.addEventListener('blur', function() {
                if (this.value && !isValidEmail(this.value)) {
                    showFieldError(this, 'Введите корректный email адрес');
                }
            });
        }
        
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function() {

                let value = this.value.replace(/\D/g, '');
                if (value.length > 0) {
                    let formatted = '';
                    
                    if (value.length <= 1) {
                        formatted = '+7 (' + value;
                    } else if (value.length <= 4) {
                        formatted = '+7 (' + value.slice(1, 4);
                    } else if (value.length <= 7) {
                        formatted = '+7 (' + value.slice(1, 4) + ') ' + value.slice(4, 7);
                    } else if (value.length <= 9) {
                        formatted = '+7 (' + value.slice(1, 4) + ') ' + value.slice(4, 7) + '-' + value.slice(7, 9);
                    } else {
                        formatted = '+7 (' + value.slice(1, 4) + ') ' + value.slice(4, 7) + '-' + value.slice(7, 9) + '-' + value.slice(9, 11);
                    }
                    
                    this.value = formatted;
                }
            });
        }
        
        const newPasswordInput = document.getElementById('new_password');
        if (newPasswordInput) {
            newPasswordInput.addEventListener('blur', function() {
                if (this.value && this.value.length < 6) {
                    showFieldError(this, 'Пароль должен содержать минимум 6 символов');
                }
            });
        }
    }
    
    function validateField(field) {
        if (field.hasAttribute('required') && !field.value.trim()) {
            showFieldError(field, 'Это поле обязательно для заполнения');
            return false;
        }
        return true;
    }
    
    function showFieldError(field, message) {
        clearFieldError(field);
        
        field.classList.add('error');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;
        errorDiv.style.cssText = `
            color: #ef4444;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            animation: fadeIn 0.3s ease;
        `;
        
        field.parentNode.appendChild(errorDiv);
    }
    
    function clearFieldError(field) {
        field.classList.remove('error');
        
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
    }
    
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    function highlightErrorFields(errorMessage) {
        
        const errors = form.querySelectorAll('.field-error');
        errors.forEach(error => error.remove());
        
        const inputs = form.querySelectorAll('input');
        inputs.forEach(input => input.classList.remove('error'));
        
        if (errorMessage.includes('email')) {
            const emailInput = document.getElementById('email');
            if (emailInput) showFieldError(emailInput, errorMessage);
        } else if (errorMessage.includes('парол')) {
            const passwordInput = document.getElementById('current_password');
            if (passwordInput) showFieldError(passwordInput, errorMessage);
        } else if (errorMessage.includes('телефон')) {
            const phoneInput = document.getElementById('phone');
            if (phoneInput) showFieldError(phoneInput, errorMessage);
        }
    }
    
    function updateFormData(formData) {
        const inputs = form.querySelectorAll('input');
        inputs.forEach(input => {
            if (input.type !== 'password') {
                input.classList.add('success');
                setTimeout(() => {
                    input.classList.remove('success');
                }, 1000);
            }
        });
    }
});