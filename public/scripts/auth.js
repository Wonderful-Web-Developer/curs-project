// Скрипты для страниц авторизации и регистрации
document.addEventListener('DOMContentLoaded', function() {
    // Валидация формы регистрации
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        setupRegisterFormValidation();
    }
    
    // Валидация формы авторизации
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        setupLoginFormValidation();
    }
    
    // Индикатор сложности пароля
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        setupPasswordStrengthIndicator();
    }
});

function setupRegisterFormValidation() {
    const form = document.getElementById('registerForm');
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Проверка email
        const email = document.getElementById('email');
        const emailError = document.getElementById('email-error');
        if (!validateEmail(email.value)) {
            showError(emailError, 'Введите корректный email');
            isValid = false;
        } else {
            hideError(emailError);
        }
        
        // Проверка пароля
        const password = document.getElementById('password');
        const passwordError = document.getElementById('password-error');
        if (password.value.length < 8) {
            showError(passwordError, 'Пароль должен содержать минимум 8 символов');
            isValid = false;
        } else {
            hideError(passwordError);
        }
        
        // Проверка подтверждения пароля
        const confirmPassword = document.getElementById('confirm_password');
        const confirmPasswordError = document.getElementById('confirm-password-error');
        if (password.value !== confirmPassword.value) {
            showError(confirmPasswordError, 'Пароли не совпадают');
            isValid = false;
        } else {
            hideError(confirmPasswordError);
        }
        
        // Проверка ФИО
        const fullName = document.getElementById('full_name');
        const fullNameError = document.getElementById('full-name-error');
        if (!validateRussianName(fullName.value)) {
            showError(fullNameError, 'ФИО должно содержать только кириллицу и пробелы');
            isValid = false;
        } else {
            hideError(fullNameError);
        }
        
        // Проверка телефона
        const phone = document.getElementById('phone');
        const phoneError = document.getElementById('phone-error');
        if (!validatePhone(phone.value)) {
            showError(phoneError, 'Введите телефон в формате 8(XXX)XXX-XX-XX или +7(XXX)XXX-XX-XX');
            isValid = false;
        } else {
            hideError(phoneError);
        }
        
        // Проверка даты рождения
        const birthDate = document.getElementById('birth_date');
        const birthDateError = document.getElementById('birth-date-error');
        if (!birthDate.value) {
            showError(birthDateError, 'Введите дату рождения');
            isValid = false;
        } else {
            const birthDateObj = new Date(birthDate.value);
            const today = new Date();
            const minDate = new Date();
            minDate.setFullYear(today.getFullYear() - 100);
            
            if (birthDateObj > today || birthDateObj < minDate) {
                showError(birthDateError, 'Введите корректную дату рождения');
                isValid = false;
            } else {
                hideError(birthDateError);
            }
        }
        
        if (!isValid) {
            e.preventDefault();
            // Прокрутка к первой ошибке
            const firstError = form.querySelector('.error-message:not([style*="display: none"])');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
}



function setupLoginFormValidation() {
    const form = document.getElementById('loginForm');
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        const login = document.getElementById('login');
        const password = document.getElementById('password');
        
        if (!login.value.trim()) {
            showInlineError(login, 'Введите email');
            isValid = false;
        } else {
            clearInlineError(login);
        }
        
        if (!password.value) {
            showInlineError(password, 'Введите пароль');
            isValid = false;
        } else {
            clearInlineError(password);
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
}

function setupPasswordStrengthIndicator() {
    const passwordInput = document.getElementById('password');
    const strengthBar = document.getElementById('password-strength');
    
    if (!passwordInput || !strengthBar) return;
    
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        
        // Проверка длины
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        
        // Проверка наличия цифр
        if (/\d/.test(password)) strength++;
        
        // Проверка наличия букв в разных регистрах
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        
        // Проверка наличия специальных символов
        if (/[^a-zA-Z0-9]/.test(password)) strength++;
        
        // Обновление индикатора
        strengthBar.className = 'password-strength';
        
        if (strength === 0) {
            strengthBar.innerHTML = '';
        } else if (strength <= 2) {
            strengthBar.innerHTML = '<div class="strength-weak"></div>';
            strengthBar.className += ' strength-weak';
        } else if (strength <= 4) {
            strengthBar.innerHTML = '<div class="strength-medium"></div>';
            strengthBar.className += ' strength-medium';
        } else {
            strengthBar.innerHTML = '<div class="strength-strong"></div>';
            strengthBar.className += ' strength-strong';
        }
    });
}

// Вспомогательные функции
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validateRussianName(name) {
    const re = /^[А-Яа-яЁё\s]+$/;
    return re.test(name);
}

function validatePhone(phone) {
    const re = /^(8|\+7)\(\d{3}\)\d{3}-\d{2}-\d{2}$/;
    return re.test(phone);
}

function showError(element, message) {
    if (element) {
        element.textContent = message;
        element.style.display = 'block';
        element.parentElement.classList.add('has-error');
    }
}

function hideError(element) {
    if (element) {
        element.style.display = 'none';
        element.parentElement.classList.remove('has-error');
    }
}

function showInlineError(input, message) {
    input.style.borderColor = '#dc3545';
    input.style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.1)';
    
    // Создаем или обновляем сообщение об ошибке
    let errorElement = input.nextElementSibling;
    if (!errorElement || !errorElement.classList.contains('inline-error')) {
        errorElement = document.createElement('div');
        errorElement.className = 'inline-error';
        errorElement.style.color = '#dc3545';
        errorElement.style.fontSize = '0.9rem';
        errorElement.style.marginTop = '0.25rem';
        input.parentNode.insertBefore(errorElement, input.nextSibling);
    }
    
    errorElement.textContent = message;
}

function clearInlineError(input) {
    input.style.borderColor = '';
    input.style.boxShadow = '';
    
    const errorElement = input.nextElementSibling;
    if (errorElement && errorElement.classList.contains('inline-error')) {
        errorElement.remove();
    }
}