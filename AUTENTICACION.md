# Sistema de Autenticación - MedHistory

## Descripción General

El sistema de autenticación de MedHistory permite:
- **Registro de pacientes** (auto-registro)
- **Inicio de sesión** para pacientes, médicos y administradores
- Los médicos son registrados únicamente por administradores

## Archivos Implementados

### Backend (Controllers)

#### AuthController.php
**Ubicación:** `backend/src/controllers/AuthController.php`
**Propósito:** Maneja el inicio de sesión para todos los tipos de usuarios

**Endpoint:** `POST /backend/src/controllers/AuthController.php`

**Request Body:**
```json
{
  "email": "usuario@email.com",
  "password": "contraseña",
  "role": "paciente|medico|admin"
}
```

**Response Success (200):**
```json
{
  "success": true,
  "mensaje": "Login exitoso",
  "usuario": {
    "id": 123,
    "nombre": "Juan Pérez",
    "email": "juan@email.com",
    "tipo": "paciente"
  },
  "redirect": "paciente.html"
}
```

**Response Error (401):**
```json
{
  "success": false,
  "mensaje": "Credenciales inválidas o usuario inactivo"
}
```

#### RegistroController.php
**Ubicación:** `backend/src/controllers/RegistroController.php`
**Propósito:** Maneja el registro de nuevos pacientes

**Endpoint:** `POST /backend/src/controllers/RegistroController.php`

**Request Body:**
```json
{
  "nombre": "Juan",
  "apellidos": "Pérez García",
  "email": "juan@email.com",
  "password": "Password123",
  "telefono": "+34 600 000 000",
  "direccion": "Calle Principal 123",
  "fecha_nacimiento": "1990-01-15"
}
```

**Validaciones:**
- Email válido y único
- Contraseña: mínimo 8 caracteres, mayúsculas, minúsculas y números
- Edad mínima: 18 años
- Todos los campos son obligatorios

**Response Success (200):**
```json
{
  "success": true,
  "mensaje": "Registro exitoso. ¡Bienvenido/a a MedHistory!",
  "usuario": {
    "id": 124,
    "nombre": "Juan Pérez García",
    "email": "juan@email.com",
    "tipo": "paciente"
  },
  "redirect": "paciente.html"
}
```

### Frontend

#### register.html
**Modificaciones:**
- Eliminada la opción de registro para médicos
- Ahora solo permite registro de pacientes
- Muestra mensaje informativo: "Los médicos son registrados por administradores"

#### register.js
**Modificaciones:**
- Eliminada lógica de selección de rol
- Formulario se muestra automáticamente
- Integración con `RegistroController.php` mediante fetch API
- Validaciones del lado del cliente antes de enviar

**Campos del formulario:**
- Nombre
- Apellidos
- Fecha de Nacimiento
- Dirección
- Teléfono
- Email
- Contraseña (con indicador de fortaleza)
- Confirmar Contraseña
- Checkbox de Política de Privacidad

#### login.html
Sin cambios en el HTML

#### login.js
**Modificaciones:**
- Integración con `AuthController.php` mediante fetch API
- Soporte para login de pacientes y médicos
- Manejo de sesión con localStorage (opción "Recordarme")
- Redirección automática al dashboard correspondiente

**Funcionalidades:**
- Selección de rol (Paciente/Médico)
- Validación de email
- Toggle de visibilidad de contraseña
- Recordar credenciales (30 días máximo)

## Flujo de Uso

### Registro de Nuevo Paciente
1. Usuario accede a `register.html`
2. Completa el formulario de registro
3. Sistema valida datos (cliente y servidor)
4. Se crea la cuenta en la base de datos
5. Inicio de sesión automático
6. Redirección a `paciente.html`

### Inicio de Sesión
1. Usuario accede a `login.html`
2. Selecciona su rol (Paciente/Médico)
3. Ingresa email y contraseña
4. Sistema verifica credenciales
5. Si es válido: crea sesión y redirige al dashboard
6. Si es inválido: muestra mensaje de error

## Seguridad Implementada

### Backend
- **Password Hashing:** PASSWORD_BCRYPT para todas las contraseñas
- **Validación de Email:** filter_var con FILTER_VALIDATE_EMAIL
- **Verificación de Usuario Activo:** Solo usuarios activos pueden iniciar sesión
- **Sesiones PHP:** Almacenamiento seguro de datos de usuario
- **CORS Headers:** Configurado para permitir requests del frontend

### Frontend
- **Validación de Contraseña:**
  - Mínimo 8 caracteres
  - Al menos una mayúscula
  - Al menos una minúscula
  - Al menos un número
  
- **Indicador de Fortaleza de Contraseña:** Feedback visual en tiempo real
- **Validación de Edad:** Edad mínima de 18 años
- **Sanitización de Inputs:** Prevención de XSS

## Variables de Configuración

En ambos archivos JS (`login.js` y `register.js`):

```javascript
const API_BASE = 'http://medHistory.local/backend/src/controllers';
```

**Modificar según tu entorno:**
- Desarrollo local: `http://medHistory.local/backend/src/controllers`
- Producción: `https://tu-dominio.com/backend/src/controllers`

## Estructura de Sesión

Después de un login exitoso, la sesión PHP contiene:

```php
$_SESSION['user_id']       // ID del usuario (paciente/médico/admin)
$_SESSION['user_tipo']     // 'paciente', 'medico', o 'admin'
$_SESSION['user_nombre']   // Nombre completo del usuario
$_SESSION['user_email']    // Email del usuario
```

Para médicos, también:
```php
$_SESSION['user_especialidad']  // Especialidad del médico
```

## Dependencias

### Backend
- PHP 8.0+
- PostgreSQL 18.2
- PDO extension
- Sesiones PHP habilitadas

### Frontend
- Navegador moderno con soporte para:
  - Fetch API
  - Async/Await
  - LocalStorage
  - ES6+

## Credenciales de Prueba

### Pacientes de Prueba
```
Email: maria.perez@email.com
Password: paciente123

Email: jose.martin@email.com
Password: paciente123
```

### Médicos de Prueba
```
Email: elena.fernandez@clinica.com
Password: medico123

Email: miguel.rodriguez@clinica.com
Password: medico123
```

### Administradores
```
Email: yousra@clinica.com
Password: admin123

Email: claudia@clinica.com
Password: admin123
```

## Testing

### Probar Registro
1. Navegar a `http://medHistory.local/register.html`
2. Completar formulario con datos válidos
3. Verificar redirección a dashboard tras registro exitoso

### Probar Login
1. Navegar a `http://medHistory.local/login.html`
2. Seleccionar rol (Paciente/Médico)
3. Ingresar credenciales de prueba
4. Verificar redirección apropiada

### Verificar Errores
- Intentar registro con email duplicado
- Intentar login con credenciales incorrectas
- Intentar login con usuario inactivo
- Probar contraseñas débiles en registro

## Próximos Pasos

1. Implementar recuperación de contraseña
2. Verificación de email al registrarse
3. Límite de intentos de login (rate limiting)
4. 2FA (autenticación de dos factores)
5. Auditoría de inicios de sesión en `auditoria_logs`

## Notas Importantes

- **Los médicos NO pueden auto-registrarse**. Deben ser creados por un administrador.
- Las contraseñas se hashean con PASSWORD_BCRYPT antes de guardarse.
- Los usuarios inactivos (activo = false) no pueden iniciar sesión.
- Las sesiones se crean automáticamente tras registro o login exitoso.
- El sistema es compatible con GDPR/LOPD para datos médicos.

---

**Última actualización:** Marzo 2026  
**Autoras:** Claudia Y Yousra
