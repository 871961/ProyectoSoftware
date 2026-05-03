-- =============================================================================
-- Eliminar consultas corruptas y volver a insertar correctamente
-- =============================================================================

DELETE FROM consultas 
WHERE id_paciente IN ('DEP-MARIA-001', 'DEP-MARIA-002', 'DEP-MARIA-003');

-- Obtener el ID del pediatra
DO $$
DECLARE
    pediatra_id INT;
    santiago_dni VARCHAR(20) := 'DEP-MARIA-001';
    lucia_dni VARCHAR(20) := 'DEP-MARIA-002';
    miguel_dni VARCHAR(20) := 'DEP-MARIA-003';
BEGIN
    SELECT id_medico INTO pediatra_id 
    FROM medicos 
    WHERE email = 'laura.martinez.pediatra@clinica.com'
    LIMIT 1;
    
    -- Reinsert correctly encoded data
    INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, observaciones)
    VALUES 
    (santiago_dni, pediatra_id, NOW() - INTERVAL '45 days', 
     'Revisión pediátrica rutinaria', 
     'Sin tratamiento necesario', 
     'Niño sano, desarrollo normal'),
    
    (santiago_dni, pediatra_id, NOW() - INTERVAL '120 days', 
     'Otitis media aguda', 
     'Amoxicilina 250mg cada 8 horas por 7 días', 
     'Oído derecho afectado'),
    
    (santiago_dni, pediatra_id, NOW() - INTERVAL '200 days', 
     'Control de crecimiento y desarrollo', 
     'Suplemento vitamina D 400 UI diarias', 
     'Desarrollo normal para su edad'),
    
    (lucia_dni, pediatra_id, NOW() - INTERVAL '30 days', 
     'Dermatitis atópica leve', 
     'Crema emoliente diaria', 
     'Mejoría esperada en 2-3 semanas'),
    
    (lucia_dni, pediatra_id, NOW() - INTERVAL '90 days', 
     'Revisión anual + vacunas', 
     'Vacuna Varicela 2ª dosis', 
     'Bien tolerada'),
    
    (lucia_dni, pediatra_id, NOW() - INTERVAL '180 days', 
     'Reacción alérgica leve a alimento', 
     'Antihistamínico H1 si es necesario', 
     'Crisis resuelta'),
    
    (miguel_dni, pediatra_id, NOW() - INTERVAL '20 days', 
     'Revisión pediátrica rutinaria 5 años', 
     'Sin tratamiento', 
     'Niño sano'),
    
    (miguel_dni, pediatra_id, NOW() - INTERVAL '100 days', 
     'Gastroenteritis aguda', 
     'Rehidratación oral', 
     'Mejoría progresiva en 48 horas'),
    
    (miguel_dni, pediatra_id, NOW() - INTERVAL '240 days', 
     'Intolerancia a la lactosa confirmada', 
     'Dieta sin lactosa', 
     'Requiere seguimiento nutricional');
     
    RAISE NOTICE 'Consultas reinsertadas correctamente';
END $$;
