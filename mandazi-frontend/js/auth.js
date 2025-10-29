// Clear all form fields on page load
document.addEventListener('DOMContentLoaded', async function() {
    // Clear login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) loginForm.reset();
    
    // Clear registration form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) registerForm.reset();
    
    // Clear any alerts
    document.getElementById('alert-container').innerHTML = '';
    document.getElementById('register-alert-container').innerHTML = '';
    
    // Check backend connection
    setTimeout(async () => {
        const isConnected = await config.showConnectionStatus();
        if (!isConnected) {
            showAlert('⚠️ Backend server not running. Please start the Laravel server on port 8001.', 'warning', 'alert-container');
        }
    }, 1000);
});

// Check if already logged in
if (localStorage.getItem('token')) {
    const user = JSON.parse(localStorage.getItem('user'));
    if (user.role === 'seller') {
        window.location.href = 'seller-dashboard.html';
    } else {
        window.location.href = 'buyer-dashboard.html';
    }
}

// Login functionality
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const login = document.getElementById('login').value;
    const password = document.getElementById('password').value;
    const loginBtn = document.getElementById('loginBtn');
    
    loginBtn.disabled = true;
    loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Logging in...';
    
    try {
        const requestData = { login, password };
        console.log('Sending login request:', requestData);
        
        const response = await fetch(`${config.apiUrl}/login`, {
            method: 'POST',
            headers: config.getHeaders(false),
            body: JSON.stringify(requestData)
        });
        
        console.log('Response status:', response.status);
        
        const data = await response.json();
        console.log('Response data:', data);
        
        if (response.ok) {
            localStorage.setItem('token', data.access_token);
            localStorage.setItem('user', JSON.stringify(data.user));
            
            showAlert('Login successful! Redirecting...', 'success', 'alert-container');
            
            setTimeout(() => {
                if (data.user.role === 'seller') {
                    window.location.href = 'seller-dashboard.html';
                } else {
                    window.location.href = 'buyer-dashboard.html';
                }
            }, 1000);
        } else {
            const errorMessage = data.message || 
                               (data.errors ? Object.values(data.errors).flat().join(', ') : 'Invalid credentials');
            showAlert(errorMessage, 'danger', 'alert-container');
            loginBtn.disabled = false;
            loginBtn.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Login';
        }
    } catch (error) {
        showAlert('Network error. Please check if backend is running on http://localhost:8001', 'danger', 'alert-container');
        loginBtn.disabled = false;
        loginBtn.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Login';
    }
});

// Registration functionality
document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const name = document.getElementById('regName').value;
    const email = document.getElementById('regEmail').value;
    const username = document.getElementById('regUsername').value;
    const password = document.getElementById('regPassword').value;
    const passwordConfirm = document.getElementById('regPasswordConfirm').value;
    const role = document.getElementById('regRole').value;
    const registerBtn = document.getElementById('registerBtn');
    
    // Validation
    if (password !== passwordConfirm) {
        showAlert('Passwords do not match!', 'danger', 'register-alert-container');
        return;
    }
    
    if (password.length < 6) {
        showAlert('Password must be at least 6 characters long!', 'danger', 'register-alert-container');
        return;
    }
    
    if (!role) {
        showAlert('Please select whether you want to buy or sell mandazis!', 'danger', 'register-alert-container');
        return;
    }
    
    registerBtn.disabled = true;
    registerBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Account...';
    
    try {
        const response = await fetch(`${config.apiUrl}/register`, {
            method: 'POST',
            headers: config.getHeaders(false),
            body: JSON.stringify({ 
                name, 
                email, 
                username,
                password, 
                password_confirmation: passwordConfirm,
                role 
            })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            showAlert('Account created successfully! Please login.', 'success', 'register-alert-container');
            
            // Clear form and switch to login
            setTimeout(() => {
                document.getElementById('registerForm').reset();
                showLogin();
            }, 2000);
        } else {
            const errorMessage = data.message || 
                               (data.errors ? Object.values(data.errors).flat().join(', ') : 'Registration failed');
            showAlert(errorMessage, 'danger', 'register-alert-container');
        }
    } catch (error) {
        showAlert('Network error. Please check if backend is running on http://localhost:8001', 'danger', 'register-alert-container');
    } finally {
        registerBtn.disabled = false;
        registerBtn.innerHTML = '<i class="fas fa-user-plus me-2"></i>Create Account';
    }
});

function showAlert(message, type, containerId) {
    const alertContainer = document.getElementById(containerId);
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    alertContainer.innerHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
}