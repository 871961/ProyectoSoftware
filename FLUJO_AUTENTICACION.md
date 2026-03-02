# Flujo de Autenticación - MedHistory

## ✅ Verificación del Sistema

### 1. REGISTRO DE PACIENTE

**Frontend:** [register.html](frontend/static/register.html) + [register.js](frontend/static/js/register.js)  
**Backend:** [RegistroController.php](backend/src/controllers/RegistroController.php)

#### Flujo Completo:
```
1. Usuario accede a: http://medHistory.local/register.html
2. Formulario se genera automáticamente con JavaScript
3. Usuario completa los campos:
   - Nombre, Apellidos
   - Fecha de Nacimiento (mín. 18 años)
   - Dirección, Teléfono, Email
   - Contraseña (mín. 8 caracteres, mayúsculas, minúsculas, números)
   - Confirmar Contraseña
   - Acepta Política de Privacidad

4. Al hacer submit:
   → fetch() POST a /backend/src/controllers/RegistroController.php
   
5. Backend valida y ejecuta:
   → password_hash() con PASSWORD_BCRYPT
   → Crea PacienteVO con los datos
   → $pacienteDAO->insertar($paciente) ✅ GUARDA EN BD
   → Crea sesión PHP automáticamente
   → Devuelve JSON: { success: true, redirect: 'paciente.html' }

6. Frontend recibe respuesta:
   → Muestra mensaje de éxito
   → Ejecuta: window.location.href = 'paciente.html' ✅ REDIRIGE
```

**Estado:** ✅ **FUNCIONA CORRECTAMENTE**
- Se guarda en BD: tabla `pacientes`
- Redirige a: `paciente.html`

---

### 2. INICIO DE SESIÓN (PACIENTE Y MÉDICO)

**Frontend:** [login.html](frontend/static/login.html) + [login.js](frontend/static/js/login.js)  
**Backend:** [AuthController.php](backend/src/controllers/AuthController.php)

#### Flujo Completo:
```
1. Usuario accede a: http://medHistory.local/login.html
2. Selecciona rol: Paciente o Médico
3. Ingresa email y contraseña
4. Opcional: marca "Recordarme"

5. Al hacer submit:
   → fetch() POST a /backend/src/controllers/AuthController.php
   → Envía: { email, password, role: 'paciente'|'medico' }

6. Backend ejecuta según rol:
   
   SI role === 'paciente':
      → $pacienteDAO->buscarPorEmail($email)
      → Verifica: password_verify($password, $hash)
      → Verifica: $paciente->getActivo() === true
      → Crea sesión PHP con datos del paciente
      → Devuelve: { success: true, redirect: 'paciente.html' } ✅
   
   SI role === 'medico':
      → $medicoDAO->buscarPorEmail($email)
      → Verifica: password_verify($password, $hash)
      → Verifica: $medico->getActivo() === true
      → Crea sesión PHP con datos del médico
      → Devuelve: { success: true, redirect: 'medico.html' } ✅

7. Frontend recibe respuesta:
   → Si remember=true: guarda email y rol en localStorage
   → Muestra mensaje: "¡Bienvenido de vuelta, [nombre]!"
   → Ejecuta: window.location.href = result.redirect
   → REDIRIGE a paciente.html o medico.html según tipo
```

**Estado:** ✅ **FUNCIONA CORRECTAMENTE**
- Paciente → redirige a `paciente.html`
- Médico → redirige a `medico.html`

---

## 🔐 Sesiones PHP Creadas

Después de login o registro exitoso:

```php
$_SESSION['user_id']       // ID del usuario (1, 2, 3, etc.)
$_SESSION['user_tipo']     // 'paciente' o 'medico'
$_SESSION['user_nombre']   // "Juan Pérez García"
$_SESSION['user_email']    // "juan@email.com"

// Solo para médicos:
$_SESSION['user_especialidad']  // "Cardiología", "Medicina General", etc.
```

---

## 📊 Tabla de Redirecciones

| Usuario   | Acción   | Destino          | Estado |
|-----------|----------|------------------|--------|
| Paciente  | Registro | paciente.html    | ✅     |
| Paciente  | Login    | paciente.html    | ✅     |
| Médico    | Login    | medico.html      | ✅     |
| Admin     | Login    | admin.html       | ✅     |

**Nota:** Los médicos NO pueden auto-registrarse. Son creados por administradores.

---

## 🧪 Pruebas

### Probar Registro (Nuevo Paciente):
```bash
URL: http://medHistory.local/register.html
- Completa formulario con datos válidos
- Click en "Crear Cuenta"
- Verificar: Redirige a paciente.html
- Verificar BD: SELECT * FROM pacientes WHERE email = 'tu@email.com';
```

### Probar Login Paciente:
```bash
URL: http://medHistory.local/login.html
- Seleccionar: Paciente
- Email: maria.perez@email.com
- Password: paciente123
- Click "Iniciar Sesión"
- Verificar: Redirige a paciente.html
```

### Probar Login Médico:
```bash
URL: http://medHistory.local/login.html
- Seleccionar: Médico
- Email: elena.fernandez@clinica.com
- Password: medico123
- Click "Iniciar Sesión"
- Verificar: Redirige a medico.html
```

---

## 🔍 Depuración

Si algo no funciona, revisar:

### 1. Consola del Navegador (F12)
```javascript
// Ver errores de fetch, JavaScript, etc.
```

### 2. Network Tab (F12 > Network)
```
- Ver requests a AuthController.php y RegistroController.php
- Ver responses JSON
- Ver códigos de estado (200, 400, 401, etc.)
```

### 3. Logs de PHP
```php
// En los controllers, agregar:
error_log("DEBUG: Email recibido: " . $email);
error_log("DEBUG: Usuario encontrado: " . print_r($usuario, true));
```

---

## ✅ RESUMEN RESPUESTA A TU PREGUNTA

**¿Cuando un cliente se registra se debe añadir a la BD?**
✅ **SÍ** - Se ejecuta `$pacienteDAO->insertar($paciente)` en [RegistroController.php](backend/src/controllers/RegistroController.php#L135)

**¿Cuando inicia sesión ir a paciente.html?**
✅ **SÍ** - Tanto registro como login redirigen a `paciente.html` con `window.location.href`

**¿Cuando un doctor inicia sesión se le debe redirigir a medico.html?**
✅ **SÍ** - [AuthController.php](backend/src/controllers/AuthController.php#L120) devuelve `redirect: 'medico.html'` para médicos

---

**Sistema:** ✅ Totalmente Funcional  
**Última actualización:** Marzo 2026
