// MedHistory - Registration Page Functionality
// API Base URL - ajustar según entorno
const API_BASE = 'http://medHistory.local/backend/src/controllers';

console.log('✓ register.js cargado');

document.addEventListener('DOMContentLoaded', function () {
    console.log('✓ DOM cargado');

    try {
        // Elements
        const registrationForm = document.getElementById('registrationForm');
        const formFields = document.getElementById('formFields');

        console.log('Elements check:', {
            registrationForm: !!registrationForm,
            formFields: !!formFields
        });

        if (!registrationForm || !formFields) {
            throw new Error('No se encontraron los elementos del formulario');
        }

        // Status message elements
        const successMessage = document.getElementById('success-message');
        const errorMessage = document.getElementById('error-message');

        let passwordVisible = false;

        // Initialize
        init();

        function init() {
            console.log('✓ Iniciando generación de formulario');
            // Mostrar formulario directamente (solo pacientes)
            generateForm();
            console.log('✓ Formulario generado');
        }

        // Generate Dynamic Form Fields
        function generateForm() {
            try {
                console.log('Generando HTML del formulario...');
                let fieldsHTML = `
                <!-- DNI/NIE Field (Primary Key) -->
                <div class="mb-6 p-5 bg-blue-50 rounded-xl border-2 border-blue-200">
                    <div class="form-group">
                        <label for="dni" class="block text-sm font-semibold text-gray-700 mb-2">
                            DNI/NIE <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="dni" name="dni" required maxlength="9"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-4 focus:ring-blue-100 focus:border-medical-blue transition-all duration-300 outline-none uppercase"
                               placeholder="12345678A"
                               pattern="[0-9XYZ][0-9]{7}[A-Z]"
                               title="Formato: 8 dígitos seguidos de una letra (ej: 12345678A)">
                        <div class="text-xs text-gray-500 mt-1">
                            <svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            Formato: 8 dígitos + letra (Ej: 12345678A). El DNI es tu identificador único en el sistema.
                        </div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="border-b border-gray-200 pb-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Información Personal</h4>
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="nombre" class="block text-sm font-semibold text-gray-700 mb-2">
                                Nombre <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="nombre" name="nombre" required
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-4 focus:ring-blue-100 focus:border-medical-blue transition-all duration-300 outline-none"
                                   placeholder="Tu nombre">
                            <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                        <div class="form-group">
                            <label for="apellidos" class="block text-sm font-semibold text-gray-700 mb-2">
                                Apellidos <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="apellidos" name="apellidos" required
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-4 focus:ring-blue-100 focus:border-medical-blue transition-all duration-300 outline-none"
                                   placeholder="Tus apellidos">
                            <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <label for="fecha_nacimiento" class="block text-sm font-semibold text-gray-700 mb-2">
                            Fecha de Nacimiento <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-4 focus:ring-blue-100 focus:border-medical-blue transition-all duration-300 outline-none">
                        <div class="text-xs text-gray-500 mt-1">Debes tener al menos 18 años</div>
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <div class="form-group mt-4">
                        <label for="direccion" class="block text-sm font-semibold text-gray-700 mb-2">
                            Dirección <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="direccion" name="direccion" required
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-4 focus:ring-blue-100 focus:border-medical-blue transition-all duration-300 outline-none"
                               placeholder="Tu dirección completa">
                        <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4 mt-4">
                        <div class="form-group">
                            <label for="telefono" class="block text-sm font-semibold text-gray-700 mb-2">
                                Teléfono <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" id="telefono" name="telefono" required
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-4 focus:ring-blue-100 focus:border-medical-blue transition-all duration-300 outline-none"
                                   placeholder="+34 600 000 000">
                            <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                        <div class="form-group">
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" id="email" name="email" required
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-4 focus:ring-blue-100 focus:border-medical-blue transition-all duration-300 outline-none"
                                   placeholder="tu@email.com">
                            <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                    </div>
                </div>

            <!-- Account Credentials -->
            <div class="border-b border-gray-200 pb-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Credenciales de Acceso</h4>

                <div class="form-group">
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        Contraseña <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required
                               class="w-full px-4 py-3 pr-12 rounded-xl border border-gray-300 focus:ring-4 focus:ring-blue-100 focus:border-medical-blue transition-all duration-300 outline-none"
                               placeholder="Contraseña segura (mín. 8 caracteres)">
                        <button type="button" id="togglePassword" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                            <svg id="eyeOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg id="eyeClosed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Password Strength Indicator -->
                    <div class="mt-2">
                        <div class="flex space-x-1 mb-2">
                            <div id="strength-bar-1" class="password-strength-bar bg-gray-200 flex-1"></div>
                            <div id="strength-bar-2" class="password-strength-bar bg-gray-200 flex-1"></div>
                            <div id="strength-bar-3" class="password-strength-bar bg-gray-200 flex-1"></div>
                            <div id="strength-bar-4" class="password-strength-bar bg-gray-200 flex-1"></div>
                        </div>
                        <div id="password-strength-text" class="text-xs text-gray-500">
                            Debe contener al menos 8 caracteres, mayúsculas, minúsculas y números
                        </div>
                    </div>
                    <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                </div>

                <div class="form-group mt-4">
                    <label for="confirmPassword" class="block text-sm font-semibold text-gray-700 mb-2">
                        Confirmar Contraseña <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-4 focus:ring-blue-100 focus:border-medical-blue transition-all duration-300 outline-none"
                           placeholder="Repite tu contraseña">
                    <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                </div>
            </div>

            <!-- Privacy Policy -->
            <div>
                <div class="flex items-start space-x-3 p-4 bg-gray-50 rounded-xl">
                    <input type="checkbox" id="privacy" name="privacy" required
                           class="mt-1 w-4 h-4 text-medical-blue border-gray-300 rounded focus:ring-blue-200">
                    <label for="privacy" class="text-sm text-gray-700">
                        <span class="font-medium">Acepto la</span> 
                        <a href="#" class="text-medical-blue hover:text-blue-700 font-semibold">Política de Privacidad</a> 
                        y 
                        <a href="#" class="text-medical-blue hover:text-blue-700 font-semibold">Términos de Uso</a>. 
                        Entiendo que mis datos serán tratados conforme a GDPR/LOPD para datos médicos.
                    </label>
                </div>
                <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
            </div>
        `;

                console.log('✓ HTML generado, longitud:', fieldsHTML.length);

                formFields.innerHTML = fieldsHTML;

                console.log('✓ HTML insertado en formFields');

                // Show form with fade-in animation
                registrationForm.classList.remove('hidden');
                registrationForm.classList.add('fade-in');

                console.log('✓ Formulario visible');

                // Setup form functionality
                setupPasswordToggle();
                setupPasswordStrength();
                setupFormValidation();

                console.log('✓ Todo configurado correctamente');

            } catch (error) {
                console.error('❌ Error en generateForm:', error);
                alert('Error al generar el formulario: ' + error.message);
                throw error;
            }
        }

        // Password Toggle Functionality
        function setupPasswordToggle() {
            const toggleButton = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeOpen = document.getElementById('eyeOpen');
            const eyeClosed = document.getElementById('eyeClosed');

            if (toggleButton && passwordInput) {
                toggleButton.addEventListener('click', function () {
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

        // Password Strength Indicator
        function setupPasswordStrength() {
            const passwordInput = document.getElementById('password');
            const strengthBars = [
                document.getElementById('strength-bar-1'),
                document.getElementById('strength-bar-2'),
                document.getElementById('strength-bar-3'),
                document.getElementById('strength-bar-4')
            ];
            const strengthText = document.getElementById('password-strength-text');

            if (passwordInput) {
                passwordInput.addEventListener('input', function () {
                    const password = this.value;
                    const strength = calculatePasswordStrength(password);
                    updatePasswordStrengthUI(strength, strengthBars, strengthText);
                });
            }
        }

        function calculatePasswordStrength(password) {
            let score = 0;
            const checks = {
                length: password.length >= 8,
                lowercase: /[a-z]/.test(password),
                uppercase: /[A-Z]/.test(password),
                numbers: /\d/.test(password),
                symbols: /[!@#$%^&*(),.?":{}|<>]/.test(password)
            };

            // Length check (2 points)
            if (checks.length) score += 2;

            // Character type checks (1 point each)
            if (checks.lowercase) score++;
            if (checks.uppercase) score++;
            if (checks.numbers) score++;
            if (checks.symbols) score++;

            return Math.min(score, 4); // Max 4 points
        }

        function updatePasswordStrengthUI(strength, bars, textElement) {
            const colors = ['bg-gray-200', 'bg-red-400', 'bg-orange-400', 'bg-yellow-400', 'bg-green-400'];
            const texts = [
                'Debe contener al menos 8 caracteres, mayúsculas, minúsculas y números',
                'Muy débil - Añade más caracteres',
                'Débil - Usa mayúsculas y números',
                'Regular - Añade símbolos especiales',
                'Fuerte - ¡Excelente contraseña!'
            ];

            bars.forEach((bar, index) => {
                bar.className = 'password-strength-bar flex-1 ' + (index < strength ? colors[strength] : 'bg-gray-200');
            });

            textElement.textContent = texts[strength];
            textElement.className = `text-xs ${strength >= 3 ? 'text-green-600' : strength >= 2 ? 'text-yellow-600' : 'text-red-600'}`;
        }

        // Username Validation


        // Form Validation
        function setupFormValidation() {
            registrationForm.addEventListener('submit', function (e) {
                e.preventDefault();

                clearAllMessages();

                if (validateForm()) {
                    performRegistration();
                }
            });
        }

        function validateForm() {
            let isValid = true;
            const requiredFields = registrationForm.querySelectorAll('[required]');

            requiredFields.forEach(field => {
                const errorDiv = field.parentElement.querySelector('.error-message');

                if (!field.value.trim()) {
                    showFieldError(field, errorDiv, 'Este campo es obligatorio');
                    isValid = false;
                } else {
                    clearFieldError(field, errorDiv);

                    // Specific field validations
                    if (field.type === 'email' && !validateEmail(field.value)) {
                        showFieldError(field, errorDiv, 'Introduce un email válido');
                        isValid = false;
                    }

                    if (field.id === 'password' && !validatePassword(field.value)) {
                        showFieldError(field, errorDiv, 'La contraseña debe tener al menos 8 caracteres, mayúsculas, minúsculas y números');
                        isValid = false;
                    }

                    if (field.id === 'confirmPassword') {
                        const password = document.getElementById('password').value;
                        if (field.value !== password) {
                            showFieldError(field, errorDiv, 'Las contraseñas no coinciden');
                            isValid = false;
                        }
                    }

                    if (field.id === 'telefono' && !validatePhone(field.value)) {
                        showFieldError(field, errorDiv, 'Introduce un número de teléfono válido');
                        isValid = false;
                    }

                    if (field.id === 'dni' && !validateDNI(field.value)) {
                        showFieldError(field, errorDiv, 'DNI/NIE inválido. Formato: 8 dígitos + letra (Ej: 12345678A)');
                        isValid = false;
                    }
                }
            });

            return isValid;
        }

        // Validation helpers
        function validateEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        function validatePassword(password) {
            return password.length >= 8 &&
                /[a-z]/.test(password) &&
                /[A-Z]/.test(password) &&
                /\d/.test(password);
        }

        function validateDNI(dni) {
            // Validar formato DNI/NIE español: 8 dígitos + letra o letra + 7 dígitos + letra
            const dniPattern = /^[0-9XYZ][0-9]{7}[A-Z]$/i;
            return dniPattern.test(dni);
        }

        function validatePhone(phone) {
            return /^[\+]?[\d\s\-\(\)]{9,}$/.test(phone);
        }

        // Registration Process
        async function performRegistration() {
            const submitBtn = registrationForm.querySelector('button[type="submit"]');
            const registerText = document.getElementById('register-text');
            const registerSpinner = document.getElementById('register-spinner');

            // Show loading state
            submitBtn.disabled = true;
            registerText.classList.add('hidden');
            registerSpinner.classList.remove('hidden');

            // Collect form data
            const formData = new FormData(registrationForm);
            const userData = {
                dni: formData.get('dni'),
                nombre: formData.get('nombre'),
                apellidos: formData.get('apellidos'),
                email: formData.get('email'),
                password: formData.get('password'),
                telefono: formData.get('telefono'),
                direccion: formData.get('direccion'),
                fecha_nacimiento: formData.get('fecha_nacimiento')
            };

            try {
                // Llamada real al backend
                const response = await fetch(`${API_BASE}/RegistroController.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(userData)
                });

                const result = await response.json();

                if (result.success) {
                    handleRegistrationSuccess(result);
                } else {
                    handleRegistrationError(result.mensaje || 'Error al crear la cuenta');
                }
            } catch (error) {
                console.error('Error de conexión:', error);
                handleRegistrationError('Error de conexión con el servidor. Verifica tu conexión e intenta de nuevo.');
            }
        }

        function handleRegistrationSuccess(result) {
            // Mostrar notificación temporal de éxito
            showSuccessNotification(result.mensaje);

            // Reset button state
            resetSubmitButton();

            // Redirigir a index después de 1 segundo
            setTimeout(() => {
                window.location.href = result.redirect || 'index.html';
            }, 1000);
        }

        function handleRegistrationError(message) {
            showErrorMessage(message);
            resetSubmitButton();
        }

        // UI Helper Functions
        function showFieldError(input, errorDiv, message) {
            input.classList.add('border-red-500', 'bg-red-50');
            input.classList.remove('border-gray-300');
            if (errorDiv) {
                errorDiv.textContent = message;
                errorDiv.classList.remove('hidden');
            }
        }

        function clearFieldError(input, errorDiv) {
            input.classList.remove('border-red-500', 'bg-red-50');
            input.classList.add('border-gray-300');
            if (errorDiv) {
                errorDiv.textContent = '';
                errorDiv.classList.add('hidden');
            }
        }

        function showSuccessMessage(message) {
            successMessage.textContent = message;
            successMessage.classList.remove('hidden');
            successMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function showSuccessNotification(message) {
            // Crear notificación flotante
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-xl shadow-lg z-50 transform transition-all duration-300 ease-in-out';
            notification.innerHTML = `
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <span class="font-semibold">${message}</span>
            </div>
        `;

            document.body.appendChild(notification);

            // Animar entrada
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 100);

            // Remover después de 1 segundo
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 1000);
        }

        function showErrorMessage(message) {
            errorMessage.textContent = message;
            errorMessage.classList.remove('hidden');
            errorMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function clearAllMessages() {
            successMessage.classList.add('hidden');
            errorMessage.classList.add('hidden');
        }

        function resetSubmitButton() {
            const submitBtn = registrationForm.querySelector('button[type="submit"]');
            const registerText = document.getElementById('register-text');
            const registerSpinner = document.getElementById('register-spinner');

            submitBtn.disabled = false;
            registerText.classList.remove('hidden');
            registerSpinner.classList.add('hidden');
        }

        // Focus animations
        document.addEventListener('focus', function (e) {
            if (e.target.matches('input:not([type="checkbox"]), select')) {
                e.target.closest('.form-group').classList.add('scale-[1.02]');
            }
        }, true);

        document.addEventListener('blur', function (e) {
            if (e.target.matches('input:not([type="checkbox"]), select')) {
                e.target.closest('.form-group').classList.remove('scale-[1.02]');
            }
        }, true);

    } catch (error) {
        console.error('❌ ERROR:', error);
        alert('Error al cargar el formulario: ' + error.message);
        // Mostrar error en la página
        document.body.insertAdjacentHTML('beforeend',
            `<div style="position:fixed; top:10px; right:10px; background:red; color:white; padding:20px; border-radius:10px; z-index:9999;">
                <strong>ERROR:</strong><br>${error.message}<br>
                <small>Abre la consola (F12) para más detalles</small>
            </div>`
        );
    }
});