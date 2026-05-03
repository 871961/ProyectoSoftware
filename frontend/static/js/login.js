// MedHistory - Login Page Functionality
// API Base URL - ajustar segun entorno
const API_BASE = '/backend/src/controllers';

document.addEventListener('DOMContentLoaded', function () {
    const roleRadios = document.querySelectorAll('input[name="role"]');
    const loginForm = document.getElementById('loginForm');
    const userRoleInput = document.getElementById('userRole');
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeOpen = document.getElementById('eyeOpen');
    const eyeClosed = document.getElementById('eyeClosed');
    const emailInput = document.getElementById('email');
    const rememberCheckbox = document.getElementById('remember');
    const successMessage = document.getElementById('success-message');
    const errorMessage = document.getElementById('error-message');
    const emailError = document.getElementById('email-error');
    const passwordError = document.getElementById('password-error');

    let selectedRole = 'paciente';
    let passwordVisible = false;

    init();

    function init() {
        setupRoleSelection();
        setupPasswordToggle();
        setupFormValidation();
        setupFormSubmission();
        loadRememberedCredentials();
        clearAllMessages();
    }

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

    function setupPasswordToggle() {
        if (!togglePasswordBtn || !passwordInput) {
            return;
        }

        togglePasswordBtn.addEventListener('click', function (e) {
            e.preventDefault();
            passwordVisible = !passwordVisible;

            if (passwordVisible) {
                passwordInput.type = 'text';
                eyeOpen.style.display = 'none';
                eyeClosed.style.display = 'inline-block';
            } else {
                passwordInput.type = 'password';
                eyeOpen.style.display = 'inline-block';
                eyeClosed.style.display = 'none';
            }
        });
    }

    function setupFormValidation() {
        emailInput.addEventListener('blur', function () {
            validateEmail();
        });

        emailInput.addEventListener('input', function () {
            clearFieldError(emailInput, emailError);
        });

        passwordInput.addEventListener('input', function () {
            clearFieldError(passwordInput, passwordError);
        });
    }

    function setupFormSubmission() {
        loginForm.addEventListener('submit', function (e) {
            e.preventDefault();
            clearAllMessages();

            if (validateForm()) {
                performLogin();
            }
        });
    }

    function validateForm() {
        let isValid = true;

        if (!validateEmail()) {
            isValid = false;
        }

        if (!validatePassword()) {
            isValid = false;
        }

        return isValid;
    }

    function validateEmail() {
        const email = emailInput.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!email) {
            showFieldError(emailInput, emailError, 'El correo electronico es obligatorio');
            return false;
        }

        if (!emailRegex.test(email)) {
            showFieldError(emailInput, emailError, 'Introduce un correo electronico valido');
            return false;
        }

        clearFieldError(emailInput, emailError);
        return true;
    }

    function validatePassword() {
        const password = passwordInput.value;

        if (!password) {
            showFieldError(passwordInput, passwordError, 'La contrasena es obligatoria');
            return false;
        }

        if (password.length < 6) {
            showFieldError(passwordInput, passwordError, 'La contrasena debe tener al menos 6 caracteres');
            return false;
        }

        clearFieldError(passwordInput, passwordError);
        return true;
    }

    async function performLogin() {
        const submitBtn = loginForm.querySelector('button[type="submit"]');
        const loginText = document.getElementById('login-text');
        const loginSpinner = document.getElementById('login-spinner');

        submitBtn.disabled = true;
        loginText.style.display = 'none';
        loginSpinner.style.display = 'inline-flex';

        const loginData = {
            email: emailInput.value.trim(),
            password: passwordInput.value,
            role: selectedRole,
            remember: rememberCheckbox.checked
        };

        try {
            const response = await fetch(`${API_BASE}/AuthController.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(loginData)
            });

            const result = await response.json();

            if (result.success) {
                handleLoginSuccess(result, loginData.remember);
            } else {
                handleLoginError(result.mensaje || 'Credenciales incorrectas', result.campo || null);
            }
        } catch (error) {
            console.error('Error de conexion:', error);
            handleLoginError('Error de conexion con el servidor. Verifica tu conexion e intenta de nuevo.');
        }
    }

    function handleLoginSuccess(result, remember) {
        if (remember) {
            saveCredentials({
                email: result.usuario.email,
                role: result.usuario.tipo
            });
        } else {
            clearSavedCredentials();
        }

        showSuccessMessage(`Bienvenido de vuelta, ${result.usuario.nombre}. Redirigiendo...`);
        resetSubmitButton();

        setTimeout(() => {
            window.location.href = result.redirect;
        }, 1500);
    }

    function handleLoginError(message, field = null) {
        if (field === 'email') {
            showFieldError(emailInput, emailError, message);
            emailInput.focus();
        } else if (field === 'password') {
            showFieldError(passwordInput, passwordError, message);
            passwordInput.focus();
        } else {
            showErrorMessage(message);
        }

        resetSubmitButton();
    }

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
        successMessage.textContent = '';
        successMessage.style.display = 'none';
        errorMessage.textContent = '';
        errorMessage.style.display = 'none';
        clearFieldError(emailInput, emailError);
        clearFieldError(passwordInput, passwordError);
    }

    function resetSubmitButton() {
        const submitBtn = loginForm.querySelector('button[type="submit"]');
        const loginText = document.getElementById('login-text');
        const loginSpinner = document.getElementById('login-spinner');

        submitBtn.disabled = false;
        loginText.style.display = '';
        loginSpinner.style.display = 'none';
    }

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
            if (!saved) {
                return;
            }

            const data = JSON.parse(saved);
            const thirtyDaysAgo = Date.now() - (30 * 24 * 60 * 60 * 1000);

            if (data.timestamp > thirtyDaysAgo) {
                emailInput.value = data.email;
                rememberCheckbox.checked = true;

                if (data.role) {
                    selectedRole = data.role;
                    userRoleInput.value = data.role;

                    roleRadios.forEach(radio => {
                        radio.checked = radio.value === data.role;
                    });
                }
            } else {
                clearSavedCredentials();
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

    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-xl shadow-lg z-50 transform translate-x-full transition-transform duration-300 max-w-sm ${type === 'success'
            ? 'bg-green-500 text-white'
            : type === 'error'
                ? 'bg-red-500 text-white'
                : 'bg-blue-500 text-white'
        }`;
        notification.textContent = message;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);

        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 4000);
    }

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

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            clearAllMessages();
        }
    });
});

window.fillDemoCredentials = function () {
    document.getElementById('email').value = 'test@test.com';
    document.getElementById('password').value = 'test123';
    console.log('Demo credentials filled. Click login to test.');
};
