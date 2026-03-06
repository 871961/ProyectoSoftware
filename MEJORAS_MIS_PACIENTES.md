# Rediseño de "Mis Pacientes" - Interfaz Mejorada

## Fecha: 6 de marzo de 2026

## Resumen de cambios

Se ha rediseñado completamente la sección "Mis Pacientes" del dashboard médico para mejorar la experiencia de usuario y la eficiencia en la gestión de pacientes.

## Cambios realizados

### 1. Nueva interfaz de usuario (medico.html)

#### Antes:
- Formularios separados en la misma página
- Selects de pacientes duplicados (uno para perfil de salud, otro para antecedentes)
- No había búsqueda de pacientes
- Información dispersa

#### Después:
- **Buscador de pacientes** en la parte superior con búsqueda en tiempo real
- **Layout de 2 columnas**:
  - **Columna izquierda**: Lista de pacientes clickeable con avatares y datos básicos
  - **Columna derecha**: Información detallada del paciente seleccionado
- **Resumen del perfil de salud**: Altura, peso, IMC, actividad física, alergias destacadas
- **Resumen de antecedentes familiares**: Primeros 3 antecedentes con contador
- **Dos botones de acción**:
  - 🫀 Gestionar Perfil de Salud (modales)
  - 🌳 Gestionar Antecedentes Familiares (modal)

### 2. Sistema de modales

Los formularios de perfil de salud y antecedentes familiares ahora se abren en modales:

**Modal de Perfil de Salud:**
- Título con información del paciente
- Badge de IMC actualizado
- Todos los campos (altura, peso, actividad, tabaco, alcohol, alergias, enfermedades)
- Botones: Guardar y Cargar perfil existente

**Modal de Antecedentes Familiares:**
- Título con información del paciente
- Formulario para añadir nuevo antecedente
- Lista completa de antecedentes registrados con botones de eliminación

### 3. Nuevo archivo JavaScript: mis-pacientes.js

**Clase `MisPacientesManager`:**

```javascript
class MisPacientesManager {
    // Gestiona toda la lógica de la nueva interfaz
    
    cargarPacientes()          // Carga lista de pacientes desde el backend
    renderizarListaPacientes() // Muestra pacientes con avatares
    filtrarPacientes()         // Búsqueda en tiempo real
    seleccionarPaciente()      // Click en paciente → muestra info
    mostrarInfoPaciente()      // Avatar, nombre, DNI, email
    cargarPerfilSalud()        // Carga perfil del paciente
    mostrarPerfilSalud()       // Muestra altura, peso, IMC, alergias
    mostrarAntecedentesResumen() // Primeros 3 antecedentes
    abrirModalPerfilSalud()    // Abre modal con formulario
    abrirModalAntecedentes()   // Abre modal de antecedentes
    cerrarModal()              // Cierra modal y recarga info
}
```

**Características:**
- Búsqueda instantánea por DNI, nombre o apellidos
- Avatares con colores únicos basados en DNI
- Sincronización automática entre lista y modales
- Recarga de datos al cerrar modales

### 4. Modificaciones en antecedentes.js

**Cambios:**
- Método `cargarAntecedentes(dniParam = null)` ahora acepta DNI como parámetro
- Se expone `window.antecedentesManager` para acceso global
- Se eliminó la sincronización de selects (ya no es necesaria)

### 5. Nuevo endpoint en PerfilSaludController.php

**Endpoint añadido:**
```php
case 'obtenerPacientes':
    // GET /backend/src/controllers/PerfilSaludController.php?accion=obtenerPacientes
    // Retorna lista de todos los pacientes activos
    // Solo accesible para médicos
    // Retorna: dni, nombre, apellidos, email, fecha_nacimiento
```

**Endpoint mejorado:**
```php
case 'obtener':
    // GET /backend/src/controllers/PerfilSaludController.php?accion=obtener&dni_paciente=XXX
    // Retorna perfil de salud de un paciente específico
    // Reemplaza el antiguo 'obtener_por_paciente'
```

## Flujo de usuario

### Paso 1: Visualizar pacientes
1. El médico entra al tab "Mis Pacientes"
2. Se carga automáticamente la lista de todos los pacientes
3. Cada paciente muestra: avatar, nombre completo, DNI

### Paso 2: Buscar paciente
1. El médico escribe en el buscador
2. La lista se filtra en tiempo real
3. Búsqueda por: DNI, nombre o apellidos

### Paso 3: Seleccionar paciente
1. El médico hace clic en un paciente de la lista
2. Se resalta el paciente seleccionado (fondo azul claro)
3. Se muestra información en el panel derecho:
   - Avatar grande con iniciales
   - Nombre completo, DNI, email
   - Badge de estado (Activo)
   - Perfil de salud resumido (4 tarjetas)
   - Alergias destacadas (si las hay)
   - Antecedentes familiares (primeros 3)
   - Dos botones de acción

