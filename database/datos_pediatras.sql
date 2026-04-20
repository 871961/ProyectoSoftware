-- =============================================================================
-- MedHistory - Datos de prueba para Pediatras y Dependientes
-- Fecha: Marzo 2026
-- =============================================================================
-- NOTA: Ejecutar después de dependientes_schema.sql
-- =============================================================================

-- Insertar médicos pediatras (si no existen)
DO $$
DECLARE
    pediatra1_id INT;
    pediatra2_id INT;
BEGIN
    -- Pediatra 1
    IF NOT EXISTS (SELECT 1 FROM medicos WHERE email = 'pediatra1@medhistory.es') THEN
        INSERT INTO medicos (nombre, apellidos, email, contrasena_hash, telefono, direccion, num_colegiado, tipo_medico)
        VALUES ('Laura', 'Martínez Ruiz', 'pediatra1@medhistory.es',
                '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password
                '612345678', 'C/ Pediatría 1, Madrid', 'COL-PED-001', 'especialista')
        RETURNING id_medico INTO pediatra1_id;

        INSERT INTO medicos_especialistas (id_medico, especialidad, dependientes_asignados)
        VALUES (pediatra1_id, 'Pediatra', 0);

        RAISE NOTICE 'Pediatra 1 creada con ID: %', pediatra1_id;
    ELSE
        SELECT id_medico INTO pediatra1_id FROM medicos WHERE email = 'pediatra1@medhistory.es';
        RAISE NOTICE 'Pediatra 1 ya existe con ID: %', pediatra1_id;
    END IF;

    -- Pediatra 2
    IF NOT EXISTS (SELECT 1 FROM medicos WHERE email = 'pediatra2@medhistory.es') THEN
        INSERT INTO medicos (nombre, apellidos, email, contrasena_hash, telefono, direccion, num_colegiado, tipo_medico)
        VALUES ('Carlos', 'García López', 'pediatra2@medhistory.es',
                '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password
                '623456789', 'C/ Pediatría 2, Barcelona', 'COL-PED-002', 'especialista')
        RETURNING id_medico INTO pediatra2_id;

        INSERT INTO medicos_especialistas (id_medico, especialidad, dependientes_asignados)
        VALUES (pediatra2_id, 'Pediatra', 0);

        RAISE NOTICE 'Pediatra 2 creado con ID: %', pediatra2_id;
    ELSE
        SELECT id_medico INTO pediatra2_id FROM medicos WHERE email = 'pediatra2@medhistory.es';
        RAISE NOTICE 'Pediatra 2 ya existe con ID: %', pediatra2_id;
    END IF;
END $$;

-- =============================================================================
-- FIN DE DATOS DE PRUEBA PEDIATRAS
-- =============================================================================

-- =============================================================================
-- DATOS DE PRUEBA: PACIENTES DEPENDIENTES (3) + PERFILES, ANTECEDENTES, CONSULTAS, RECORDATORIOS
-- NOTA: Este bloque crea 3 dependientes asignados a tutores existentes y añade datos clínicos básicos.
-- =============================================================================

DO $$
DECLARE
    pediatra_id INT;
    consulta_id INT;
    dni_dep VARCHAR(50);
