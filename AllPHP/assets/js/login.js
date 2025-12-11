document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.querySelector('input[name="email"]');
    const passwordInput = document.querySelector('input[name="password"]');
    const rememberCheckbox = document.getElementById('remember');
    const loginButton = document.querySelector('.login-button');
    const video = document.getElementById('bgVideo');
if (video) {
        video.addEventListener('loadedmetadata', () => {
            video.currentTime = video.duration / 2;
        });
    }
if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            emailInput.classList.remove('error');
            passwordInput.classList.remove('error');

            let hasError = false;
if (!emailInput.value.trim()) {
                showInputError(emailInput, 'Email is required');
                hasError = true;
            } else if (!isValidEmail(emailInput.value)) {
                showInputError(emailInput, 'Please enter a valid email address');
                hasError = true;
            }
if (!passwordInput.value) {
                showInputError(passwordInput, 'Password is required');
                hasError = true;
            } else if (passwordInput.value.length < 8) {
                showInputError(passwordInput, 'Password must be at least 8 characters');
                hasError = true;
            }
if (hasError) {
                e.preventDefault();
                return false;
            }
loginButton.textContent = 'LOGGING IN...';
            loginButton.disabled = true;
            loginButton.style.opacity = '0.7';
            loginButton.style.cursor = 'wait';
        });
    }
if (emailInput) {
        emailInput.addEventListener('input', () => {
            clearInputError(emailInput);
        });

        emailInput.addEventListener('blur', () => {
            if (emailInput.value.trim() && !isValidEmail(emailInput.value)) {
                showInputError(emailInput, 'Please enter a valid email address');
            }
        });
    }

    if (passwordInput) {
        passwordInput.addEventListener('input', () => {
            clearInputError(passwordInput);
        });
passwordInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                loginForm.dispatchEvent(new Event('submit'));
            }
        });
    }
const flashMessage = document.querySelector('.flash-message');
    if (flashMessage) {
        setTimeout(() => {
            flashMessage.style.transition = 'opacity 0.5s ease';
            flashMessage.style.opacity = '0';
            setTimeout(() => {
                flashMessage.remove();
            }, 500);
        }, 5000);
    }
});

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function showInputError(input, message) {
    input.classList.add('error');
    const existingError = input.parentElement.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    errorDiv.style.color = '#dc3545';
    errorDiv.style.fontSize = '0.85rem';
    errorDiv.style.marginTop = '5px';
    errorDiv.style.animation = 'fadeIn 0.3s ease';
    
    input.parentElement.appendChild(errorDiv);
    input.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function clearInputError(input) {
    input.classList.remove('error');
    
    const errorMessage = input.parentElement.querySelector('.error-message');
    if (errorMessage) {
        errorMessage.remove();
    }
}

const style = document.createElement('style');
style.textContent = `
    input.error {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);