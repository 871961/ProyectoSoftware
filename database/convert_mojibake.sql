-- =============================================================================
-- Corregir doble-codificación UTF-8 (Mojibake) en consultas
-- =============================================================================

-- Función para convertir doble-codificación UTF-8
DO $$
DECLARE
    rec RECORD;
BEGIN
    -- Para cada consulta con mojibake
    FOR rec IN 
        SELECT id_consulta, diagnostico 
        FROM consultas 
        WHERE diagnostico LIKE '%Ã%'
    LOOP
        -- Convertir de vuelta: las secuencias Ã¡, Ã©, etc. son UTF-8 bytes interpretados como latin1
        -- Necesitamos: 1) convertir a bytea, 2) interpretar como UTF-8
        UPDATE consultas
        SET diagnostico = CONVERT(
            CAST(CONVERT(rec.diagnostico::bytea, 'UTF8', 'LATIN1') AS text),
            'LATIN1',
            'UTF8'
        )
        WHERE id_consulta = rec.id_consulta;
    END LOOP;
    
    RAISE NOTICE 'Conversión completada';
END $$;

-- Verificación final
SELECT diagnostico FROM consultas 
WHERE id_paciente IN ('DEP-MARIA-001', 'DEP-MARIA-002', 'DEP-MARIA-003')
ORDER BY id_consulta;
