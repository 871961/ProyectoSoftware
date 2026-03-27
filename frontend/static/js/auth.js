/**
 * ====================================
 * MedHistory - Authentication Module
 * ====================================
 * 
 * Purpose: Handle user authentication and role-based routing
 * Responsibilities:
 *   - Form validation (login/register)
 *   - Role detection (Doctor/Patient)
 *   - Redirect to appropriate dashboard
 *   - Session management
 * 
 * Dependencies: None (vanilla JavaScript)
 * Author: MedHistory Development Team
 * Last Modified: February 2, 2026
 */

// ========================================
// MODULE: FORM VALIDATOR
// ========================================

class FormValidator {
    /**
     * Validate email format
     * @param {string} email - Email address to validate
     * @returns {boolean} True if valid
     */
    static isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Validate password strength
     * @param {string} password - Password to validate
     * @returns {object} Validation result with status and message
     */
    static validatePassword(password) {
        const minLength = 8;
        const hasUpperCase = /[A-Z]/.test(password);
        const hasLowerCase = /[a-z]/.test(password);
        const hasNumber = /\d/.test(password);
        const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);

        if (password.length < minLength) {
            return {
                valid: false,
                message: `La contraseña debe tener al menos ${minLength} caracteres`
            };
        }

        if (!hasUpperCase) {
            return {
                valid: false,
                message: 'La contraseña debe contener al menos una mayúscula'
            };
        }

        if (!hasLowerCase) {
            return {
                valid: false,
                message: 'La contraseña debe contener al menos una minúscula'
            };
        }

        if (!hasNumber) {
            return {
                valid: false,
                message: 'La contraseña debe contener al menos un número'
            };
        }

        if (!hasSpecialChar) {
            return {
                valid: false,
                message: 'La contraseña debe contener al menos un carácter especial'
            };
        }

        return {
            valid: true,
            message: 'Contraseña válida'
        };
    }

    /**
     * Validate required field
     * @param {string} value - Field value
     * @param {string} fieldName - Name of the field
     * @returns {object} Validation result
     */
    static validateRequired(value, fieldName) {
        if (!value || value.trim() === '') {
            return {
                valid: false,
                message: `El campo ${fieldName} es obligatorio`
            };
        }

        return {
            valid: true,
            message: 'Campo válido'
        };
    }

    /**
     * Validate DNI/NIF format (Spanish ID)
     * @param {string} dni - DNI to validate
     * @returns {boolean} True if valid
     */
    static isValidDNI(dni) {
        const dniRegex = /^\d{8}[A-Z]$/;
        return dniRegex.test(dni);
    }
}

// ========================================
// MODULE: USER ROLES
// ========================================

const UserRoles = {
    DOCTOR: 'doctor',
    PATIENT: 'patient',
    ADMIN: 'admin'
};

// ========================================
// MODULE: AUTHENTICATION SERVICE
// ========================================

class AuthService {
    constructor() {
        this.currentUser = null;
        this.sessionKey = 'medhistory_session';
    }

    /**
     * Handle login attempt
     * @param {object} credentials - User credentials
     * @returns {Promise<object>} Login result
     */
    async login(credentials) {
        const { email, password, role } = credentials;

        // Validate inputs
        if (!FormValidator.isValidEmail(email)) {
            return {
                success: false,
                message: 'Email no válido'
            };
        }

        const passwordValidation = FormValidator.validatePassword(password);
        if (!passwordValidation.valid) {
            return {
                success: false,
                message: passwordValidation.message
            };
        }

        // Simulate API call (replace with real API in production)
        try {
            const user = await this.authenticateUser(email, password, role);

            if (user) {
                this.setSession(user);
                return {
                    success: true,
                    message: 'Inicio de sesión exitoso',
                    user: user
                };
            } else {
                return {
                    success: false,
                    message: 'Credenciales incorrectas'
                };
            }
        } catch (error) {
            console.error('Login error:', error);
            return {
                success: false,
                message: 'Error al iniciar sesión. Inténtelo de nuevo.'
            };
        }
    }

