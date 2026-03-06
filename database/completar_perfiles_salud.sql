-- Script para completar perfiles de salud faltantes
-- Fecha: Marzo 2026
-- Inserta registros en perfiles_salud para pacientes activos que no tienen perfil

-- Insertar perfil para PacienteXX (25209659A)
INSERT INTO perfiles_salud (
    id_paciente,
    altura_cm,
    peso_kg,
    alergias,
    enfermedades,
    consumo_tabaco,
    consumo_alcohol,
    actividad_fisica,
    fecha_creacion,
    fecha_actualizacion
) VALUES (
    '25209659A',
    170,
    68,
    'Ninguna conocida',
    'Ninguna diagnosticada',
    'No fumador',
    'Ocasional',
    'Moderada',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
) ON CONFLICT (id_paciente) DO NOTHING;

-- Insertar perfil para Claudia (77220038E)
INSERT INTO perfiles_salud (
    id_paciente,
    altura_cm,
    peso_kg,
    alergias,
    enfermedades,
    consumo_tabaco,
    consumo_alcohol,
    actividad_fisica,
    fecha_creacion,
    fecha_actualizacion
) VALUES (
    '77220038E',
    165,
    62,
    'Alergia a frutos secos',
    'Ninguna diagnosticada',
    'No fumador',
    'No consume',
    'Activa',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
) ON CONFLICT (id_paciente) DO NOTHING;

-- Actualizar altura_cm y peso_kg para perfiles existentes que no tienen estos datos
UPDATE perfiles_salud
SET 
    altura_cm = 168,
    peso_kg = 70,
    consumo_tabaco = COALESCE(consumo_tabaco, 'No fumador'),
    consumo_alcohol = COALESCE(consumo_alcohol, 'Ocasional'),
    actividad_fisica = COALESCE(actividad_fisica, 'Moderada'),
    fecha_actualizacion = CURRENT_TIMESTAMP
WHERE id_paciente = '12345678A' AND (altura_cm IS NULL OR peso_kg IS NULL);

UPDATE perfiles_salud
SET 
    altura_cm = 175,
    peso_kg = 80,
    consumo_tabaco = COALESCE(consumo_tabaco, 'No fumador'),
    consumo_alcohol = COALESCE(consumo_alcohol, 'Moderado'),
    actividad_fisica = COALESCE(actividad_fisica, 'Ligera'),
    fecha_actualizacion = CURRENT_TIMESTAMP
WHERE id_paciente = '45678901D' AND (altura_cm IS NULL OR peso_kg IS NULL);

UPDATE perfiles_salud
SET 
    altura_cm = 162,
    peso_kg = 58,
    consumo_tabaco = COALESCE(consumo_tabaco, 'No fumador'),
    consumo_alcohol = COALESCE(consumo_alcohol, 'No consume'),
    actividad_fisica = COALESCE(actividad_fisica, 'Activa'),
    fecha_actualizacion = CURRENT_TIMESTAMP
WHERE id_paciente = '34567890C' AND (altura_cm IS NULL OR peso_kg IS NULL);

UPDATE perfiles_salud
SET 
    altura_cm = 160,
    peso_kg = 55,
    consumo_tabaco = COALESCE(consumo_tabaco, 'No fumador'),
    consumo_alcohol = COALESCE(consumo_alcohol, 'Ocasional'),
    actividad_fisica = COALESCE(actividad_fisica, 'Moderada'),
    fecha_actualizacion = CURRENT_TIMESTAMP
WHERE id_paciente = '56789012E' AND (altura_cm IS NULL OR peso_kg IS NULL);

-- Verificar resultados
SELECT 
    p.dni, 
    p.nombre || ' ' || p.apellidos as paciente,
    ps.altura_cm,
    ps.peso_kg,
    ROUND((ps.peso_kg / ((ps.altura_cm / 100.0) * (ps.altura_cm / 100.0)))::numeric, 2) as imc,
    ps.alergias,
    ps.consumo_tabaco,
    ps.actividad_fisica
FROM pacientes p
LEFT JOIN perfiles_salud ps ON p.dni = ps.id_paciente
WHERE p.activo = TRUE
ORDER BY p.nombre;

\echo ''
\echo 'Perfiles de salud completados correctamente.'
