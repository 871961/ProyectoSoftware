# Cambios: Sistema de Identificación con DNI/NIE

## Resumen de Cambios

El sistema ha sido actualizado para usar **DNI/NIE** como clave primaria de pacientes en lugar de un ID autoincrementable. Este cambio hace que el sistema sea más realista y cumple con las prácticas de identificación en el sistema sanitario español.

---

## 📋 Archivos Modificados

### 🗄️ Base de Datos

#### `database/schema.sql`
- **Tabla `pacientes`**: 
  - Clave primaria cambiada de `id_paciente SERIAL` a `dni VARCHAR(20) PRIMARY KEY`
  - El DNI es único e identifica de forma inequívoca a cada paciente

- **Tablas relacionadas** actualizadas para usar DNI:
  - `perfiles_salud`: `id_paciente` ahora es `VARCHAR(20)` y referencia `pacientes(dni)`
  - `antecedentes_familiares`: `id_paciente` ahora es `VARCHAR(20)` y referencia `pacientes(dni)`
  - `consultas`: `id_paciente` ahora es `VARCHAR(20)` y referencia `pacientes(dni)`
  - `auditoria_logs`: `id_paciente` ahora es `VARCHAR(20)` y referencia `pacientes(dni)`
  - `auditoria_logs`: `registro_id` ahora es `VARCHAR(50)` para almacenar tanto IDs numéricos como DNIs

#### `database/datos_prueba.sql`
- Todos los pacientes de prueba ahora tienen DNIs válidos:
  - María Pérez: `12345678A`
  - José Martín: `87654321B`
  - Laura Ruiz: `23456789C`
  - Antonio Sánchez: `98765432D`
  - Patricia Moreno: `34567890E`
- Todas las foreign keys actualizadas para usar estos DNIs

#### `database/migracion_dni.sql` (NUEVO)
- Script de migración para actualizar bases de datos existentes
- Instrucciones paso a paso para migrar de `id_paciente` a `dni`

---

### 🔧 Backend (PHP)

#### `backend/src/vo/PacienteVO.php`
- Atributo `$id_paciente` reemplazado por `$dni`
- Añadidos getters/setters para DNI:
  - `getDni()` / `setDni($dni)`
- **Métodos de compatibilidad** añadidos:
  - `getIdPaciente()` ahora devuelve `$this->dni`
  - `setIdPaciente($id)` ahora establece `$this->dni`
  - Esto mantiene compatibilidad con código existente

#### `backend/src/dao/PacienteDAO.php`
- **`insertar()`**: Ahora inserta el DNI en lugar de generar un ID autoincremental
  - Retorna el DNI insertado en lugar del lastInsertId()
- **`actualizar()`**: Usa DNI en la cláusula WHERE
- **`darDeBaja()`**: Parámetro cambiado de `$id_paciente` a `$dni`
- **`obtenerPorId()`**: Busca por DNI en lugar de id_paciente
- **`buscarPorId()`**: Nuevo método alias de `obtenerPorId()`

#### `backend/src/controllers/RegistroController.php`
- **Validación de DNI añadida**:
  - Campo requerido
  - Formato validado: `^[0-9XYZ][0-9]{7}[A-Z]$`
  - Error específico si el formato es inválido
- **Verificación de duplicados**:
  - Ahora verifica tanto DNI como email
  - HTTP 409 si el DNI ya existe
  - HTTP 409 si el email ya existe
- DNI normalizado a mayúsculas antes de insertar
- Sesión creada con DNI como `user_id`

#### `backend/src/controllers/AuthController.php`
- Compatible automáticamente gracias a métodos de compatibilidad en PacienteVO
- `$_SESSION['user_id']` ahora contiene el DNI del paciente
- Sin cambios necesarios en el código

---

### 🎨 Frontend (JavaScript/HTML)

#### `frontend/static/js/register.js`

##### Campo DNI en el formulario:
```javascript
<div class="form-group">
    <label for="dni">DNI/NIE <span class="text-red-500">*</span></label>
    <input type="text" id="dni" name="dni" required maxlength="9"
           pattern="[0-9XYZ][0-9]{7}[A-Z]"
           placeholder="12345678A"
           class="uppercase">
    <div class="text-xs text-gray-500 mt-1">
        Formato: 8 dígitos + letra (Ej: 12345678A)
    </div>
</div>
```

##### Validación de DNI:
```javascript
function validateDNI(dni) {
    const dniPattern = /^[0-9XYZ][0-9]{7}[A-Z]$/i;
    return dniPattern.test(dni);
}
```

##### Datos enviados al backend:
```javascript
const userData = {
    dni: formData.get('dni'),  // NUEVO
    nombre: formData.get('nombre'),
    apellidos: formData.get('apellidos'),
    // ... resto de campos
};
```

---

## 🚀 Instrucciones de Instalación

### Para Nueva Instalación (Base de datos vacía)

1. **Ejecutar el schema actualizado:**
   ```bash
   psql -U postgres -d medhistory -f database/schema.sql
   ```

