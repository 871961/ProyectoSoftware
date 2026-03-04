# Base de Datos - MedHistory

Este directorio contiene los scripts SQL para configurar la base de datos PostgreSQL.

## 📁 Archivos

- **schema.sql**: Esquema completo de la base de datos (11 tablas)
- **datos_prueba.sql**: Datos de ejemplo para desarrollo y testing
- **reinstalar_completo.sql**: Script todo-en-uno (limpia + crea tablas)
- **limpiar_base_datos.sql**: Elimina todas las tablas existentes
- **ejecutar_sql.ps1**: Script PowerShell para automatizar la instalación

## 🚀 Instalación Rápida

### Opción 1: Todo-en-Uno (Más Fácil)

Desde **pgAdmin** o **Query Tool**, ejecutar en orden:

```sql
-- 1. Ejecutar reinstalar_completo.sql (crea las 11 tablas)
-- 2. Ejecutar datos_prueba.sql (inserta datos de prueba)
```

### Opción 2: PowerShell Automatizado

```powershell
cd database
.\ejecutar_sql.ps1
```

### Opción 3: Paso a Paso (Manual)

```powershell
cd database

# 1. Limpiar tablas existentes (opcional)
psql -U postgres -d medhistory -f limpiar_base_datos.sql

# 2. Crear estructura
psql -U postgres -d medhistory -f schema.sql

# 3. Insertar datos
psql -U postgres -d medhistory -f datos_prueba.sql
```

## 📊 Estructura de la Base de Datos

**11 Tablas creadas:**

### Tablas Principales
- `administradores` - Usuarios admin del sistema
- `medicos` - Tabla padre (médicos generales + especialistas)
- `medicos_generales` - Médicos de cabecera
- `medicos_especialistas` - Especialistas (cardiólogos, dermatólogos, etc.)
- `pacientes` - Pacientes con médico general asignado

### Tablas de Historial Clínico
- `enfermedades_catalogo` - Catálogo de enfermedades
- `perfiles_salud` - Información detallada de salud
- `antecedentes_familiares` - Historial familiar
- `consultas` - Registro de consultas médicas
- `recordatorios` - Recordatorios de medicación/citas
- `auditoria_logs` - Logs de auditoría y trazabilidad

## 🔐 Credenciales de Prueba

Todos los usuarios usan contraseña: **Admin123**

- **Admin**: claudia.mateo@admin.com
- **Médico General**: elena.fernandez@clinica.com
- **Paciente**: maria.perez@email.com

## 📝 Datos de Prueba

- 2 Administradores
- 3 Médicos Generales
- 4 Médicos Especialistas (Cardiología, Dermatología, Traumatología, Ginecología)
- 5 Pacientes (todos con médico general asignado automáticamente)
- 15 Enfermedades en catálogo
- 5 Perfiles de salud completos
- 5 Antecedentes familiares
- 5 Consultas médicas
- 5 Recordatorios
- 5 Logs de auditoría

## ⚙️ Configuración PostgreSQL

Si `psql` no está en el PATH:

```powershell
# Agregar PostgreSQL al PATH (sesión actual)
$env:Path += ";C:\Program Files\PostgreSQL\18\bin"
```

## ✅ Verificación

Para verificar que todo se instaló correctamente:

```powershell
psql -U postgres -d medhistory -c "\dt"
```

Deberías ver 11 tablas listadas.

---

**Autoras**: Yousra Jebari & Claudia Mateo  
**Fecha**: Marzo 2026
