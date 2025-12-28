
// Dodaj listebnery po załadowaniu strony
document.addEventListener('DOMContentLoaded', function() {
    initLoginForm();
    initPasswordToggle();
});

// Inicjalizacja formularza logowania
function initLoginForm() {
    const form = document.querySelector('.login-form');
    if (!form) return;
    
    const emailInput = form.querySelector('input[name="email"]');
    const passwordInput = form.querySelector('input[name="password"]');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    if (emailInput) {
        // blur - walidacja po opuszczeniu pola
        emailInput.addEventListener('blur', function() {
            validateEmailField(this);
        });
    }
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        if (emailInput && !validateEmailField(emailInput)) {
            isValid = false;
        }
        
        if (passwordInput && passwordInput.value.length === 0) {
            showFieldError(passwordInput, 'Hasło jest wymagane');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
}

function initPasswordToggle() {
    const toggleBtn = document.querySelector('.login-password-toggle');
    if (!toggleBtn) return;
    
    toggleBtn.addEventListener('click', function() {
        const input = this.closest('.login-input-wrapper').querySelector('input');
        const icon = this.querySelector('.material-symbols-outlined');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.textContent = 'visibility_off';
        } else {
            input.type = 'password';
            icon.textContent = 'visibility';
        }
    });
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

function showFieldError(input, message) {
    clearFieldError(input);
    
    const group = input.closest('.login-input-group');
    if (group) {
        group.classList.add('has-error');
        
        const errorEl = document.createElement('span');
        errorEl.className = 'login-field-error';
        errorEl.textContent = message;
        group.appendChild(errorEl);
    }
}

function clearFieldError(input) {
    const group = input.closest('.login-input-group');
    if (group) {
        group.classList.remove('has-error');
        
        const existingError = group.querySelector('.login-field-error');
        if (existingError) {
            existingError.remove();
        }
    }
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}