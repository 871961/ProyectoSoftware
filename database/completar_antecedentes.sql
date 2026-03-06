-- =============================================================================
-- Script para completar antecedentes familiares de pacientes
-- Añade antecedentes a pacientes que tengan menos de 3 registrados
-- Fecha: Marzo 2026
-- =============================================================================

DO $$
DECLARE
    pac RECORD;
    enf_hipertension INT;
    enf_diabetes INT;
    enf_cancer INT;
    enf_cardiopatia INT;
    enf_asma INT;
    enf_depresion INT;
    enf_artritis INT;
    num_antecedentes INT;
BEGIN
    -- Obtener IDs de enfermedades comunes
    SELECT id_enfermedad INTO enf_hipertension FROM enfermedades_catalogo WHERE LOWER(nombre_patologia) LIKE '%hipertension%' LIMIT 1;
    SELECT id_enfermedad INTO enf_diabetes FROM enfermedades_catalogo WHERE LOWER(nombre_patologia) LIKE '%diabetes%' LIMIT 1;
    SELECT id_enfermedad INTO enf_cancer FROM enfermedades_catalogo WHERE LOWER(nombre_patologia) LIKE '%cancer%' OR LOWER(nombre_patologia) LIKE '%mama%' LIMIT 1;
    SELECT id_enfermedad INTO enf_cardiopatia FROM enfermedades_catalogo WHERE LOWER(nombre_patologia) LIKE '%cardio%' OR LOWER(nombre_patologia) LIKE '%corazon%' LIMIT 1;
    SELECT id_enfermedad INTO enf_asma FROM enfermedades_catalogo WHERE LOWER(nombre_patologia) LIKE '%asma%' LIMIT 1;
    SELECT id_enfermedad INTO enf_depresion FROM enfermedades_catalogo WHERE LOWER(nombre_patologia) LIKE '%depresion%' LIMIT 1;
    SELECT id_enfermedad INTO enf_artritis FROM enfermedades_catalogo WHERE LOWER(nombre_patologia) LIKE '%artritis%' OR LOWER(nombre_patologia) LIKE '%artrosis%' LIMIT 1;

    -- Si no existen enfermedades específicas, usar IDs genéricos
    IF enf_hipertension IS NULL THEN enf_hipertension := 1; END IF;
    IF enf_diabetes IS NULL THEN enf_diabetes := 2; END IF;
    IF enf_cancer IS NULL THEN enf_cancer := 3; END IF;
    IF enf_cardiopatia IS NULL THEN enf_cardiopatia := 4; END IF;
    IF enf_asma IS NULL THEN enf_asma := 5; END IF;
    IF enf_depresion IS NULL THEN enf_depresion := 6; END IF;
    IF enf_artritis IS NULL THEN enf_artritis := 7; END IF;

    -- Recorrer todos los pacientes activos
    FOR pac IN SELECT dni, nombre, apellidos FROM pacientes WHERE activo = TRUE
    LOOP
        -- Contar cuántos antecedentes tiene este paciente
        SELECT COUNT(*) INTO num_antecedentes 
        FROM antecedentes_familiares 
        WHERE id_paciente = pac.dni AND activo = TRUE;

        -- Si tiene menos de 3, añadir antecedentes adicionales
        IF num_antecedentes < 3 THEN
            RAISE NOTICE 'Completando antecedentes para: % % (% existentes)', pac.nombre, pac.apellidos, num_antecedentes;

            -- Añadir hasta 5 antecedentes
            
            -- 1. Hipertensión en padre (si no existe ya)
            IF NOT EXISTS (
                SELECT 1 FROM antecedentes_familiares 
                WHERE id_paciente = pac.dni 
                  AND id_enfermedad = enf_hipertension 
                  AND parentesco = 'padre'
                  AND activo = TRUE
            ) THEN
                INSERT INTO antecedentes_familiares (
                    id_paciente, id_enfermedad, parentesco, 
                    lado_familiar, edad_diagnóstico, notas_adicionales, activo
                ) VALUES (
                    pac.dni, enf_hipertension, 'padre',
                    'paterno', 55, 'Diagnosticado en chequeo de rutina', TRUE
                );
            END IF;

            -- 2. Diabetes en madre (si no existe ya)
            IF NOT EXISTS (
                SELECT 1 FROM antecedentes_familiares 
                WHERE id_paciente = pac.dni 
                  AND id_enfermedad = enf_diabetes 
                  AND parentesco = 'madre'
                  AND activo = TRUE
            ) THEN
                INSERT INTO antecedentes_familiares (
                    id_paciente, id_enfermedad, parentesco, 
                    lado_familiar, edad_diagnóstico, notas_adicionales, activo
                ) VALUES (
                    pac.dni, enf_diabetes, 'madre',
                    'materno', 60, 'Tipo 2, controlada con medicación', TRUE
                );
            END IF;

            -- 3. Cáncer en abuelo paterno (probabilidad 40%)
            IF random() > 0.6 AND NOT EXISTS (
                SELECT 1 FROM antecedentes_familiares 
                WHERE id_paciente = pac.dni 
                  AND id_enfermedad = enf_cancer
                  AND activo = TRUE
                LIMIT 1
            ) THEN
                INSERT INTO antecedentes_familiares (
                    id_paciente, id_enfermedad, parentesco, 
                    lado_familiar, edad_diagnóstico, activo
                ) VALUES (
                    pac.dni, enf_cancer, 'abuelo_paterno',
                    'paterno', 68, TRUE
                );
            END IF;

            -- 4. Cardiopatía en abuela materna (probabilidad 50%)
            IF random() > 0.5 AND NOT EXISTS (
                SELECT 1 FROM antecedentes_familiares 
                WHERE id_paciente = pac.dni 
                  AND id_enfermedad = enf_cardiopatia
                  AND activo = TRUE
                LIMIT 1
            ) THEN
                INSERT INTO antecedentes_familiares (
                    id_paciente, id_enfermedad, parentesco, 
                    lado_familiar, edad_diagnóstico, notas_adicionales, activo
                ) VALUES (
                    pac.dni, enf_cardiopatia, 'abuela_materna',
                    'materno', 72, 'Infarto de miocardio', TRUE
                );
            END IF;

            -- 5. Asma en hermano (probabilidad 30%)
            IF random() > 0.7 AND NOT EXISTS (
                SELECT 1 FROM antecedentes_familiares 
                WHERE id_paciente = pac.dni 
                  AND id_enfermedad = enf_asma
                  AND parentesco IN ('hermano', 'hermana')
                  AND activo = TRUE
            ) THEN
                INSERT INTO antecedentes_familiares (
                    id_paciente, id_enfermedad, parentesco, 
                    edad_diagnóstico, notas_adicionales, activo
                ) VALUES (
                    pac.dni, enf_asma, 'hermano',
                    15, 'Asma alérgica estacional', TRUE
                );
            END IF;

            -- 6. Depresión en tía (probabilidad 25%)
            IF random() > 0.75 AND enf_depresion IS NOT NULL THEN
                INSERT INTO antecedentes_familiares (
                    id_paciente, id_enfermedad, parentesco, 
                    lado_familiar, edad_diagnóstico, activo
                ) VALUES (
                    pac.dni, enf_depresion, 'tia',
                    CASE WHEN random() > 0.5 THEN 'materno' ELSE 'paterno' END,
                    42, TRUE
                );
            END IF;

            -- 7. Artritis en abuelo (probabilidad 35%)
            IF random() > 0.65 AND enf_artritis IS NOT NULL THEN
                INSERT INTO antecedentes_familiares (
                    id_paciente, id_enfermedad, parentesco, 
                    lado_familiar, edad_diagnóstico, notas_adicionales, activo
                ) VALUES (
                    pac.dni, enf_artritis, 
                    CASE WHEN random() > 0.5 THEN 'abuelo_materno' ELSE 'abuelo_paterno' END,
                    CASE WHEN random() > 0.5 THEN 'materno' ELSE 'paterno' END,
                    65, 'Artritis reumatoide', TRUE
                );
            END IF;

        END IF;
    END LOOP;

    RAISE NOTICE 'Proceso completado. Antecedentes familiares actualizados.';
END $$;

-- Verificar resultado
SELECT 
    p.nombre || ' ' || p.apellidos as paciente,
    COUNT(af.id_antecedente) as num_antecedentes
FROM pacientes p
LEFT JOIN antecedentes_familiares af ON p.dni = af.id_paciente AND af.activo = TRUE
WHERE p.activo = TRUE
GROUP BY p.dni, p.nombre, p.apellidos
ORDER BY num_antecedentes, p.apellidos;
