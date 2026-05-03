// MedHistory - Login Page Functionality
// API Base URL - ajustar según entorno
const API_BASE = '/backend/src/controllers';

document.addEventListener('DOMContentLoaded', function () {

    // Elements
    const roleRadios = document.querySelectorAll('input[name="role"]');
    const loginForm = document.getElementById('loginForm');
    const userRoleInput = document.getElementById('userRole');
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeOpen = document.getElementById('eyeOpen');
    const eyeClosed = document.getElementById('eyeClosed');
    const emailInput = document.getElementById('email');
    const rememberCheckbox = document.getElementById('remember');

    // Status message elements
    const successMessage = document.getElementById('success-message');
    const errorMessage = document.getElementById('error-message');
    const emailError = document.getElementById('email-error');
    const passwordError = document.getElementById('password-error');

    let selectedRole = 'paciente'; // Default role
    let passwordVisible = false;

    // Initialize
    init();

    function init() {
        setupRoleSelection();
        setupPasswordToggle();
        setupFormValidation();
        setupFormSubmission();
        loadRememberedCredentials();
    }

    // Role Selection Handler
    function setupRoleSelection() {
        roleRadios.forEach(radio => {
            radio.addEventListener('change', function () {
                if (this.checked) {
                    selectedRole = this.value;
                    userRoleInput.value = this.value;
                }
            });
        });
    }

    // Password Toggle Functionality
    function setupPasswordToggle() {
        if (togglePasswordBtn && passwordInput) {
            togglePasswordBtn.addEventListener('click', function (e) {
                e.preventDefault();
                passwordVisible = !passwordVisible;

                if (passwordVisible) {
                    passwordInput.type = 'text';
                    eyeOpen.classList.add('hidden');
                    eyeClosed.classList.remove('hidden');
                } else {
                    passwordInput.type = 'password';
                    eyeOpen.classList.remove('hidden');
                    eyeClosed.classList.add('hidden');
                }
            });
        }
    }

    // Form Validation
    function setupFormValidation() {
        // Real-time email validation
        emailInput.addEventListener('blur', function () {
            validateEmail();
        });

        emailInput.addEventListener('input', function () {
            clearFieldError(emailInput, emailError);
        });

        // Real-time password validation
        passwordInput.addEventListener('input', function () {
            clearFieldError(passwordInput, passwordError);
        });
    }

    // Form Submission
    function setupFormSubmission() {
        loginForm.addEventListener('submit', function (e) {
            e.preventDefault();

            // Clear previous messages
            clearAllMessages();

            // Validate form
            if (validateForm()) {
                performLogin();
            }
        });
    }

    // Validation Functions
    function validateForm() {
        let isValid = true;

        // Validate email
        if (!validateEmail()) {
            isValid = false;
        }

        // Validate password
        if (!validatePassword()) {
            isValid = false;
        }

        return isValid;
    }

    function validateEmail() {
        const email = emailInput.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!email) {
            showFieldError(emailInput, emailError, 'El correo electrónico es obligatorio');
            return false;
        }

        if (!emailRegex.test(email)) {
            showFieldError(emailInput, emailError, 'Introduce un correo electrónico válido');
            return false;
        }

        clearFieldError(emailInput, emailError);
        return true;
    }

    function validatePassword() {
        const password = passwordInput.value;

        if (!password) {
            showFieldError(passwordInput, passwordError, 'La contraseña es obligatoria');
            return false;
        }

        if (password.length < 6) {
            showFieldError(passwordInput, passwordError, 'La contraseña debe tener al menos 6 caracteres');
            return false;
        }

        clearFieldError(passwordInput, passwordError);
        return true;
    }

    // Login Process
    async function performLogin() {
        const submitBtn = loginForm.querySelector('button[type="submit"]');
        const loginText = document.getElementById('login-text');
        const loginSpinner = document.getElementById('login-spinner');
        const selectedRoleRadio = document.querySelector('input[name="role"]:checked');
        const selectedRoleActual = selectedRoleRadio ? selectedRoleRadio.value : 'paciente';

        // Show loading state
        submitBtn.disabled = true;
        loginText.classList.add('hidden');
        loginSpinner.classList.remove('hidden');

        // Collect form data
        const loginData = {
            email: emailInput.value.trim(),
            password: passwordInput.value,
            role: selectedRoleActual,
            remember: rememberCheckbox.checked
        };

        try {
            // Llamada real al backend
            const response = await fetch(`${API_BASE}/AuthController.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(loginData)
            });

            const result = await response.json();

            if (result.success) {
                handleLoginSuccess(result, loginData.remember);
            } else {
                handleLoginError(result.mensaje || 'Credenciales incorrectas', result.campo || '');
            }
        } catch (error) {
            console.error('Error de conexión:', error);
            handleLoginError('Error de conexión con el servidor. Verifica tu conexión e intenta de nuevo.');
        }
    }

    function handleLoginSuccess(result, remember) {
        // Save credentials if remember is checked
        if (remember) {
            saveCredentials({
                email: result.usuario.email,
                role: result.usuario.tipo
            });
        } else {
            clearSavedCredentials();
        }

        // Show success message
        showSuccessMessage(`¡Bienvenido de vuelta, ${result.usuario.nombre}! Redirigiendo...`);

        // Reset button state
        resetSubmitButton();

        // Redirect after success message
        setTimeout(() => {
            window.location.href = result.redirect;
        }, 1500);
    }

    function handleLoginError(message, campo) {
        if (campo === 'email') {
            showFieldError(emailInput, emailError, message);
            resetSubmitButton();
            return;
        }

        if (campo === 'password') {
            showFieldError(passwordInput, passwordError, message);
            resetSubmitButton();
            return;
        }

        showErrorMessage(message);
        resetSubmitButton();
    }

    // UI Helper Functions
    function showFieldError(input, errorDiv, message) {
        input.classList.add('border-red-500', 'bg-red-50');
        input.classList.remove('border-gray-300');
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }

    function clearFieldError(input, errorDiv) {
        input.classList.remove('border-red-500', 'bg-red-50');
        input.classList.add('border-gray-300');
        errorDiv.textContent = '';
        errorDiv.style.display = 'none';
    }

    function showSuccessMessage(message) {
        successMessage.textContent = message;
        successMessage.style.display = 'block';
        successMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function showErrorMessage(message) {
        errorMessage.textContent = message;
        errorMessage.style.display = 'block';
        errorMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function clearAllMessages() {
        successMessage.style.display = 'none';
        errorMessage.style.display = 'none';
    }

    function resetSubmitButton() {
        const submitBtn = loginForm.querySelector('button[type="submit"]');
        const loginText = document.getElementById('login-text');
        const loginSpinner = document.getElementById('login-spinner');

        submitBtn.disabled = false;
        loginText.classList.remove('hidden');
        loginSpinner.classList.add('hidden');
    }

    // Credential Management
    function saveCredentials(data) {
        try {
            localStorage.setItem('medhistory_remember', JSON.stringify({
                email: data.email,
                role: data.role,
                timestamp: Date.now()
            }));
        } catch (error) {
            console.warn('Could not save credentials:', error);
        }
    }

    function loadRememberedCredentials() {
        try {
            const saved = localStorage.getItem('medhistory_remember');
            if (saved) {
                const data = JSON.parse(saved);

                // Check if saved data is not too old (30 days max)
                const thirtyDaysAgo = Date.now() - (30 * 24 * 60 * 60 * 1000);
                if (data.timestamp > thirtyDaysAgo) {
                    emailInput.value = data.email;
                    rememberCheckbox.checked = true;

                    // Set role
                    if (data.role) {
                        selectedRole = data.role;
                        userRoleInput.value = data.role;

                        // Check the appropriate radio button
                        roleRadios.forEach(radio => {
                            radio.checked = radio.value === data.role;
                        });
                    }
                } else {
                    clearSavedCredentials();
                }
            }
        } catch (error) {
            console.warn('Could not load saved credentials:', error);
        }
    }

    function clearSavedCredentials() {
        try {
            localStorage.removeItem('medhistory_remember');
        } catch (error) {
            console.warn('Could not clear saved credentials:', error);
        }
    }

    // Notification System
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-xl shadow-lg z-50 transform translate-x-full transition-transform duration-300 max-w-sm ${type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
                'bg-blue-500 text-white'
            }`;
        notification.textContent = message;

        document.body.appendChild(notification);

        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);

        // Auto remove after 4 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 4000);
    }

    // Focus animations (solo para inputs de formulario, no para radio buttons)
    document.addEventListener('focus', function (e) {
        if (e.target.matches('input:not([type="checkbox"]):not([type="radio"])')) {
            const formGroup = e.target.closest('.form-group');
            if (formGroup) {
                formGroup.classList.add('scale-[1.02]');
            }
        }
    }, true);

    document.addEventListener('blur', function (e) {
        if (e.target.matches('input:not([type="checkbox"]):not([type="radio"])')) {
            const formGroup = e.target.closest('.form-group');
            if (formGroup) {
                formGroup.classList.remove('scale-[1.02]');
            }
        }
    }, true);

    // Keyboard shortcuts
    document.addEventListener('keydown', function (e) {
        // Escape key to clear messages
        if (e.key === 'Escape') {
            clearAllMessages();
        }
    });
});

// Demo helper for testing
window.fillDemoCredentials = function () {
    document.getElementById('email').value = 'test@test.com';
    document.getElementById('password').value = 'test123';
    console.log('Demo credentials filled. Click login to test.');
};