BEGIN
    -- Buscar un pediatra (especialidad 'Pediatra') si existe, si no, usar el primer especialista disponible
    SELECT me.id_medico INTO pediatra_id
    FROM medicos_especialistas me
    WHERE me.especialidad = 'Pediatra'
    LIMIT 1;

    IF pediatra_id IS NULL THEN
        SELECT id_medico INTO pediatra_id FROM medicos_especialistas LIMIT 1;
    END IF;

    -- Dependiente 1: Santiago Perez Gomez (tutor: Maria - dni 12345678A)
    dni_dep := 'DEP-SAN-180615';
    IF NOT EXISTS (SELECT 1 FROM pacientes WHERE dni = dni_dep) THEN
        INSERT INTO pacientes (dni, nombre, apellidos, email, contrasena_hash, telefono, direccion, fecha_nacimiento, num_seguridad_social, id_medico_general, es_dependiente, dni_tutor, id_pediatra, grupo_sanguineo, alergias, observaciones)
        VALUES (dni_dep, 'Santiago', 'Perez Gomez', 'santiago.perez.dep@medhistory.local', '$2y$10$1ra02Z0q35KcTdfr.5HF1OCWBTgj.vrth1IsghM0TiB2f5V59oJVO', NULL, NULL, '2018-06-15', 'ES-20180615-001', NULL, TRUE, '12345678A', pediatra_id, 'A+', 'Alergia a polen', 'Seguimiento anual por pediatra');

        INSERT INTO perfiles_salud (id_paciente, peso_kg, altura_cm, alergias, enfermedades)
        VALUES (dni_dep, 24.5, 120.0, 'Alergia a polen', 'Ninguna crónica');

        INSERT INTO antecedentes_familiares (id_paciente, id_enfermedad, parentesco, lado_familiar, edad_diagnostico, notas_adicionales)
        VALUES (dni_dep, 6, 'madre', 'materno', 28, 'Madre con asma en la infancia'),
               (dni_dep, 1, 'padre', 'paterno', 45, 'Padre con diabetes tipo 2');

        INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, observaciones)
        VALUES (dni_dep, pediatra_id, NOW() - INTERVAL '30 days', 'Revisión pediátrica rutinaria', 'Vacuna DTP administrada', 'Bien tolerada')
        RETURNING id_consulta INTO consulta_id;

        INSERT INTO recordatorios (id_consulta, fecha_hora, tipo_recordatorio, razon, estado)
        VALUES (consulta_id, NOW() + INTERVAL '11 months', 'Cita', 'Revisión y refuerzo vacunal en 11 meses', 'Pendiente');
    END IF;

    -- Dependiente 2: Lucia Martin (tutor: Jose - dni 23456789B)
    dni_dep := 'DEP-LUC-160203';
    IF NOT EXISTS (SELECT 1 FROM pacientes WHERE dni = dni_dep) THEN
        INSERT INTO pacientes (dni, nombre, apellidos, email, contrasena_hash, telefono, direccion, fecha_nacimiento, num_seguridad_social, id_medico_general, es_dependiente, dni_tutor, id_pediatra, grupo_sanguineo, alergias, observaciones)
        VALUES (dni_dep, 'Lucia', 'Martin', 'lucia.martin.dep@medhistory.local', '$2y$10$1ra02Z0q35KcTdfr.5HF1OCWBTgj.vrth1IsghM0TiB2f5V59oJVO', NULL, NULL, '2016-02-03', 'ES-20160203-002', NULL, TRUE, '23456789B', pediatra_id, 'O-', 'Ninguna', 'Historia familiar de migraña');

        INSERT INTO perfiles_salud (id_paciente, peso_kg, altura_cm, alergias, enfermedades)
        VALUES (dni_dep, 30.0, 128.5, 'Ninguna', 'Eccema atópico leve');

        INSERT INTO antecedentes_familiares (id_paciente, id_enfermedad, parentesco, lado_familiar, edad_diagnostico, notas_adicionales)
        VALUES (dni_dep, 13, 'madre', 'materno', 32, 'Madre con migrañas recurrentes'),
               (dni_dep, 6, 'hermano', NULL, 8, 'Hermano con asma bronquica');

        INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, observaciones)
        VALUES (dni_dep, pediatra_id, NOW() - INTERVAL '75 days', 'Eccema leve con brotes', 'Cremas emolientes y corticoide tópico por 7 días', 'Control en 2 semanas')
        RETURNING id_consulta INTO consulta_id;

        INSERT INTO recordatorios (id_consulta, fecha_hora, tipo_recordatorio, razon, estado)
        VALUES (consulta_id, NOW() + INTERVAL '14 days', 'Control', 'Revisión de eccema', 'Pendiente');
    END IF;

    -- Dependiente 3: Mateo Ruiz (tutor: Laura - dni 34567890C)
    dni_dep := 'DEP-MAT-201120';
    IF NOT EXISTS (SELECT 1 FROM pacientes WHERE dni = dni_dep) THEN
        INSERT INTO pacientes (dni, nombre, apellidos, email, contrasena_hash, telefono, direccion, fecha_nacimiento, num_seguridad_social, id_medico_general, es_dependiente, dni_tutor, id_pediatra, grupo_sanguineo, alergias, observaciones)
        VALUES (dni_dep, 'Mateo', 'Ruiz', 'mateo.ruiz.dep@medhistory.local', '$2y$10$1ra02Z0q35KcTdfr.5HF1OCWBTgj.vrth1IsghM0TiB2f5V59oJVO', NULL, NULL, '2020-11-20', 'ES-20201120-003', NULL, TRUE, '34567890C', pediatra_id, 'B+', 'Alergia a huevo', 'Seguimiento por alergólogo pediátrico si reacciones');

        INSERT INTO perfiles_salud (id_paciente, peso_kg, altura_cm, alergias, enfermedades)
        VALUES (dni_dep, 13.0, 92.0, 'Alergia a huevo', 'Ninguna crónica');

        INSERT INTO antecedentes_familiares (id_paciente, id_enfermedad, parentesco, lado_familiar, edad_diagnostico, notas_adicionales)
        VALUES (dni_dep, 14, 'madre', 'materno', 28, 'Madre con epilepsia controlada'),
               (dni_dep, 17, 'abuelo_paterno', 'paterno', 68, 'Antecedente de obesidad');

        INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, observaciones)
        VALUES (dni_dep, pediatra_id, NOW() - INTERVAL '40 days', 'Reacción alérgica leve tras ingestión de huevo', 'Antihistamínico y observación', 'Derivar a alergología si repetición')
        RETURNING id_consulta INTO consulta_id;

        INSERT INTO recordatorios (id_consulta, fecha_hora, tipo_recordatorio, razon, estado)
        VALUES (consulta_id, NOW() + INTERVAL '6 months', 'Control', 'Revisión alergológica semestral', 'Pendiente');
    END IF;
