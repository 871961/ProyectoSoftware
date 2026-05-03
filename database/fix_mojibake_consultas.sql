-- =============================================================================
-- Corregir mojibake en consultas de dependientes de María
-- =============================================================================

DO $$
BEGIN
    -- Corrección 1: Reacción alérgica leve a alimento (Miguel)
    UPDATE consultas 
    SET diagnostico = 'Reacción alérgica leve a alimento'
    WHERE id_paciente = 'DEP-MARIA-003' AND diagnostico LIKE '%Reacci%n%';
    
    -- Corrección 2: Dermatitis atópica leve (Lucia)
    UPDATE consultas
    SET diagnostico = 'Dermatitis atópica leve'
    WHERE id_paciente = 'DEP-MARIA-002' AND diagnostico LIKE '%Dermatitis%';
    
    -- Corrección 3: Revisión anual + vacunas (Lucia)
    UPDATE consultas
    SET diagnostico = 'Revisión anual + vacunas'
    WHERE id_paciente = 'DEP-MARIA-002' AND diagnostico LIKE '%Revisi%n%';
    
    -- Corrección 4: Revisión pediátrica rutinaria 5 años (Miguel)
    UPDATE consultas
    SET diagnostico = 'Revisión pediátrica rutinaria 5 años'
    WHERE id_paciente = 'DEP-MARIA-003' AND diagnostico LIKE '%Revisi%n%';
    
    -- Corrección 5: Tratamiento en observaciones si existe mojibake
    UPDATE consultas
    SET tratamiento = 'Crema emoliente diaria. Corticosteroide tópico si es necesario'
    WHERE id_paciente = 'DEP-MARIA-002' AND diagnostico = 'Dermatitis atópica leve';
    
    UPDATE consultas
    SET tratamiento = 'Antihistamínico H1 si es necesario. Evitar alimento causante'
    WHERE id_paciente = 'DEP-MARIA-003' AND diagnostico = 'Reacción alérgica leve a alimento';
    
    UPDATE consultas
    SET tratamiento = 'Dieta sin lactosa. Lactasa en suplemento si lo necesita'
    WHERE id_paciente = 'DEP-MARIA-003' AND diagnostico = 'Intolerancia a la lactosa confirmada';
    
    -- Verificar enfermedades_catalogo
    INSERT INTO enfermedades_catalogo (nombre_patologia) 
    VALUES ('Dermatitis atópica')
    ON CONFLICT (nombre_patologia) DO NOTHING;
    
    INSERT INTO enfermedades_catalogo (nombre_patologia)
    VALUES ('Otitis media aguda')
    ON CONFLICT (nombre_patologia) DO NOTHING;
    
    RAISE NOTICE 'Correcciones de mojibake aplicadas exitosamente';
END $$;

-- Verificación
SELECT COUNT(*) as total_fixed FROM consultas 
WHERE id_paciente IN ('DEP-MARIA-001', 'DEP-MARIA-002', 'DEP-MARIA-003');