    /**
     * Handle registration attempt
     * @param {object} userData - New user data
     * @returns {Promise<object>} Registration result
     */
    async register(userData) {
        const { name, email, password, confirmPassword, dni, role } = userData;

        // Validate all fields
        const nameValidation = FormValidator.validateRequired(name, 'Nombre');
        if (!nameValidation.valid) {
            return { success: false, message: nameValidation.message };
        }

        if (!FormValidator.isValidEmail(email)) {
            return { success: false, message: 'Email no válido' };
        }

        if (!FormValidator.isValidDNI(dni)) {
            return { success: false, message: 'DNI no válido (formato: 12345678A)' };
        }

        const passwordValidation = FormValidator.validatePassword(password);
        if (!passwordValidation.valid) {
            return { success: false, message: passwordValidation.message };
        }

        if (password !== confirmPassword) {
            return { success: false, message: 'Las contraseñas no coinciden' };
        }

        // Simulate API call (replace with real API in production)
        try {
            const newUser = await this.createUser(userData);

            if (newUser) {
                this.setSession(newUser);
                return {
                    success: true,
                    message: 'Registro exitoso',
                    user: newUser
                };
            } else {
                return {
                    success: false,
                    message: 'Error al crear usuario'
                };
            }
        } catch (error) {
            console.error('Registration error:', error);
            return {
                success: false,
                message: 'Error al registrar usuario. Inténtelo de nuevo.'
            };
        }
    }

    /**
     * Simulate user authentication (replace with real API)
     * @param {string} email - User email
     * @param {string} password - User password
     * @param {string} role - User role
     * @returns {Promise<object|null>} User object or null
     */
    async authenticateUser(email, password, role) {
        // Simulate API delay
        await this.simulateDelay(800);

        // Mock authentication (DEMO ONLY - Remove in production)
        if (password.length >= 8) {
            return {
                id: Math.random().toString(36).substr(2, 9),
                name: email.split('@')[0],
                email: email,
                role: role || UserRoles.PATIENT,
                createdAt: new Date().toISOString()
            };
        }

        return null;
    }

    /**
     * Simulate user creation (replace with real API)
     * @param {object} userData - New user data
     * @returns {Promise<object>} Created user object
     */
    async createUser(userData) {
        // Simulate API delay
        await this.simulateDelay(1000);

        // Mock user creation (DEMO ONLY - Remove in production)
        return {
            id: Math.random().toString(36).substr(2, 9),
            name: userData.name,
            email: userData.email,
            dni: userData.dni,
            role: userData.role || UserRoles.PATIENT,
            createdAt: new Date().toISOString()
        };
    }

    /**
     * Store user session
     * @param {object} user - User object
     */
    setSession(user) {
        this.currentUser = user;
        localStorage.setItem(this.sessionKey, JSON.stringify(user));
    }

    /**
     * Get current session
     * @returns {object|null} User object or null
     */
    getSession() {
        if (this.currentUser) {
            return this.currentUser;
        }

        const sessionData = localStorage.getItem(this.sessionKey);
        if (sessionData) {
            this.currentUser = JSON.parse(sessionData);
            return this.currentUser;
        }

        return null;
    }

    /**
     * Clear user session (logout)
     */
    clearSession() {
        this.currentUser = null;
        localStorage.removeItem(this.sessionKey);
    }

    /**
     * Redirect to appropriate dashboard based on user role
     * @param {object} user - User object
     */
    redirectToDashboard(user) {
        if (!user || !user.role) {
            console.error('Invalid user object for redirect');
            return;
        }

        switch (user.role) {
            case UserRoles.DOCTOR:
                window.location.href = 'medico.html';
                break;
            case UserRoles.PATIENT:
                window.location.href = 'paciente.html';
                break;
            case UserRoles.ADMIN:
                window.location.href = 'dashboard-admin.html';
                break;
            default:
                console.error('Unknown user role:', user.role);
        }
    }

    /**
     * Check if user is authenticated
     * @returns {boolean} True if authenticated
     */
    isAuthenticated() {
        return this.getSession() !== null;
    }

    /**
     * Simulate network delay (for demo purposes)
     * @param {number} ms - Milliseconds to delay
     * @returns {Promise} Promise that resolves after delay
     */
    simulateDelay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

// ========================================
// SINGLETON INSTANCE
// ========================================

const authService = new AuthService();

// ========================================
// EXPORT FOR MODULE USAGE
// ========================================

if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        FormValidator,
        UserRoles,
        AuthService,
        authService
    };
}
