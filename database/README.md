# Base de Datos - MedHistory

Este directorio contiene los scripts SQL y herramientas para configurar la base de datos.

## Archivos

- **schema.sql**: Crea todas las tablas del sistema (pacientes, médicos, administradores, etc.)
- **datos_prueba.sql**: Carga datos de ejemplo para probar el sistema
- **ejecutar_sql.ps1**: Script de PowerShell que facilita la ejecución de los archivos SQL

## Instalación de la Base de Datos

### Opción 1: Usando el script de PowerShell (Recomendado)

Desde el directorio raíz del proyecto:

```powershell
# Ejecutar ambos archivos (schema y datos)
.\database\ejecutar_sql.ps1

# O ejecutar solo el schema
.\database\ejecutar_sql.ps1 schema

# O ejecutar solo los datos
.\database\ejecutar_sql.ps1 datos
```

### Opción 2: Manualmente con psql

Si `psql` está en el PATH del sistema:

```powershell
psql -U postgres -d medhistory -f database/schema.sql
psql -U postgres -d medhistory -f database/datos_prueba.sql
```

Si `psql` NO está en el PATH, usar la ruta completa:

```powershell
& "C:\Program Files\PostgreSQL\18\bin\psql.exe" -U postgres -d medhistory -f database/schema.sql
& "C:\Program Files\PostgreSQL\18\bin\psql.exe" -U postgres -d medhistory -f database/datos_prueba.sql
```

### Opción 3: Agregar PostgreSQL al PATH

1. Buscar la carpeta `bin` de PostgreSQL (ejemplo: `C:\Program Files\PostgreSQL\18\bin`)
2. Editar las variables de entorno del sistema
3. Añadir la ruta a la variable PATH
4. Reiniciar PowerShell

Comando para agregar al PATH de sesión actual:

```powershell
$env:Path += ";C:\Program Files\PostgreSQL\18\bin"
```

## Verificación

Después de ejecutar los scripts, acceder a:

```
http://medHistory.local/test_db.php
```

Este script de pruebas verificará:
- Conexión a PostgreSQL
- Creación de administradores (Yousra y Claudia)
- Creación de médicos (2)
- Creación de pacientes (5)
- Catálogo de enfermedades (15)
- Borrado lógico
- Reactivación de pacientes inactivos

## Credenciales de Prueba

### Administradores
- yousra@clinica.com / admin123
- claudia@clinica.com / admin123

### Médicos
- elena.fernandez@clinica.com / medico123
- miguel.rodriguez@clinica.com / medico123

### Pacientes
- maria.perez@email.com / paciente123
- jose.martin@email.com / paciente123
- laura.ruiz@email.com / paciente123
- antonio.sanchez@email.com / paciente123
- patricia.moreno@email.com / paciente123

## Estructura de la Base de Datos

El esquema implementa:
- **Borrado lógico**: Las tablas principales usan `activo` y `fecha_baja`
- **Sin CASCADE**: Las FK no usan ON DELETE CASCADE para preservar historial
- **Auditoría**: Tabla `auditoria_logs` para trazabilidad GDPR/LOPD
- **Catálogo de enfermedades**: Para antecedentes familiares
- **Recordatorios**: Asociados a consultas médicas

## Notas

- El script `test_db.php` puede ejecutarse múltiples veces sin duplicar datos
- Los datos ya existentes no se recrean
- Los pacientes inactivos se reactivan automáticamente en cada ejecución de test_db.php
