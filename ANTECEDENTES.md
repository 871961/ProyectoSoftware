# Gestión de Antecedentes Familiares

## Descripción
Sistema completo para que los médicos registren y consulten antecedentes médicos familiares de los pacientes.

## Características Implementadas

### 1. Base de Datos
- ✅ Tabla `antecedentes_familiares` actualizada con columnas:
  - `id_antecedente` (PK)
  - `id_paciente` (FK a pacientes.dni)
  - `id_enfermedad` (FK a enfermedades_catalogo)
  - `parentesco` (padre, madre, hermano, abuelos, tíos, primos, etc.)
  - `lado_familiar` (paterno, materno, ambos)
  - `edad_diagnóstico` (edad en que el familiar fue diagnosticado)
  - `notas_adicionales` (información adicional)
  - `fecha_registro` (timestamp automático)
  - `activo` (borrado lógico)

### 2. Backend
- ✅ **AntecedentesController.php**: API REST para CRUD de antecedentes
  - `obtenerPorPaciente`: Lista antecedentes de un paciente
  - `obtenerEnfermedades`: Catálogo de enfermedades
  - `registrar`: Crear nuevo antecedente
  - `actualizar`: Modificar antecedente existente
  - `eliminar`: Borrado lógico de antecedente

### 3. Frontend
- ✅ **Interfaz en Tab "Mis Pacientes"**:
  - Formulario para registrar nuevos antecedentes
  - Selects para:
    - Paciente
    - Enfermedad (agrupadas por categoría)
    - Parentesco (13 opciones)
    - Lado familiar
  - Lista visual de antecedentes registrados con:
    - Badges de información
    - Notas adicionales
    - Botón de eliminación
  - Sincronización automática con selector de pacientes del perfil de salud

### 4. JavaScript
- ✅ **antecedentes.js**: Gestión completa del frontend
  - Carga de catálogo de enfermedades
  - CRUD de antecedentes vía API
  - Renderizado dinámico de antecedentes
  - Validaciones y mensajes de feedback

## Instalación y Configuración

### Paso 1: Actualizar Base de Datos
Ejecuta el script PowerShell que actualiza la estructura y pobla datos de prueba:

```powershell
.\database\ejecutar_act_antecedentes.ps1
```

Este script hace dos cosas:
1. Añade las columnas nuevas a la tabla `antecedentes_familiares`
2. Crea antecedentes de prueba para todos los pacientes existentes

### Paso 2: Verificar Archivos
Asegúrate de que existan estos archivos:
- `backend/src/controllers/AntecedentesController.php`
- `frontend/static/js/antecedentes.js`
- La interfaz está integrada en `frontend/static/medico.html`

### Paso 3: Probar Funcionalidad
1. Inicia sesión como médico
2. Ve al tab "Mis Pacientes"
3. Desplázate hasta la sección "Antecedentes Familiares"
4. Selecciona un paciente para ver sus antecedentes
5. Añade un nuevo antecedente utilizando el formulario

## Uso del Sistema

### Para Médicos Generales
1. **Ver antecedentes de un paciente**:
   - Selecciona el paciente en el dropdown
   - Los antecedentes se cargan automáticamente

2. **Registrar nuevo antecedente**:
   - Selecciona el paciente
   - Elige la enfermedad del catálogo
   - Especifica el parentesco (obligatorio)
   - Opcionalmente: lado familiar, edad al diagnóstico, notas
   - Haz clic en "Registrar Antecedente"

3. **Eliminar antecedente**:
   - Haz clic en el botón de eliminar (ícono de papelera)
   - Confirma la eliminación

### Tipos de Parentesco Disponibles
- Padre / Madre
- Hermano / Hermana
- Abuelo Paterno / Abuela Paterna
- Abuelo Materno / Abuela Materna
- Tío / Tía
- Primo / Prima
- Otro

### Lado Familiar
- **Paterno**: Familia por parte del padre
- **Materno**: Familia por parte de la madre
- **Ambos**: Cuando afecta a ambas ramas familiares
- **No especificado**: Si no se conoce o no es relevante

## API Endpoints

### GET: Obtener antecedentes de un paciente
```
GET /backend/src/controllers/AntecedentesController.php?accion=obtenerPorPaciente&dni_paciente=12345678A
```

### GET: Obtener catálogo de enfermedades
```
GET /backend/src/controllers/AntecedentesController.php?accion=obtenerEnfermedades
```

### POST: Registrar nuevo antecedente
```
POST /backend/src/controllers/AntecedentesController.php?accion=registrar
Content-Type: application/json

{
  "id_paciente": "12345678A",
  "id_enfermedad": 1,
  "parentesco": "padre",
  "lado_familiar": "paterno",
  "edad_diagnóstico": 55,
  "notas_adicionales": "Diagnosticado en chequeo rutinario"
}
```

### POST: Actualizar antecedente
```
POST /backend/src/controllers/AntecedentesController.php?accion=actualizar
Content-Type: application/json

{
  "id_antecedente": 10,
  "edad_diagnóstico": 58,
  "notas_adicionales": "Actualización de edad"
}
```

### GET: Eliminar antecedente
```
GET /backend/src/controllers/AntecedentesController.php?accion=eliminar&id_antecedente=10
```

## Scripts SQL

### actualizar_antecedentes.sql
Añade las columnas nuevas a la tabla existente sin perder datos.

### poblar_antecedentes.sql
Crea antecedentes de prueba para todos los pacientes:
- Hipertensión en padre (todos los pacientes)
- Diabetes en madre (todos los pacientes)
- Cáncer en abuelo paterno (50% de pacientes)
- Cardiopatía en abuela materna (40% de pacientes)
- Asma en hermano (30% de pacientes)

## Notas Técnicas

### Seguridad
- Todas las acciones requieren autenticación como médico
- Validación de sesión en cada endpoint
- Borrado lógico (no se eliminan registros físicamente)

### Sincronización
- El selector de pacientes se sincroniza automáticamente con el del perfil de salud
- Al cambiar de paciente en cualquier selector, ambos se actualizan

### UX
- Mensajes de feedback en verde (éxito) o rojo (error)
- Confirmación antes de eliminar
- Iconos de Lucide para mejor visualización
- Agrupación de enfermedades por categoría en el select

## Troubleshooting

### Los antecedentes no se cargan
Verifica que:
1. La tabla esté actualizada (ejecuta `actualizar_antecedentes.sql`)
2. El controlador PHP esté en la ruta correcta
3. La sesión de médico esté activa

### Error al registrar antecedente
Posibles causas:
- Paciente o enfermedad no existen
- Campos requeridos vacíos
- DNI del paciente incorrecto

### El selector de enfermedades está vacío
Ejecuta el script de datos de prueba para poblar el catálogo de enfermedades.

## Futuras Mejoras
- [ ] Gráfico de árbol genealógico
- [ ] Cálculo automático de riesgo genético
- [ ] Exportación a PDF del historial familiar
- [ ] Filtros avanzados por tipo de enfermedad
- [ ] Estadísticas de prevalencia familiar

---

**Fecha de implementación**: Marzo 2026  
**Autoras**: Yousra y Claudia
