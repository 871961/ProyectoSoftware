-- =============================================================================
-- Insertar Pediatra
-- =============================================================================
INSERT INTO medicos (nombre, apellidos, email, contrasena_hash, telefono, num_colegiado, tipo_medico)
VALUES ('Laura', 'Martinez Ruiz', 'laura.martinez.pediatra@clinica.com', 
        '$2y$10$Joc080WMq2rrLGPLGwUQ2O7wKUgjmmgTavdOT/xYLD44c43mXWvta', 
        '615-987-654', 'PE-001234', 'especialista')
RETURNING id_medico;

-- Obtener el ID del pediatra recién creado
DO $$
DECLARE
    pediatra_id INT;
    maria_dni VARCHAR(20) := '12345678A';
BEGIN
    -- Obtener el ID del médico que acabamos de crear
    SELECT id_medico INTO pediatra_id 
    FROM medicos 
    WHERE email = 'laura.martinez.pediatra@clinica.com'
    LIMIT 1;
    
    -- Crear entrada en medicos_especialistas
    INSERT INTO medicos_especialistas (id_medico, especialidad)
    VALUES (pediatra_id, 'Pediatra');
    
    -- =============================================================================
    -- Insertar Dependientes (Hijos) de María
    -- =============================================================================
    
    -- HIJO 1: Santiago
    INSERT INTO pacientes (
        dni, nombre, apellidos, email, contrasena_hash, telefono, direccion,
        fecha_nacimiento, num_seguridad_social, es_dependiente, dni_tutor,
        id_pediatra, grupo_sanguineo, alergias, observaciones, activo
    ) VALUES (
        'DEP-MARIA-001', 'Santiago', 'Perez Garcia', 
        'santiago.perez@email.com', '$2y$10$Joc080WMq2rrLGPLGwUQ2O7wKUgjmmgTavdOT/xYLD44c43mXWvta',
        NULL, NULL, '2018-06-15', 'ES-20180615-001',
        TRUE, maria_dni, pediatra_id, 'O+', 'Polen', 
        'Seguimiento pediátrico. Alergia estacional leve', TRUE
    );
    
    -- HIJO 2: Lucia
    INSERT INTO pacientes (
        dni, nombre, apellidos, email, contrasena_hash, telefono, direccion,
        fecha_nacimiento, num_seguridad_social, es_dependiente, dni_tutor,
        id_pediatra, grupo_sanguineo, alergias, observaciones, activo
    ) VALUES (
        'DEP-MARIA-002', 'Lucia', 'Perez Garcia', 
        'lucia.perez@email.com', '$2y$10$Joc080WMq2rrLGPLGwUQ2O7wKUgjmmgTavdOT/xYLD44c43mXWvta',
        NULL, NULL, '2016-04-22', 'ES-20160422-002',
        TRUE, maria_dni, pediatra_id, 'A+', 'Ninguna', 
        'Seguimiento pediátrico regular. Desarrollo normal', TRUE
    );
    
    -- HIJO 3: Miguel
    INSERT INTO pacientes (
        dni, nombre, apellidos, email, contrasena_hash, telefono, direccion,
        fecha_nacimiento, num_seguridad_social, es_dependiente, dni_tutor,
        id_pediatra, grupo_sanguineo, alergias, observaciones, activo
    ) VALUES (
        'DEP-MARIA-003', 'Miguel', 'Perez Garcia', 
        'miguel.perez@email.com', '$2y$10$Joc080WMq2rrLGPLGwUQ2O7wKUgjmmgTavdOT/xYLD44c43mXWvta',
        NULL, NULL, '2020-11-10', 'ES-20201110-003',
        TRUE, maria_dni, pediatra_id, 'B+', 'Lactosa', 
        'Seguimiento pediátrico. Intolerancia a la lactosa desde los 6 meses', TRUE
    );

    RAISE NOTICE 'Pediatra creado: ID %', pediatra_id;
    RAISE NOTICE 'Dependientes de María creados: Santiago, Lucia, Miguel';
END $$;