2. **Insertar datos de prueba:**
   ```bash
   psql -U postgres -d medhistory -f database/datos_prueba.sql
   ```

3. ✅ ¡Listo! El sistema ya usa DNI como clave primaria.

---

### Para Migración (Base de datos existente con datos)

#### ⚠️ IMPORTANTE: Hacer backup antes de migrar
```bash
pg_dump -U postgres medhistory > backup_antes_migracion.sql
```

#### Opción 1: Recrear la base de datos (RECOMENDADO si no hay datos importantes)
```bash
# 1. Eliminar base de datos existente
psql -U postgres -c "DROP DATABASE IF EXISTS medhistory;"

# 2. Crear nueva base de datos
psql -U postgres -c "CREATE DATABASE medhistory;"

# 3. Ejecutar schema nuevo
psql -U postgres -d medhistory -f database/schema.sql

# 4. Insertar datos de prueba
psql -U postgres -d medhistory -f database/datos_prueba.sql
```

#### Opción 2: Migración con datos existentes
```bash
# Revisar y editar database/migracion_dni.sql según tus necesidades
# Luego ejecutar:
psql -U postgres -d medhistory -f database/migracion_dni.sql
```

**Nota:** Si tienes datos existentes, necesitarás:
1. Añadir DNIs reales a los pacientes existentes
2. Actualizar las foreign keys en tablas relacionadas
3. Ver comentarios en `migracion_dni.sql` para detalles

---

## 📝 Formato de DNI/NIE

### Formato Válido
- **DNI**: 8 dígitos + letra mayúscula
  - Ejemplo: `12345678A`, `87654321B`
- **NIE**: Letra (X, Y, Z) + 7 dígitos + letra mayúscula
  - Ejemplo: `X1234567L`, `Y7654321R`

### Expresión Regular
```regex
^[0-9XYZ][0-9]{7}[A-Z]$
```

### Validación en JavaScript
```javascript
/^[0-9XYZ][0-9]{7}[A-Z]$/i.test(dni)
```

### Validación en PHP
```php
preg_match('/^[0-9XYZ][0-9]{7}[A-Z]$/', strtoupper($dni))
```

---

## 🧪 Datos de Prueba

### Pacientes con DNI

| DNI | Nombre Completo | Email | Contraseña |
|-----|----------------|-------|------------|
| `12345678A` | María Pérez García | maria.perez@email.com | password |
| `87654321B` | José Martín López | jose.martin@email.com | password |
| `23456789C` | Laura Ruiz Herrera | laura.ruiz@email.com | password |
| `98765432D` | Antonio Sánchez Verde | antonio.sanchez@email.com | password |
| `34567890E` | Patricia Moreno Castro | patricia.moreno@email.com | password |

---

## 🔐 Impacto en Sesiones

### Antes (id_paciente)
```php
$_SESSION['user_id'] = 1; // ID numérico
$_SESSION['user_tipo'] = 'paciente';
```

### Ahora (DNI)
```php
$_SESSION['user_id'] = '12345678A'; // DNI
$_SESSION['user_tipo'] = 'paciente';
```

**Nota:** El código del frontend no necesita cambios porque `user_id` siempre se trata como string en JavaScript.

---

## ✅ Compatibilidad

### Métodos de Compatibilidad
El sistema mantiene compatibilidad con código existente que usa `getIdPaciente()`:

```php
// Estos métodos son equivalentes ahora:
$paciente->getDni();          // '12345678A'
$paciente->getIdPaciente();   // '12345678A' (mismo resultado)
```

### AuthController
No requiere cambios porque usa `getIdPaciente()` que ahora devuelve el DNI automáticamente.

---

## 📊 Beneficios del Cambio

1. **✅ Identificación Real**: DNI/NIE es el identificador oficial en España
2. **✅ Prevención de Duplicados**: DNI único por ley
3. **✅ Cumplimiento Legal**: Alineado con normativa sanitaria
4. **✅ Trazabilidad**: Mejor auditoría de acciones por paciente
5. **✅ Integración**: Facilita integración con sistemas externos del SNS

---

## 🐛 Resolución de Problemas

### Error: "DNI inválido"
- Verifica el formato: 8 dígitos + letra mayúscula
- Ejemplo correcto: `12345678A`
- Ejemplo incorrecto: `1234567A` (faltan dígitos)

### Error: "Este DNI ya está registrado"
- El DNI debe ser único en el sistema
- Verifica si el paciente ya existe en la base de datos
- Usa un DNI diferente o inicia sesión con el existente

### Error en Foreign Keys durante migración
- Asegúrate de eliminar las foreign keys antes de cambiar tipos de columna
- Sigue el orden del script `migracion_dni.sql`
- Verifica que todos los DNIs en tablas relacionadas existan en `pacientes`

---

## 📞 Contacto

Para dudas o problemas con la migración:
- **Autoras**: Yousra y Claudia
- **Proyecto**: MedHistory - Sistema de Gestión de Historial Clínico
- **Fecha**: Marzo 2026

---

## 📄 Licencia

Este proyecto es parte del sistema MedHistory y está sujeto a las mismas condiciones de uso y privacidad.
