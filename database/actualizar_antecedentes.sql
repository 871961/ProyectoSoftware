-- =============================================================================
-- Script para actualizar estructura de antecedentes_familiares
-- Añade columnas nuevas: lado_familiar, edad_diagnóstico, notas_adicionales,
-- fecha_registro, activo
-- Fecha: Marzo 2026
-- =============================================================================

-- Añadir columnas nuevas si no existen
ALTER TABLE antecedentes_familiares 
ADD COLUMN IF NOT EXISTS lado_familiar VARCHAR(20),
ADD COLUMN IF NOT EXISTS edad_diagnóstico INT,
ADD COLUMN IF NOT EXISTS notas_adicionales TEXT,
ADD COLUMN IF NOT EXISTS fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS activo BOOLEAN DEFAULT TRUE;

-- Migrar datos de observaciones a notas_adicionales si existe la columna observaciones
DO $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'antecedentes_familiares' 
        AND column_name = 'observaciones'
    ) THEN
        UPDATE antecedentes_familiares 
        SET notas_adicionales = observaciones 
        WHERE observaciones IS NOT NULL 
        AND (notas_adicionales IS NULL OR notas_adicionales = '');
        
        -- Opcional: eliminar columna observaciones antigua
        -- ALTER TABLE antecedentes_familiares DROP COLUMN observaciones;
    END IF;
END $$;

-- Establecer valores por defecto para registros existentes
UPDATE antecedentes_familiares 
SET activo = TRUE 
WHERE activo IS NULL;

UPDATE antecedentes_familiares 
SET fecha_registro = CURRENT_TIMESTAMP 
WHERE fecha_registro IS NULL;

-- Verificar estructura actualizada
SELECT column_name, data_type, is_nullable, column_default
FROM information_schema.columns
WHERE table_name = 'antecedentes_familiares'
ORDER BY ordinal_position;
