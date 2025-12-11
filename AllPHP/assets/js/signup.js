document.addEventListener('DOMContentLoaded', () => {
    const signupForm = document.getElementById('signupForm');
    const nameInput = document.getElementById('name');
    const usernameInput = document.getElementById('username');
    const dobInput = document.getElementById('dob');
    const phoneInput = document.getElementById('phone');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmpassword');
    const termsCheckbox = document.getElementById('terms');
    const signupButton = document.getElementById('signup');
    const video = document.getElementById('bgVideo');
    
    if (video) {
        video.addEventListener('loadedmetadata', () => {
            video.currentTime = video.duration / 2;
        });
    }
    
    const today = new Date();
    const maxDate = new Date(today.getFullYear() - 13, today.getMonth(), today.getDate());
    dobInput.max = maxDate.toISOString().split('T')[0];
    
    if (signupForm) {
        signupForm.addEventListener('submit', (e) => {
            clearAllErrors();
            
            let hasError = false;
            if (!nameInput.value.trim()) {
                showInputError(nameInput, 'Name is required');
                hasError = true;
            } else if (nameInput.value.trim().length < 3) {
                showInputError(nameInput, 'Name must be at least 3 characters');
                hasError = true;
            }
            if (!usernameInput.value.trim()) {
                showInputError(usernameInput, 'Username is required');
                hasError = true;
            } else if (usernameInput.value.trim().length < 3 || usernameInput.value.trim().length > 20) {
                showInputError(usernameInput, 'Username must be 3-20 characters');
                hasError = true;
            } else if (!/^[a-zA-Z0-9_]+$/.test(usernameInput.value.trim())) {
                showInputError(usernameInput, 'Username can only contain letters, numbers, and underscores');
                hasError = true;
            }
            if (!dobInput.value) {
                showInputError(dobInput, 'Date of birth is required');
                hasError = true;
            } else {
                const birthDate = new Date(dobInput.value);
                const age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                
                if (age < 13) {
                    showInputError(dobInput, 'You must be at least 13 years old');
                    hasError = true;
                } else if (age > 120) {
                    showInputError(dobInput, 'Please enter a valid date of birth');
                    hasError = true;
                }
            }
            if (!phoneInput.value.trim()) {
                showInputError(phoneInput, 'Phone number is required');
                hasError = true;
            } else {
                const phoneDigits = phoneInput.value.replace(/\D/g, '');
                if (phoneDigits.length < 10 || phoneDigits.length > 15) {
                    showInputError(phoneInput, 'Phone must be 10-15 digits');
                    hasError = true;
                }
            }
            if (!emailInput.value.trim()) {
                showInputError(emailInput, 'Email is required');
                hasError = true;
            } else if (!isValidEmail(emailInput.value)) {
                showInputError(emailInput, 'Please enter a valid email');
                hasError = true;
            }
            if (!passwordInput.value) {
                showInputError(passwordInput, 'Password is required');
                hasError = true;
            } else if (passwordInput.value.length < 8) {
                showInputError(passwordInput, 'Password must be at least 8 characters');
                hasError = true;
            } else if (!isValidPassword(passwordInput.value)) {
                showInputError(passwordInput, 'Password must contain uppercase, lowercase, and numbers');
                hasError = true;
            }
            if (!confirmPasswordInput.value) {
                showInputError(confirmPasswordInput, 'Please confirm your password');
                hasError = true;
            } else if (passwordInput.value !== confirmPasswordInput.value) {
                showInputError(confirmPasswordInput, 'Passwords do not match');
                hasError = true;
            }
            if (!termsCheckbox.checked) {
                const termsLabel = termsCheckbox.parentElement.querySelector('label');
                if (termsLabel) {
                    termsLabel.style.color = '#dc3545';
                    termsLabel.style.fontWeight = 'bold';
                }
                showGeneralError('You must agree to the Terms of Service and Privacy Policy');
                hasError = true;
            }
            
            if (hasError) {
                e.preventDefault();
                return false;
            }
            signupButton.textContent = 'CREATING ACCOUNT...';
            signupButton.disabled = true;
            signupButton.style.opacity = '0.7';
            signupButton.style.cursor = 'wait';
        });
    }
    
    [nameInput, usernameInput, dobInput, phoneInput, emailInput, passwordInput, confirmPasswordInput].forEach(input => {
        if (input) {
            input.addEventListener('input', () => {
                clearInputError(input);
                if (input === termsCheckbox) {
                    const termsLabel = termsCheckbox.parentElement.querySelector('label');
                    if (termsLabel) {
                        termsLabel.style.color = '';
                        termsLabel.style.fontWeight = '';
                    }
                }
            });
        }
    });
    if (usernameInput) {
        usernameInput.addEventListener('blur', () => {
            const username = usernameInput.value.trim();
            if (username && username.length >= 3 && username.length <= 20) {
                if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                    showInputError(usernameInput, 'Username can only contain letters, numbers, and underscores');
                }
            }
        });
    }
    if (passwordInput) {
        passwordInput.addEventListener('input', () => {
            clearInputError(passwordInput);
            clearInputError(confirmPasswordInput);
        });
    }
    
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', () => {
            clearInputError(confirmPasswordInput);
            if (passwordInput.value && confirmPasswordInput.value && passwordInput.value !== confirmPasswordInput.value) {
                showInputError(confirmPasswordInput, 'Passwords do not match');
            }
        });
    }
});

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidPassword(password) {
    const hasUpper = /[A-Z]/.test(password);
    const hasLower = /[a-z]/.test(password);
    const hasNumber = /\d/.test(password);
    return hasUpper && hasLower && hasNumber;
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
    if (!isElementInViewport(input)) {
        input.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function showGeneralError(message) {
    const existingError = document.querySelector('.general-error');
    if (existingError) {
        existingError.remove();
    }
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'general-error flash-message error';
    errorDiv.textContent = message;
    errorDiv.style.marginTop = '1rem';
    errorDiv.style.animation = 'fadeIn 0.3s ease';
    
    const form = document.getElementById('signupForm');
    const firstFormGroup = form.querySelector('.form-group');
    if (firstFormGroup) {
        form.insertBefore(errorDiv, firstFormGroup);
    } else {
        form.prepend(errorDiv);
    }
}

function clearInputError(input) {
    input.classList.remove('error');
    const errorMessage = input.parentElement.querySelector('.error-message');
    if (errorMessage) {
        errorMessage.remove();
    }
}

function clearAllErrors() {
    document.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
    document.querySelectorAll('.error-message').forEach(el => el.remove());
    document.querySelectorAll('.general-error').forEach(el => el.remove());
}

function isElementInViewport(el) {
    const rect = el.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
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