END $$;

-- Fin de datos de prueba para dependientes

-- =============================================================================
-- Registros de vacunación para dependientes (cartilla de vacunas)
-- =============================================================================
DO $$
BEGIN
    -- Santiago
    IF NOT EXISTS (SELECT 1 FROM cartilla_vacunas WHERE id_paciente = 'DEP-SAN-180615' AND nombre_vacuna = 'DTP') THEN
        INSERT INTO cartilla_vacunas (id_paciente, nombre_vacuna, fecha_administracion, dosis, centro, observaciones, estado)
        VALUES ('DEP-SAN-180615', 'DTP', '2020-06-15', '1ª dosis', 'Centro Salud Madrid', 'Administrada sin incidencias', 'Administrada');
    END IF;

    -- Lucia
    IF NOT EXISTS (SELECT 1 FROM cartilla_vacunas WHERE id_paciente = 'DEP-LUC-160203' AND nombre_vacuna = 'Triple Viral') THEN
        INSERT INTO cartilla_vacunas (id_paciente, nombre_vacuna, fecha_administracion, dosis, centro, observaciones, estado)
        VALUES ('DEP-LUC-160203', 'Triple Viral', '2016-04-10', '1ª dosis', 'Centro Salud Madrid', 'Administrada', 'Administrada');
    END IF;

    -- Mateo
    IF NOT EXISTS (SELECT 1 FROM cartilla_vacunas WHERE id_paciente = 'DEP-MAT-201120' AND nombre_vacuna = 'Hepatitis B') THEN
        INSERT INTO cartilla_vacunas (id_paciente, nombre_vacuna, fecha_administracion, dosis, centro, observaciones, estado)
        VALUES ('DEP-MAT-201120', 'Hepatitis B', '2021-01-15', '1ª dosis', 'Hospital Local', 'Administrada', 'Administrada');
    END IF;

    -- Elena
    IF NOT EXISTS (SELECT 1 FROM cartilla_vacunas WHERE id_paciente = 'DEP-ELENA-170709' AND nombre_vacuna = 'BCG') THEN
        INSERT INTO cartilla_vacunas (id_paciente, nombre_vacuna, fecha_administracion, dosis, centro, observaciones, estado)
        VALUES ('DEP-ELENA-170709', 'BCG', '2017-07-10', 'Única', 'Centro Salud', 'Administrada al nacimiento', 'Administrada');
    END IF;

    -- Hugo
    IF NOT EXISTS (SELECT 1 FROM cartilla_vacunas WHERE id_paciente = 'DEP-HUGO-20191212' AND nombre_vacuna = 'Neumococo') THEN
        INSERT INTO cartilla_vacunas (id_paciente, nombre_vacuna, fecha_administracion, dosis, centro, observaciones, estado)
        VALUES ('DEP-HUGO-20191212', 'Neumococo', '2020-02-12', '1ª dosis', 'Centro Salud', 'Administrada', 'Administrada');
    END IF;

    -- Abril
    IF NOT EXISTS (SELECT 1 FROM cartilla_vacunas WHERE id_paciente = 'DEP-ABRIL-20200405' AND nombre_vacuna = 'Polio') THEN
        INSERT INTO cartilla_vacunas (id_paciente, nombre_vacuna, fecha_administracion, dosis, centro, observaciones, estado)
        VALUES ('DEP-ABRIL-20200405', 'Polio', '2020-05-10', '1ª dosis', 'Hospital Local', 'Administrada', 'Administrada');
    END IF;
