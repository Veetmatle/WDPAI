// settings.js

// Inicjalizacja po załadowaniu strony
document.addEventListener('DOMContentLoaded', function() {
    initPasswordToggles();
    initPasswordStrength();
    initPasswordMatch();
});


function initPasswordToggles() {
    const toggleButtons = document.querySelectorAll('.settings-password-toggle');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const input = document.getElementById(targetId);
            const icon = this.querySelector('.material-symbols-outlined');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility_off';
            }
        });
    });
}

function initPasswordStrength() {
    const newPasswordInput = document.getElementById('newPassword');
    if (!newPasswordInput) return;
    
    newPasswordInput.addEventListener('input', function() {
        checkPasswordStrength(this.value);
    });
}


function checkPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;

    const colors = ['var(--color-danger)', 'var(--color-warning)', '#EAB308', 'var(--color-success)'];
    const texts = ['Słabe', 'Przeciętne', 'Dobre', 'Silne'];

    for (let i = 1; i <= 4; i++) {
        const bar = document.getElementById('str' + i);
        if (bar) {
            bar.style.backgroundColor = i <= strength ? colors[strength - 1] : 'var(--color-border)';
        }
    }

    const strengthText = document.getElementById('strengthText');
    if (strengthText) {
        if (password.length > 0) {
            strengthText.textContent = texts[strength - 1] || 'Bardzo słabe';
            strengthText.style.color = colors[strength - 1] || 'var(--color-danger)';
        } else {
            strengthText.textContent = '';
        }
    }
    
    const confirmInput = document.getElementById('confirmPassword');
    if (confirmInput && confirmInput.value.length > 0) {
        checkPasswordMatch();
    }
}


function initPasswordMatch() {
    const confirmInput = document.getElementById('confirmPassword');
    if (!confirmInput) return;
    
    confirmInput.addEventListener('input', checkPasswordMatch);
}


function checkPasswordMatch() {
    const newPass = document.getElementById('newPassword');
    const confirmPass = document.getElementById('confirmPassword');
    const matchText = document.getElementById('matchText');

    if (!newPass || !confirmPass || !matchText) return;

    if (confirmPass.value.length > 0) {
        matchText.classList.remove('hidden');
        if (newPass.value === confirmPass.value) {
            matchText.textContent = 'Hasła są zgodne';
            matchText.className = 'settings-match-text success';
        } else {
            matchText.textContent = 'Hasła nie są zgodne';
            matchText.className = 'settings-match-text danger';
        }
    } else {
        matchText.classList.add('hidden');
    }
}