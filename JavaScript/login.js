const button = document.getElementById('button');

button.addEventListener('click', (e) => {
    e.preventDefault();

    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    const remember = document.getElementById('remember').checked;
    const error = document.getElementById('error');
    
    error.textContent = '';
    error.style.color = 'red';
    error.style.marginTop = '1rem';
    error.style.fontSize = '0.9rem';

    if (!username) {
        showError(error, 'Username is required');
        return;
    }

    if (username.length < 3 || username.length > 20) {
        showError(error, 'Username must be between 3-20 characters');
        return;
    }

    if (!password) {
        showError(error, 'Password is required');
        return;
    }

    if (password.length < 8 || password.length > 20) {
        showError(error, 'Password must be between 8-20 characters');
        return;
    }
    const isValidUser = authenticateUser(username, password);

    if (!isValidUser) {
        showError(error, 'Invalid username or password');
        document.getElementById('password').value = '';
        return;
    }
    if (remember) {
        localStorage.setItem('rememberedUser', username);
        localStorage.setItem('rememberMe', 'true');
    } else {
        localStorage.removeItem('rememberedUser');
        localStorage.removeItem('rememberMe');
    }

    sessionStorage.setItem('currentUser', username);
    sessionStorage.setItem('isLoggedIn', 'true');
    sessionStorage.setItem('loginTime', new Date().toISOString());

    error.style.color = 'green';
    error.textContent = 'Login successful! Redirecting...';
    button.disabled = true;
    button.style.opacity = '0.6';
    button.style.cursor = 'not-allowed';

    console.log('Login successful:', {
        username,
        rememberMe: remember,
        timestamp: new Date().toISOString()
    });

    setTimeout(() => {
        window.location.href = 'model.html';
    }, 1500);
});

function showError(errorElement, message) {
    errorElement.style.color = 'red';
    errorElement.textContent = message;
    errorElement.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function authenticateUser(username, password) {
    return true;
}

window.addEventListener('DOMContentLoaded', () => {
    const rememberMe = localStorage.getItem('rememberMe');
    const rememberedUser = localStorage.getItem('rememberedUser');
    
    if (rememberMe === 'true' && rememberedUser) {
        document.getElementById('username').value = rememberedUser;
        document.getElementById('remember').checked = true;
    }
});
const passwordInput = document.getElementById('password');
passwordInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        button.click();
    }
});
document.getElementById('username').addEventListener('input', () => {
    document.getElementById('error').textContent = '';
});

document.getElementById('password').addEventListener('input', () => {
    document.getElementById('error').textContent = '';
});
let isSubmitting = false;
button.addEventListener('click', (e) => {
    if (isSubmitting) {
        e.preventDefault();
        return;
    }
});
