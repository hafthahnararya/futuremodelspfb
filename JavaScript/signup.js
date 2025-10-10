const button = document.getElementById('signup');

button.addEventListener('click', (e) => {
    e.preventDefault();
    const name = document.getElementById('name').value.trim();
    const dob = document.getElementById('dob').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const confirmpassword = document.getElementById('confirmpassword').value.trim();

    const error = document.getElementById('error');
    error.textContent = '';
    error.style.color = 'red';
    error.style.marginTop = '1rem';
    error.style.fontSize = '0.9rem';

    if (name.length < 5) {
        showError(error, "Name must be at least 5 characters long");
        return;
    }

    if (!dob) {
        showError(error, "Date of birth is required");
        return;
    }
    const birthDate = new Date(dob);
    const today = new Date();
    const age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (age < 10 || (age === 10 && monthDiff < 0)) {
        showError(error, "You must be at least 10 years old to sign up");
        return;
    }
    const phoneDigits = phone.replace(/\D/g, '');
    if (phoneDigits.length < 11 || phoneDigits.length > 13) {
        showError(error, "Phone number must be between 11-13 digits");
        return;
    }

    if (username.length < 3 || username.length > 20) {
        showError(error, "Username must be between 3-20 characters");
        return;
    }
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showError(error, "Please enter a valid email address");
        return;
    }

    if (password.length < 8 || password.length > 20) {
        showError(error, "Password must be between 8-20 characters");
        return;
    }
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumber = /[0-9]/.test(password);
    
    if (!hasUpperCase || !hasLowerCase || !hasNumber) {
        showError(error, "Password must contain uppercase, lowercase, and numbers");
        return;
    }

    if (confirmpassword !== password) {
        showError(error, "Passwords do not match");
        return;
    }

    error.style.color = 'green';
    error.textContent = 'Sign up successful! Redirecting...';
    button.disabled = true;
    button.style.opacity = '0.6';
    button.style.cursor = 'not-allowed';
    const userData = {
        name,
        dob,
        phone,
        username,
        email,
        registeredAt: new Date().toISOString()
    };

    console.log('User registered:', userData);

    setTimeout(() => {
        window.location.href = 'login.html';
    }, 1500);
});
function showError(errorElement, message) {
    errorElement.style.color = 'red';
    errorElement.textContent = message;
    errorElement.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

document.getElementById('email').addEventListener('blur', function() {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (this.value && !emailRegex.test(this.value)) {
        this.style.borderBottomColor = 'red';
    } else {
        this.style.borderBottomColor = '#ddd';
    }
});

document.getElementById('confirmpassword').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    if (this.value && this.value !== password) {
        this.style.borderBottomColor = 'red';
    } else {
        this.style.borderBottomColor = '#ddd';
    }
});