### Paso 4a: Gestionar perfil de salud
1. Clic en "Gestionar Perfil de Salud"
2. Se abre modal con formulario
3. Se cargan datos existentes automáticamente
4. El médico edita campos
5. Clic en "Guardar"
6. Modal se cierra
7. Info del paciente se recarga automáticamente

### Paso 4b: Gestionar antecedentes
1. Clic en "Gestionar Antecedentes Familiares"
2. Se abre modal con:
   - Formulario para añadir antecedente
   - Lista completa de antecedentes registrados
3. El médico puede:
   - Añadir nuevo antecedente
   - Ver todos los antecedentes
   - Eliminar antecedentes (con confirmación)
4. Al cerrar modal, info se recarga

## Ventajas del nuevo diseño

### Experiencia de usuario
- ✅ **Más intuitivo**: Click directo en paciente en lugar de selects
- ✅ **Búsqueda rápida**: Encontrar paciente sin scroll
- ✅ **Vista resumida**: Ver datos clave sin abrir formularios
- ✅ **Alerta visual**: Alergias destacadas en rojo
- ✅ **Modales**: Formularios en ventanas emergentes sin perder contexto

### Eficiencia
- ✅ **Menos clics**: Información visible inmediatamente
- ✅ **Sin duplicación**: Un solo selector de pacientes
- ✅ **Sincronización automática**: Datos actualizados al cerrar modales
- ✅ **Carga inteligente**: Solo se cargan datos del paciente seleccionado

### Diseño
- ✅ **Avatares de colores**: Identificación visual rápida
- ✅ **Responsive**: Funciona en móviles (columna única)
- ✅ **Iconos Lucide**: Interfaz moderna y profesional
- ✅ **Badges y tarjetas**: Información organizada visualmente

## Archivos modificados

```
frontend/
  static/
    medico.html               [MODIFICADO] - Nueva UI completa
    js/
      mis-pacientes.js        [NUEVO] - Gestor de la interfaz
      antecedentes.js         [MODIFICADO] - Accepta DNI como parámetro

backend/
  src/
    controllers/
      PerfilSaludController.php  [MODIFICADO] - Nuevos endpoints
```

## Testing

### Probar la nueva interfaz:

1. **Iniciar sesión como médico**:
   - Email: elena.fernandez@clinica.com
   - Password: Medico123

2. **Ir al tab "Mis Pacientes"**

3. **Verificar lista de pacientes**:
   - [ ] Se muestran todos los pacientes
   - [ ] Cada paciente tiene avatar con iniciales
   - [ ] Colores de avatar son variados

4. **Probar búsqueda**:
   - [ ] Buscar por DNI (ej: "12345")
   - [ ] Buscar por nombre (ej: "Maria")
   - [ ] Buscar por apellidos (ej: "Perez")
   - [ ] Borrar búsqueda → todos los pacientes vuelven

5. **Seleccionar paciente**:
   - [ ] Clic en un paciente
   - [ ] Paciente se resalta en azul
   - [ ] Panel derecho muestra información
   - [ ] Perfil de salud con datos (si existe)
   - [ ] Antecedentes resumidos (si existen)
   - [ ] Alergias destacadas en rojo (si existen)

6. **Modal de Perfil de Salud**:
   - [ ] Clic en botón "Gestionar Perfil de Salud"
   - [ ] Modal se abre
   - [ ] Nombre del paciente visible
   - [ ] Datos existentes se cargan
   - [ ] Editar campos y guardar
   - [ ] Mensaje de éxito
   - [ ] Cerrar modal con X
   - [ ] Info actualizada en panel derecho

7. **Modal de Antecedentes**:
   - [ ] Clic en botón "Gestionar Antecedentes Familiares"
   - [ ] Modal se abre
   - [ ] Lista de antecedentes visible
   - [ ] Añadir nuevo antecedente
   - [ ] Antecedente aparece en lista
   - [ ] Eliminar antecedente (con confirmación)
   - [ ] Cerrar modal
   - [ ] Info actualizada en panel derecho

8. **Click fuera del modal**:
   - [ ] Modal se cierra al hacer clic en el fondo oscuro

## Compatibilidad

- ✅ Navegadores modernos (Chrome, Firefox, Edge, Safari)
- ✅ Responsive design (móviles, tablets, desktop)
- ✅ Compatible con código existente de dashboard-medico.js
- ✅ Integración perfecta con antecedentes.js

## Próximas mejoras sugeridas

- [ ] Añadir foto de perfil real del paciente
- [ ] Exportar informe completo del paciente (PDF)
- [ ] Gráfico de evolución de peso/IMC
- [ ] Filtros adicionales (edad, alergias, enfermedades)
- [ ] Vista de árbol genealógico para antecedentes
- [ ] Ordenar pacientes por nombre, DNI o última consulta
- [ ] Paginación para médicos con muchos pacientes

---

**Autoras**: Claudia y Yousra  
**Fecha de implementación**: 6 de marzo de 2026  
**Versión**: 2.0
