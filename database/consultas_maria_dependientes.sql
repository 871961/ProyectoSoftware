-- =============================================================================
-- Consultas Pediátricas para los Dependientes de María
-- =============================================================================

DO $$
DECLARE
    pediatra_id INT;
    santiago_dni VARCHAR(20) := 'DEP-MARIA-001';
    lucia_dni VARCHAR(20) := 'DEP-MARIA-002';
    miguel_dni VARCHAR(20) := 'DEP-MARIA-003';
BEGIN
    -- Obtener el ID del pediatra
    SELECT id_medico INTO pediatra_id 
    FROM medicos 
    WHERE email = 'laura.martinez.pediatra@clinica.com'
    LIMIT 1;
    
    IF pediatra_id IS NULL THEN
        RAISE EXCEPTION 'Pediatra no encontrado';
    END IF;
    
    -- =============================================================================
    -- CONSULTAS DE SANTIAGO
    -- =============================================================================
    INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, observaciones)
    VALUES 
    (santiago_dni, pediatra_id, NOW() - INTERVAL '45 days', 
     'Revisión pediátrica rutinaria', 
     'Sin tratamiento necesario. Recomendaciones de higiene', 
     'Niño sano, desarrollo normal. Próxima revisión en 6 meses'),
    
    (santiago_dni, pediatra_id, NOW() - INTERVAL '120 days', 
     'Otitis media aguda', 
     'Amoxicilina 250mg cada 8 horas por 7 días', 
     'Revisión en 10 días. Oído derecho afectado. Mejoría progresiva'),
    
    (santiago_dni, pediatra_id, NOW() - INTERVAL '200 days', 
     'Control de crecimiento y desarrollo', 
     'Suplemento vitamina D 400 UI diarias', 
     'Percentil 75 peso, 80 talla. Desarrollo normal para su edad');
    
    -- =============================================================================
    -- CONSULTAS DE LUCIA
    -- =============================================================================
    INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, observaciones)
    VALUES 
    (lucia_dni, pediatra_id, NOW() - INTERVAL '30 days', 
     'Dermatitis atópica leve', 
     'Crema emoliente diaria. Corticosteroide tópico si es necesario', 
     'Mejoría esperada en 2-3 semanas. Evitar irritantes'),
    
    (lucia_dni, pediatra_id, NOW() - INTERVAL '90 days', 
     'Revisión anual + vacunas', 
     'Vacuna Varicela 2ª dosis', 
     'Bien tolerada. Próxima cita en 12 meses'),
    
    (lucia_dni, pediatra_id, NOW() - INTERVAL '180 days', 
     'Reacción alérgica leve a alimento', 
     'Antihistamínico H1 si es necesario. Evitar alimento causante', 
     'Crisis resuelta. Derivación a alergólogo si persisten síntomas');
    
    -- =============================================================================
    -- CONSULTAS DE MIGUEL
    -- =============================================================================
    INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, observaciones)
    VALUES 
    (miguel_dni, pediatra_id, NOW() - INTERVAL '20 days', 
     'Revisión pediátrica rutinaria 5 años', 
     'Sin tratamiento. Reforzar importancia de higiene bucal', 
     'Niño sano. Remitir a odontología pediátrica para revisión dental'),
    
    (miguel_dni, pediatra_id, NOW() - INTERVAL '100 days', 
     'Gastroenteritis aguda', 
     'Rehidratación oral. Dieta blanda. Probióticos', 
     'Mejoría progresiva en 48 horas. Educación en higiene'),
    
    (miguel_dni, pediatra_id, NOW() - INTERVAL '240 days', 
     'Intolerancia a la lactosa confirmada', 
     'Dieta sin lactosa. Lactasa en suplemento si lo necesita', 
     'Derivación a nutrición pediátrica para educación dietética');
    
    -- =============================================================================
    -- PERFILES DE SALUD
    -- =============================================================================
    INSERT INTO perfiles_salud (id_paciente, peso_kg, altura_cm, alergias, enfermedades)
    VALUES 
    (santiago_dni, 28.5, 125, 'Polen (alergia estacional)', NULL),
    (lucia_dni, 37.2, 142, NULL, 'Dermatitis atópica leve'),
    (miguel_dni, 20.1, 112, 'Lactosa', 'Intolerancia a lactosa');
    
    RAISE NOTICE 'Consultas y perfiles de salud creados para Santiago, Lucia y Miguel';
END $$;
