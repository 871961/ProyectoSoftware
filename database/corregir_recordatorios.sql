-- Corrección rápida para completar datos pediátricos
-- Insertar recordatorios sin problemas de codificación

DO $$
DECLARE
    consulta_id INT;
BEGIN
    -- Recordatorios para Santiago sin tildes que puedan causar problemas
    SELECT id_consulta INTO consulta_id
    FROM consultas
    WHERE id_paciente = 'DEP-SAN-180615'
    AND diagnostico LIKE '%crecimiento%'
    LIMIT 1;

    IF consulta_id IS NOT NULL THEN
        INSERT INTO recordatorios (id_consulta, fecha_hora, tipo_recordatorio, razon, estado)
        VALUES
        (consulta_id, NOW() + INTERVAL '6 months', 'Control', 'Control semestral de crecimiento', 'Pendiente'),
        (consulta_id, NOW() + INTERVAL '3 months', 'Otro', 'Continuar vitamina D', 'Pendiente')
        ON CONFLICT DO NOTHING;
    END IF;

    -- Recordatorios adicionales para otros dependientes
    SELECT id_consulta INTO consulta_id
    FROM consultas
    WHERE id_paciente = 'DEP-LUC-160203'
    AND diagnostico LIKE '%dermatitis%'
    LIMIT 1;

    IF consulta_id IS NOT NULL THEN
        INSERT INTO recordatorios (id_consulta, fecha_hora, tipo_recordatorio, razon, estado)
        VALUES
        (consulta_id, NOW() + INTERVAL '2 weeks', 'Control', 'Revision dermatitis', 'Pendiente')
        ON CONFLICT DO NOTHING;
    END IF;

    -- Más recordatorios para completar
    SELECT id_consulta INTO consulta_id
    FROM consultas
    WHERE id_paciente = 'DEP-MAT-201120'
    AND diagnostico LIKE '%alergia%'
    LIMIT 1;

    IF consulta_id IS NOT NULL THEN
        INSERT INTO recordatorios (id_consulta, fecha_hora, tipo_recordatorio, razon, estado)
        VALUES
        (consulta_id, NOW() + INTERVAL '3 months', 'Cita', 'Revision con alergologo', 'Pendiente')
        ON CONFLICT DO NOTHING;
    END IF;

END $$;