END $$;


-- Añadir 3 dependientes adicionales (para tener 6 en total)
DO $$
DECLARE
    pediatra_id INT;
    consulta_id INT;
    dni_dep VARCHAR(50);
BEGIN
    SELECT me.id_medico INTO pediatra_id FROM medicos_especialistas me WHERE me.especialidad = 'Pediatra' LIMIT 1;
    IF pediatra_id IS NULL THEN
        SELECT id_medico INTO pediatra_id FROM medicos_especialistas LIMIT 1;
    END IF;

    -- Dependiente 4: Elena Soto (tutor: 45678901D)
    dni_dep := 'DEP-ELENA-170709';
    IF NOT EXISTS (SELECT 1 FROM pacientes WHERE dni = dni_dep) THEN
        INSERT INTO pacientes (dni, nombre, apellidos, email, contrasena_hash, fecha_nacimiento, num_seguridad_social, activo, es_dependiente, dni_tutor, id_pediatra, grupo_sanguineo, alergias, observaciones)
        VALUES (dni_dep, 'Elena', 'Soto', 'elena.soto.dep@medhistory.local', '$2y$10$1ra02Z0q35KcTdfr.5HF1OCWBTgj.vrth1IsghM0TiB2f5V59oJVO', '2017-07-09', 'ES-20170709-004', TRUE, TRUE, '45678901D', pediatra_id, 'A-', 'Ninguna', 'Seguimiento anual');

        INSERT INTO perfiles_salud (id_paciente, peso_kg, altura_cm, alergias, enfermedades)
        VALUES (dni_dep, 18.0, 100.0, 'Ninguna', 'Ninguna');

        INSERT INTO antecedentes_familiares (id_paciente, id_enfermedad, parentesco, lado_familiar, edad_diagnostico, notas_adicionales)
        VALUES (dni_dep, 6, 'madre', 'materno', 35, 'Madre con asma');

        INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, observaciones)
        VALUES (dni_dep, pediatra_id, NOW() - INTERVAL '20 days', 'Chequeo pediátrico', 'Ninguno', 'Todo normal') RETURNING id_consulta INTO consulta_id;

        INSERT INTO recordatorios (id_consulta, fecha_hora, tipo_recordatorio, razon, estado)
        VALUES (consulta_id, NOW() + INTERVAL '11 months', 'Cita', 'Revisión anual', 'Pendiente');
    END IF;

    -- Dependiente 5: Hugo Alvarez (tutor: 56789012E)
    dni_dep := 'DEP-HUGO-20191212';
    IF NOT EXISTS (SELECT 1 FROM pacientes WHERE dni = dni_dep) THEN
        INSERT INTO pacientes (dni, nombre, apellidos, email, contrasena_hash, fecha_nacimiento, num_seguridad_social, activo, es_dependiente, dni_tutor, id_pediatra, grupo_sanguineo, alergias, observaciones)
        VALUES (dni_dep, 'Hugo', 'Alvarez', 'hugo.alvarez.dep@medhistory.local', '$2y$10$1ra02Z0q35KcTdfr.5HF1OCWBTgj.vrth1IsghM0TiB2f5V59oJVO', '2019-12-12', 'ES-20191212-005', TRUE, TRUE, '56789012E', pediatra_id, 'O+', 'Alergia a leche', 'Control alergias');

        INSERT INTO perfiles_salud (id_paciente, peso_kg, altura_cm, alergias, enfermedades)
        VALUES (dni_dep, 12.5, 85.0, 'Alergia a leche', 'Ninguna');

        INSERT INTO antecedentes_familiares (id_paciente, id_enfermedad, parentesco, lado_familiar, edad_diagnostico, notas_adicionales)
        VALUES (dni_dep, 18, 'madre', 'materno', 40, 'Madre con anemia');

        INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, observaciones)
        VALUES (dni_dep, pediatra_id, NOW() - INTERVAL '10 days', 'Revisión alergia', 'Antihistamínico', 'Mejora') RETURNING id_consulta INTO consulta_id;

        INSERT INTO recordatorios (id_consulta, fecha_hora, tipo_recordatorio, razon, estado)
        VALUES (consulta_id, NOW() + INTERVAL '1 month', 'Control', 'Revisión alergia', 'Pendiente');
    END IF;

    -- Dependiente 6: Abril Moreno (tutor: 67890123F)
    dni_dep := 'DEP-ABRIL-20200405';
    IF NOT EXISTS (SELECT 1 FROM pacientes WHERE dni = dni_dep) THEN
        INSERT INTO pacientes (dni, nombre, apellidos, email, contrasena_hash, fecha_nacimiento, num_seguridad_social, activo, es_dependiente, dni_tutor, id_pediatra, grupo_sanguineo, alergias, observaciones)
        VALUES (dni_dep, 'Abril', 'Moreno', 'abril.moreno.dep@medhistory.local', '$2y$10$1ra02Z0q35KcTdfr.5HF1OCWBTgj.vrth1IsghM0TiB2f5V59oJVO', '2020-04-05', 'ES-20200405-006', TRUE, TRUE, '67890123F', pediatra_id, 'B-', 'Ninguna', 'Seguimiento preventiva');

        INSERT INTO perfiles_salud (id_paciente, peso_kg, altura_cm, alergias, enfermedades)
        VALUES (dni_dep, 9.0, 70.0, 'Ninguna', 'Ninguna');

        INSERT INTO antecedentes_familiares (id_paciente, id_enfermedad, parentesco, lado_familiar, edad_diagnostico, notas_adicionales)
        VALUES (dni_dep, 17, 'abuelo_materno', 'materno', 70, 'Abuelo con obesidad');

        INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, observaciones)
        VALUES (dni_dep, pediatra_id, NOW() - INTERVAL '5 days', 'Chequeo preventivo', 'Ninguno', 'Bien') RETURNING id_consulta INTO consulta_id;

        INSERT INTO recordatorios (id_consulta, fecha_hora, tipo_recordatorio, razon, estado)
        VALUES (consulta_id, NOW() + INTERVAL '6 months', 'Control', 'Revisión semestral', 'Pendiente');
    END IF;
END $$;
