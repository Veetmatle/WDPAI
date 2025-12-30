document.addEventListener('DOMContentLoaded', function() {
    initRegisterForm();
    initPasswordStrength();
    initPasswordToggles();
});

function initRegisterForm() {
    const form = document.querySelector('.register-form');
    if (!form) return;
    
    const nameInput = form.querySelector('input[name="name"]');
    const surnameInput = form.querySelector('input[name="surname"]');
    const emailInput = form.querySelector('input[name="email"]');
    const passwordInput = form.querySelector('input[name="password"]');
    const confirmInput = form.querySelector('input[name="password_confirm"]');
    
    if (nameInput) {
        nameInput.addEventListener('blur', function() {
            validateNameField(this, 'Imię');
        });
    }
    
    if (surnameInput) {
        surnameInput.addEventListener('blur', function() {
            validateNameField(this, 'Nazwisko');
        });
    }
    
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            validateEmailField(this);
        });
    }
    
    if (confirmInput && passwordInput) {
        confirmInput.addEventListener('input', function() {
            validatePasswordMatch(passwordInput, confirmInput);
        });
    }
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        if (nameInput && !validateNameField(nameInput, 'Imię')) {
            isValid = false;
        }
        
        if (surnameInput && !validateNameField(surnameInput, 'Nazwisko')) {
            isValid = false;
        }
        
        if (emailInput && !validateEmailField(emailInput)) {
            isValid = false;
        }
        
        if (passwordInput && passwordInput.value.length < 8) {
            showFieldError(passwordInput, 'Hasło musi mieć min. 8 znaków');
            isValid = false;
        }
        
        if (confirmInput && !validatePasswordMatch(passwordInput, confirmInput)) {
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
}

function initPasswordStrength() {
    const passwordInput = document.querySelector('input[name="password"]');
    const strengthContainer = document.getElementById('passwordStrength');
    const strengthBars = document.querySelectorAll('.register-strength-bar');
    const passwordHint = document.getElementById('passwordHint');
    
    if (!passwordInput || !strengthContainer || !strengthBars.length) return;
    
    passwordInput.addEventListener('input', function() {
        const value = this.value;
        
        if (value.length > 0) {
            strengthContainer.classList.remove('hidden');
        } else {
            strengthContainer.classList.add('hidden');
        }
        
        const strength = checkPasswordStrength(value);
        
        strengthBars.forEach(function(bar, index) {
            if (index < strength.score) {
                bar.style.backgroundColor = strength.color;
            } else {
                bar.style.backgroundColor = '#E5E7EB';
            }
        });
        
        if (passwordHint) {
            passwordHint.textContent = strength.label;
            passwordHint.style.color = strength.color;
        }
    });
}

function initPasswordToggles() {
    document.querySelectorAll('.register-password-toggle').forEach(function(toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            const input = this.closest('.register-input-wrapper').querySelector('input');
            const icon = this.querySelector('.material-symbols-outlined');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility';
            }
        });
    });
}

function checkPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z\d]/.test(password)) strength++;
    
    return {
        score: strength,
        label: strength <= 1 ? 'Słabe' : strength <= 3 ? 'Średnie' : 'Silne',
        color: strength <= 1 ? '#EF4444' : strength <= 3 ? '#F59E0B' : '#10B981'
    };
}

function validateNameField(input, fieldName) {
    const value = input.value.trim();
    
    if (!value) {
        showFieldError(input, `${fieldName} jest wymagane`);
        return false;
    }
    
    if (value.length < 2) {
        showFieldError(input, `${fieldName} musi mieć min. 2 znaki`);
        return false;
    }
    
    const nameRegex = /^[a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ\s\-]+$/;
    if (!nameRegex.test(value)) {
        showFieldError(input, `${fieldName} może zawierać tylko litery, spacje i myślniki`);
        return false;
    }
    
    clearFieldError(input);
    return true;
}

function validateEmailField(input) {
    const value = input.value.trim();
    
    if (!value) {
        showFieldError(input, 'Email jest wymagany');
        return false;
    }
    
    if (!validateEmail(value)) {
        showFieldError(input, 'Nieprawidłowy format email');
        return false;
    }
    
    clearFieldError(input);
    return true;
}

function validatePasswordMatch(passwordInput, confirmInput) {
    if (confirmInput.value !== passwordInput.value) {
        showFieldError(confirmInput, 'Hasła nie są takie same');
        return false;
    }
    
    clearFieldError(confirmInput);
    return true;
}

function showFieldError(input, message) {
    clearFieldError(input);
    
    const group = input.closest('.register-input-group');
    if (group) {
        group.classList.add('has-error');
        
        const errorEl = document.createElement('span');
        errorEl.className = 'register-field-error';
        errorEl.textContent = message;
        group.appendChild(errorEl);
    }
}

function clearFieldError(input) {
    const group = input.closest('.register-input-group');
    if (group) {
        group.classList.remove('has-error');
        
        const existingError = group.querySelector('.register-field-error');
        if (existingError) {
            existingError.remove();
        }
    }
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}