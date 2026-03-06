-- =============================================================================
-- Script para poblar antecedentes familiares de pacientes existentes
-- Crea antecedentes de prueba para todos los pacientes activos
-- Fecha: Marzo 2026
-- =============================================================================

-- Primero, obtener IDs de enfermedades comunes para usar en los antecedentes
-- Asumiendo que ya existen enfermedades en el catálogo

-- Función auxiliar para insertar antecedentes
DO $$
DECLARE
    pac RECORD;
    enf_hipertension INT;
    enf_diabetes INT;
    enf_cancer INT;
    enf_cardiopatia INT;
    enf_asma INT;
BEGIN
    -- Obtener IDs de enfermedades comunes (ajustar según tu catálogo)
    SELECT id_enfermedad INTO enf_hipertension FROM enfermedades_catalogo WHERE LOWER(nombre_patologia) LIKE '%hipertensión%' OR LOWER(nombre_patologia) LIKE '%hipertension%' LIMIT 1;
    SELECT id_enfermedad INTO enf_diabetes FROM enfermedades_catalogo WHERE LOWER(nombre_patologia) LIKE '%diabetes%' LIMIT 1;
    SELECT id_enfermedad INTO enf_cancer FROM enfermedades_catalogo WHERE LOWER(nombre_patologia) LIKE '%cáncer%' OR LOWER(nombre_patologia) LIKE '%cancer%' LIMIT 1;
    SELECT id_enfermedad INTO enf_cardiopatia FROM enfermedades_catalogo WHERE LOWER(nombre_patologia) LIKE '%cardio%' LIMIT 1;
    SELECT id_enfermedad INTO enf_asma FROM enfermedades_catalogo WHERE LOWER(nombre_patologia) LIKE '%asma%' LIMIT 1;

    -- Si no hay enfermedades, usar IDs genéricos (ajustar según tu base de datos)
    IF enf_hipertension IS NULL THEN enf_hipertension := 1; END IF;
    IF enf_diabetes IS NULL THEN enf_diabetes := 2; END IF;
    IF enf_cancer IS NULL THEN enf_cancer := 3; END IF;
    IF enf_cardiopatia IS NULL THEN enf_cardiopatia := 4; END IF;
    IF enf_asma IS NULL THEN enf_asma := 5; END IF;

    -- Iterar sobre cada paciente activo
    FOR pac IN SELECT dni FROM pacientes WHERE activo = TRUE LOOP
        -- Antecedente 1: Hipertensión en padre
        IF enf_hipertension IS NOT NULL THEN
            INSERT INTO antecedentes_familiares 
                (id_paciente, id_enfermedad, parentesco, lado_familiar, edad_diagnóstico, notas_adicionales, activo)
            VALUES 
                (pac.dni, enf_hipertension, 'padre', 'paterno', 55, 'Diagnosticado en chequeo rutinario', TRUE)
            ON CONFLICT DO NOTHING;
        END IF;

        -- Antecedente 2: Diabetes en madre
        IF enf_diabetes IS NOT NULL THEN
            INSERT INTO antecedentes_familiares 
                (id_paciente, id_enfermedad, parentesco, lado_familiar, edad_diagnóstico, notas_adicionales, activo)
            VALUES 
                (pac.dni, enf_diabetes, 'madre', 'materno', 48, 'Diabetes tipo 2, controlada con medicación', TRUE)
            ON CONFLICT DO NOTHING;
        END IF;

        -- Antecedente 3: Cáncer en abuelo paterno (solo para algunos pacientes, variedad)
        IF enf_cancer IS NOT NULL AND random() > 0.5 THEN
            INSERT INTO antecedentes_familiares 
                (id_paciente, id_enfermedad, parentesco, lado_familiar, edad_diagnóstico, notas_adicionales, activo)
            VALUES 
                (pac.dni, enf_cancer, 'abuelo_paterno', 'paterno', 72, 'Cáncer de colon, tratamiento exitoso', TRUE)
            ON CONFLICT DO NOTHING;
        END IF;

        -- Antecedente 4: Cardiopatía en abuela materna (para algunos)
        IF enf_cardiopatia IS NOT NULL AND random() > 0.6 THEN
            INSERT INTO antecedentes_familiares 
                (id_paciente, id_enfermedad, parentesco, lado_familiar, edad_diagnóstico, notas_adicionales, activo)
            VALUES 
                (pac.dni, enf_cardiopatia, 'abuela_materna', 'materno', 68, 'Insuficiencia cardíaca', TRUE)
            ON CONFLICT DO NOTHING;
        END IF;

        -- Antecedente 5: Asma en hermano (para algunos)
        IF enf_asma IS NOT NULL AND random() > 0.7 THEN
            INSERT INTO antecedentes_familiares 
                (id_paciente, id_enfermedad, parentesco, lado_familiar, edad_diagnóstico, notas_adicionales, activo)
            VALUES 
                (pac.dni, enf_asma, 'hermano', NULL, 15, 'Asma leve, controlada con inhaladores', TRUE)
            ON CONFLICT DO NOTHING;
        END IF;

    END LOOP;

    RAISE NOTICE 'Antecedentes familiares creados para todos los pacientes activos';
END $$;

-- Verificar resultados
SELECT 
    p.nombre || ' ' || p.apellidos AS paciente,
    COUNT(af.id_antecedente) AS num_antecedentes
FROM pacientes p
LEFT JOIN antecedentes_familiares af ON p.dni = af.id_paciente AND af.activo = TRUE
WHERE p.activo = TRUE
GROUP BY p.dni, p.nombre, p.apellidos
ORDER BY p.apellidos;
