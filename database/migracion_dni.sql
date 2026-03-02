-- =============================================================================
-- Script de Migración: Cambiar id_paciente (SERIAL) a dni (VARCHAR) como PK
-- Sistema: MedHistory
-- Fecha: Marzo 2026
-- Autoras: Yousra y Claudia
-- =============================================================================
-- Este script migra la tabla pacientes de usar id_paciente como clave primaria
-- a usar dni (DNI/NIE) como clave primaria.
-- =============================================================================

-- ADVERTENCIA: Este script modifica la estructura de la base de datos
-- IMPORTANTE: Hacer backup antes de ejecutar este script
-- =============================================================================

-- Paso 1: Mostrar información antes de la migración
SELECT 'Pacientes antes de migración:' as info, COUNT(*) as total FROM pacientes;

-- Paso 2: Eliminar las foreign keys que referencian a pacientes
ALTER TABLE perfiles_salud DROP CONSTRAINT IF EXISTS fk_paciente_perfil;
ALTER TABLE antecedentes_familiares DROP CONSTRAINT IF EXISTS fk_paciente_antecedente;
ALTER TABLE consultas DROP CONSTRAINT IF EXISTS fk_paciente_consulta;
ALTER TABLE auditoria_logs DROP CONSTRAINT IF EXISTS fk_auditoria_paciente;

-- Paso 3: Añadir columna DNI temporal a la tabla pacientes
ALTER TABLE pacientes ADD COLUMN IF NOT EXISTS dni VARCHAR(20);

-- Paso 4: Opcional - Si ya tienes datos, puedes generar DNIs temporales
-- Descomenta las siguientes líneas si necesitas generar DNIs de prueba
-- UPDATE pacientes SET dni = LPAD(id_paciente::TEXT, 8, '0') || 'Z' WHERE dni IS NULL;

-- Paso 5: Hacer DNI NOT NULL después de llenar los datos
-- ALTER TABLE pacientes ALTER COLUMN dni SET NOT NULL;

-- Paso 6: Eliminar la clave primaria antigua
ALTER TABLE pacientes DROP CONSTRAINT IF EXISTS pacientes_pkey;

-- Paso 7: Crear nueva clave primaria con DNI
ALTER TABLE pacientes ADD PRIMARY KEY (dni);

-- Paso 8: Eliminar la columna id_paciente antigua (OPCIONAL - hacer con cuidado)
-- ALTER TABLE pacientes DROP COLUMN IF EXISTS id_paciente;

-- Paso 9: Cambiar tipo de columna id_paciente en tablas relacionadas
ALTER TABLE perfiles_salud ALTER COLUMN id_paciente TYPE VARCHAR(20);
ALTER TABLE antecedentes_familiares ALTER COLUMN id_paciente TYPE VARCHAR(20);
ALTER TABLE consultas ALTER COLUMN id_paciente TYPE VARCHAR(20);
ALTER TABLE auditoria_logs ALTER COLUMN id_paciente TYPE VARCHAR(20);
ALTER TABLE auditoria_logs ALTER COLUMN registro_id TYPE VARCHAR(50);

-- Paso 10: Recrear las foreign keys con las nuevas referencias
ALTER TABLE perfiles_salud 
    ADD CONSTRAINT fk_paciente_perfil 
    FOREIGN KEY (id_paciente) REFERENCES pacientes(dni);

ALTER TABLE antecedentes_familiares 
    ADD CONSTRAINT fk_paciente_antecedente 
    FOREIGN KEY (id_paciente) REFERENCES pacientes(dni);

ALTER TABLE consultas 
    ADD CONSTRAINT fk_paciente_consulta 
    FOREIGN KEY (id_paciente) REFERENCES pacientes(dni);

ALTER TABLE auditoria_logs 
    ADD CONSTRAINT fk_auditoria_paciente 
    FOREIGN KEY (id_paciente) REFERENCES pacientes(dni);

-- Paso 11: Crear índice en DNI para mejorar rendimiento
CREATE INDEX IF NOT EXISTS idx_pacientes_dni ON pacientes(dni);

-- Paso 12: Verificar la migración
SELECT 'Pacientes después de migración:' as info, COUNT(*) as total FROM pacientes;
SELECT 'Estructura de la tabla pacientes:' as info;
\d pacientes;

-- =============================================================================
-- NOTAS IMPORTANTES:
-- =============================================================================
-- 1. Si ya tienes datos en la base de datos, necesitarás:
--    a) Exportar los datos con los DNIs reales
--    b) Actualizar la columna 'dni' con los valores correctos
--    c) Actualizar las foreign keys en las tablas relacionadas
--
-- 2. Para una instalación nueva, simplemente ejecuta schema.sql que ya tiene
--    la estructura correcta con DNI como clave primaria.
--
-- 3. Para actualizar las foreign keys en tablas relacionadas con datos existentes:
--    UPDATE perfiles_salud ps 
--    SET id_paciente = p.dni 
--    FROM pacientes p 
--    WHERE ps.id_paciente::INT = p.id_paciente;
--    
--    (Adaptar para cada tabla: antecedentes_familiares, consultas, auditoria_logs)
--
-- =============================================================================
